<?php
/**
 * Created by OOO 1C-SOFT.
 * User: Dremin_S
 * Date: 04.06.2018
 */

namespace Soft1c\Rest;

use Bitrix\Main;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Soft1c\Rest\Exceptions;

class MainHandler implements HttpKernelInterface
{

	/** @var string */
	protected $contentRequest = '';

	/** @var Request */
	protected $request;

	public function __construct()
	{
	}

	public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
	{
		$this->request = $request;

		$attrs = $this->request->attributes;
		$controller = explode('::', $attrs->get('_controller'));
		$result = null;
		$action = $controller[1] ? : $attrs->get('method');

		if ($attrs->has('_module')){
			if (!Main\Loader::includeModule($attrs->has('_module'))){
				throw new Exceptions\RouterFileException('Module '.$attrs->has('_module').' not found or not installed');
			}
		}

		$this->contentRequest = $this->convertRequestBody($this->request->getContent());

		try {
			if ($attrs->has('_component')){
				\CBitrixComponent::includeComponentClass($attrs->get('_component'));
				$reflection = new \ReflectionClass($controller[0]);
				$instance = $reflection->newInstance();
				$result = $instance->$action($this->request);
				// todo сделать установку arParams для компонента
			} else {
				$reflection = new \ReflectionClass($controller[0]);
				$instance = $reflection->newInstance();

				if (strlen($action) == 0){
					if (is_callable([$instance, '__invoke'])){
						dd($instance);
					}
				} elseif (is_callable([$instance, $action])) {
					$result = $instance->$action($this->request);
					// todo exception router callable method
				}
			}
		} catch (\ReflectionException $e) {
			throw new Exceptions\RouteException($e->getMessage(), 100);
		}


		return $result;
	}

	/**
	 * @method convertRequestBody
	 * @param string $body
	 *
	 * @return array|\RecursiveArrayIterator
	 */
	protected function convertRequestBody($body = '')
	{
		if($this->request->getMethod() !== Request::METHOD_GET && strlen($body) > 0){
			switch ($this->request->getContentType()) {
				case 'json':
					$this->contentRequest = (array)Main\Web\Json::decode($body);
					break;

				case 'xml':

					break;
			}

			$this->contentRequest = $this->filterRequest($this->contentRequest);

			foreach ($this->contentRequest as $code => $value) {
				$this->request->request->set($code, $value);
			}
		}

		return $this->contentRequest;
	}

	/**
	 * @method filterRequest
	 * @param array $data
	 *
	 * @return \RecursiveArrayIterator
	 */
	protected function filterRequest(array $data = [])
	{
		$result = [];
		$iterator = new \RecursiveArrayIterator($data);

		$it = new \RecursiveIteratorIterator($iterator);

		foreach ($iterator as $k => &$item) {
			if($iterator->hasChildren()){
				$iterator->offsetSet($k, $this->filterRequest($item));
			} else {
				$iterator->offsetSet($k, htmlspecialcharsbx($item));
			}
		}

		return $iterator;
	}
}
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

	public function __construct()
	{
	}

	public function handle(Request $request, $type = self::MASTER_REQUEST, $catch = true)
	{
		$this->contentRequest = $request->getContent();

		$attrs = $request->attributes;
		$controller = $attrs->get('_controller');
		$result = null;
		$action = $attrs->get('method');

		if ($attrs->has('_module')){
			if(!Main\Loader::includeModule($attrs->has('_module'))){
				throw new Exceptions\RouterFileException('Module '.$attrs->has('_module').' not found or not installed');
			}
		}

		try {
			if ($attrs->has('_component')) {
				\CBitrixComponent::includeComponentClass($attrs->get('_component'));
				$reflection = new \ReflectionClass($controller);
				$instance = $reflection->newInstance();
				$result = $instance->$action($request);
				// todo сделать установку arParams для компонента
			} else {
				$reflection = new \ReflectionClass($controller);
				$instance = $reflection->newInstance();
				if(strlen($action) == 0){
					if(is_callable([$instance, '__invoke'])){
						dd($instance);
					}
				} elseif(is_callable([$instance, $action])) {
					$result = $instance->$action($request);
					// todo exception router callable method
				}
			}
		} catch (\ReflectionException $e){
			throw new Exceptions\RouteException($e->getMessage(), 100);
		}


		return $result;
	}


}
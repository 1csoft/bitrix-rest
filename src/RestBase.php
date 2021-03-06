<?php
/**
 * Created by OOO 1C-SOFT.
 * User: Dremin_S
 * Date: 04.06.2018
 */

namespace Soft1c\Rest;

use Bitrix\Main\DB\Exception;
use Bitrix\Main\Web\Json;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Routing;
use Soft1c\Rest\Exceptions;

class RestBase
{
	/** @var array */
	protected $options;

	/** @var string */
	protected $routerFile = '';

	/** @var Request */
	protected $request;

	/** @var Response */
	protected $response;

	/** @var EventDispatcher */
	protected static $eventDispatcher = null;

	/** @var RequestEvent */
	protected $event;

	/** @var IAuthStrategy */
	protected $authHandler = null;

	/** @var null|IHttpConvertor */
	protected $responseConvertor = null;

	/**
	 * RestBase constructor.
	 *
	 * @param array $options
	 */
	public function __construct(array $options = [])
	{
		$resolver = new OptionsResolver();
		$resolver->setDefaults([
			'routes' => '/local/php_interface/router',
			'type' => 'php',
			'format' => 'json',
		]);
		$resolver->setDefined(array_keys($options));

		$this->options = $resolver->resolve($options);

		$this->request = Request::createFromGlobals();

		$this->routerFile = $this->options['routes'].'.'.$this->options['type'];
//		$this->request->setFormat($this->options['format'], false);
		$this->request->setRequestFormat($this->options['format']);

		static::$eventDispatcher = new EventDispatcher();
		$this->response = new Response(null);

		if ($this->options['response_converter'] instanceof IResponseConvertor){
			$this->responseConvertor = $this->options['response_converter'];
		} elseif ($this->options['format'] == 'xml') {
			$this->responseConvertor = new XmlResponseConvertor();
		} elseif ($this->options['format'] == 'json'){
			$this->responseConvertor = new JsonConverter();
			$this->options['response_converter'] = $this->responseConvertor;
		}

		if ($this->options['request_converter'] instanceof IResponseConvertor){
			$this->responseConvertor = $this->options['request_converter'];
		} elseif ($this->options['format'] == 'xml') {
			$this->options['request_converter'] = new XmlRequestConverter();
		} elseif ($this->options['format'] == 'json'){
			$this->responseConvertor = new JsonConverter();
			$this->options['request_converter'] = $this->responseConvertor;
		}
	}

	/**
	 * @method getRoutes
	 * @return Routing\RouteCollection
	 * @throws Exceptions\RouterFileException
	 */
	protected function getRoutes()
	{
		$file = $this->request->server->get('DOCUMENT_ROOT').$this->routerFile;

		if (!file_exists($file)){
			throw new Exceptions\RouterFileException('File with routes not found', 1000);
		}
		$routes = require_once($file);

		if (!is_array($routes)){
			throw new Exceptions\RouterFileException('File with routes not found', 1001);
		}

		if (count($routes) == 0){
			throw new Exceptions\RouterFileException('Array with routes is empty', 1002);
		}

		$routeCollection = new Routing\RouteCollection();


		foreach ($routes as $route) {
			$path = $route['path'];
			unset($route['path']);

			$methods = !$route['_methods'] ? ['GET', 'POST'] : $route['_methods'];

			$router = new Routing\Route($this->options['baseUrl'].$path,
				$route,
				$route['require'] ? : [],
				array(),
				'',
				['http', 'https'],
				$methods
			);
			$routeCollection->add($path, $router);

			/*if(is_array($route['events'])){
				foreach ($route['events'] as $name => $event) {

					if(!is_array($event)){
						$arEvent = explode('::', $event);
						static::getEventDispatcher()->addListener($name, call_user_func_array(array($arEvent[0], $arEvent[1])));
					}
				}
			}*/

			unset($router);
		}

		return $routeCollection;
	}

	/**
	 * @method start
	 * @return $this
	 * @throws Exceptions\RestException
	 */
	public function start()
	{
		$context = new Routing\RequestContext();
		$context->fromRequest($this->request);
		$context->setBaseUrl($this->options['baseUrl']);

		$routes = $this->getRoutes();

		$matcher = new Routing\Matcher\UrlMatcher($routes, $context);
		try {
			$attrs = $matcher->match($this->request->getPathInfo());
			foreach ($attrs as $code => $val) {
				$this->request->attributes->set($code, $val);
			}

			if ($attrs['_format']){
				$this->request->setFormat($attrs['_format'], false);
			}
		} catch (Routing\Exception\ResourceNotFoundException $e) {
			dump($routes, $e);
			$this->response->setStatusCode(404);
			\CHTTP::SetStatus('404');
		}


		$this->event = new RequestEvent($this->request);
		static::getEventDispatcher()->dispatch('request.start', $this->event);

//		if($this->request->headers->has('AuthenticationToken')){
//			UserTokenTable::authByToken($this->request->headers->get('AuthenticationToken'));
//		}

		return $this;
	}

	/**
	 * @method process
	 * @return $this
	 */
	public function process()
	{
		$mainHandler = new MainHandler();
		$out = [
			'success' => true,
			'data' => null,
			'error' => null,
			'errorCode' => null
		];
		$result = null;
		$mainHandler->setConverter($this->options['request_converter']);

		static::getEventDispatcher()->dispatch('request.beforeResult', $this->event);
		try {

			if ($this->request->attributes->get('_auth') == true && $this->authHandler instanceof IAuthStrategy){
				if (!$this->authHandler->authorize($this->request->headers->get('x-token'))){
					throw new Exceptions\Auth('not auth', 403);
				}
			}

			$result = $mainHandler->handle($this->request);
			$this->response->setStatusCode(Response::HTTP_OK);

		} catch (Exceptions\Auth $exception) {
			$this->response->setStatusCode($exception->getCode());
			\CHTTP::SetStatus($exception->getCode());
			$out['error'] = $exception->__toString();
			$out['errorCode'] = $exception->getCode();
		} catch (Exceptions\Main $e) {
			$out['error'] = $e->__toString();
			$out['errorCode'] = $e->getCode();
		} catch (\Exception $e) {
			$out['trace'] = $e->__toString();
			$out['error'] = sprintf('%s', $e->getMessage());
			$out['errorCode'] = $e->getCode();
		}

		switch ($this->response->getStatusCode()) {
			case Response::HTTP_NOT_FOUND:
				$out['error'] = '404 not found';
				$out['errorCode'] = $this->response->getStatusCode();
				break;
		}

		if (!is_null($out['error'])){
			$out['success'] = false;
		}
		$out['data'] = $result;

		$this->event->setParams($out);

		static::getEventDispatcher()->dispatch('request.afterResult', $this->event);

		$response = '';

		switch ($this->request->getRequestFormat()) {
			case 'xml':
				$response = $this->responseConvertor->encode($out);
				break;
			case 'html':
				break;
			default:
				if ($this->options['JSON_UNESCAPED_UNICODE']){
					$response = Json::encode($out, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE);
				} else {
					$response = Json::encode($out);
				}
				break;
		}

		$this->response->headers->set('Content-type', Request::getMimeTypes($this->request->getRequestFormat()));
		$this->response->headers->set('Accept', Request::getMimeTypes($this->request->getRequestFormat()));

		$this->response->setContent($response);

		$this->event->setResponse($this->response);
		static::getEventDispatcher()->dispatch('request.result', $this->event);

		return $this;
	}

	/**
	 * @method finish
	 * @return string
	 */
	public function finish()
	{
		foreach ($this->response->headers->all() as $name => $item) {
			header($name, implode('; ', $item));
		}

		static::getEventDispatcher()->dispatch('request.finish', $this->event);


		return $this->response->getContent();
	}

	/**
	 * @method getEventDispatcher - get param eventDispatcher
	 * @return EventDispatcher
	 */
	public static function getEventDispatcher()
	{
		if (is_null(static::$eventDispatcher)){
			static::$eventDispatcher = new EventDispatcher();
		}

		return static::$eventDispatcher;
	}

	/**
	 * @method addOption
	 * @param null $name
	 * @param mixed $value
	 */
	public function addOption($name = null, $value)
	{
		$this->options[$name] = $value;
	}

	/**
	 * @method getOption
	 * @param $name
	 *
	 * @return mixed|null
	 */
	public function getOption($name)
	{
		return isset($this->options[$name]) ? $this->options[$name] : null;
	}

	/**
	 * @method getAuthHandler - get param authHandler
	 * @return IAuthStrategy
	 */
	public function getAuthHandler()
	{
		return $this->authHandler;
	}

	/**
	 * @method setAuthHandler - set param AuthHandler
	 * @param IAuthStrategy $authHandler
	 */
	public function setAuthHandler(IAuthStrategy $authHandler)
	{
		$this->authHandler = $authHandler;
	}


}
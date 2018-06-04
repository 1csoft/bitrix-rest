<?php
/**
 * Created by OOO 1C-SOFT.
 * User: dremin_s
 * Date: 28.09.2017
 */

namespace Soft1c\Rest;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\EventDispatcher\Event;
use Symfony\Component\HttpFoundation\Response;

class RequestEvent extends Event
{
	/** @var Request */
	protected $request;
	protected $params;
	protected $decode;

	/** @var Response */
	protected $response;

	/** @var \Exception */
	protected $error;

	/**
	 * RequestEvent constructor.
	 *
	 * @param $request
	 */
	public function __construct($request)
	{
		$this->request = $request;
	}


	/**
	 * @method setRequest
	 * @param Request $request
	 */
	public function setRequest(Request $request)
	{
		$this->request = $request;
	}

	/**
	 * @method getRequest
	 * @return Request
	 */
	public function getRequest()
	{
		return $this->request;
	}

	/**
	 * @method getParams - get param params
	 * @return mixed
	 */
	public function getParams()
	{
		return $this->params;
	}

	/**
	 * @method setParams - set param Params
	 * @param mixed $params
	 */
	public function setParams($params)
	{
		$this->params = $params;
	}

	/**
	 * @method setError
	 * @param \Exception|\TypeError $exception
	 */
	public function setError($exception)
	{
		$this->error = $exception;
	}

	/**
	 * @method getError - get param error
	 * @return \Exception
	 */
	public function getError()
	{
		return $this->error;
	}

	/**
	 * @method getResponse - get param response
	 * @return Response
	 */
	public function getResponse()
	{
		return $this->response;
	}

	/**
	 * @method setResponse - set param Response
	 * @param Response $response
	 */
	public function setResponse($response)
	{
		$this->response = $response;
	}

}
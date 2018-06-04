<?php
/**
 * Created by OOO 1C-SOFT.
 * User: Dremin_S
 * Date: 04.06.2018
 */

namespace Soft1c\Rest;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

class RouterResolver
{
	/** @var RouteCollection */
	protected $routes;

	protected $mainHandler;

	public function __construct(MainHandler $mainHandler)
	{
		$this->mainHandler = $mainHandler;

	}

	public function resolve()
	{

	}

}
<?php
/**
 * Created by OOO 1C-SOFT.
 * User: Dremin_S
 * Date: 04.06.2018
 */

namespace Soft1c\Rest\Exceptions;


use Throwable;

class RouteException extends RestException
{
	public function __construct(string $message = "", int $code = 0, ?Throwable $previous = null)
	{
		$code += 2000;

		parent::__construct($message, $code, $previous);
	}


}
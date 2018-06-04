<?php
/**
 * Created by OOO 1C-SOFT.
 * User: Dremin_S
 * Date: 04.06.2018
 */

namespace Soft1c\Rest\Exceptions;

use Throwable;

class RestException extends \Exception implements Main
{
	protected $msg;
	protected $code;

	/**
	 * RestException constructor.
	 *
	 * @param string $message
	 * @param int $code
	 * @param Throwable|null $previous
	 */
	public function __construct(string $message = "", int $code = 0, Throwable $previous = null)
	{
		$this->message = $message;
		$this->code = $code;
		parent::__construct($message, $code, $previous);
	}

	/**
	 * @method __toString
	 * @return string
	 */
	public function __toString()
	{
		return sprintf('Internal system error: [%d] %s', $this->getCode(), $this->getMessage());
	}

}
<?php
/**
 * Created by OOO 1C-SOFT.
 * User: Dremin_S
 * Date: 23.08.2018
 */

namespace Soft1c\Rest;


interface IHttpConvertor
{

	/**
	 * @method encode
	 * @param array $content
	 *
	 * @return string
	 */
	public function encode(array $content = []): string;

	/**
	 * @method decode
	 * @param string $content
	 *
	 * @return array
	 */
	public function decode(string $content = ''): array;
}
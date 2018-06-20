<?php
/**
 * Created by OOO 1C-SOFT.
 * User: Dremin_S
 * Date: 20.06.2018
 */

namespace Soft1c\Rest;


interface IAuthStrategy
{

	/**
	 * @method findUser
	 * @param array $filter
	 *
	 * @return null|array
	 */
	public function findUser(array $filter = []);

	/**
	 * @method authorize
	 * @param $params
	 *
	 * @return bool
	 */
	public function authorize($params);

	/**
	 * @method isAuthorize
	 * @return bool
	 */
	public function isAuthorize();

}
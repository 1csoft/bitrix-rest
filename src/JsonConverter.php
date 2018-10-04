<?php
/**
 * Created by OOO 1C-SOFT.
 * User: Dremin_S
 * Date: 04.10.2018
 */

namespace Soft1c\Rest;


use Bitrix\Main\Web\Json;

class JsonConverter implements IHttpConvertor
{

	protected $content;

	/**
	 * @method setContent
	 * @param array $data
	 *
	 * @return XmlResponseConvertor
	 */
	public function setContent(array $data = [])
	{
		$this->content = $data;

		return $this;
	}

	/**
	 * @method getContent
	 * @return array
	 */
	public function getContent(): array
	{
		return $this->content;
	}

	/**
	 * @method encode
	 * @param array $content
	 *
	 * @return string
	 */
	public function encode(array $content = []): string
	{
		if(count($content) == 0 || empty($content)){
			$content = $this->content;
		}

		return Json::encode($content, JSON_HEX_TAG|JSON_HEX_AMP|JSON_HEX_APOS|JSON_HEX_QUOT|JSON_UNESCAPED_UNICODE);
	}

	/**
	 * @method decode
	 * @param string $content
	 *
	 * @return array
	 */
	public function decode(string $content = ''): array
	{
		return Json::decode($content);
	}


}
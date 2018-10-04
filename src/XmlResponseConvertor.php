<?php
/**
 * Created by OOO 1C-SOFT.
 * User: Dremin_S
 * Date: 23.08.2018
 */

namespace Soft1c\Rest;


use Desperado\XmlBundle\Model\XmlGenerator;
use Desperado\XmlBundle\Model\XmlPrepare;
use Desperado\XmlBundle\Model\XmlReader;

class XmlResponseConvertor implements IHttpConvertor
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

		$xmlWriter = new XmlGenerator();
		$xmlWriter->setRootName('response');
		$prepare = new XmlPrepare();

		return $xmlWriter->generateFromArray($content);
	}

	/**
	 * @method decode
	 * @param string $content
	 *
	 * @return array
	 */
	public function decode(string $content = ''): array
	{
		$xmlReader = new XmlReader();

		return $xmlReader->processConvert($content);
	}


}
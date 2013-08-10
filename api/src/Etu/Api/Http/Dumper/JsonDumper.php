<?php

namespace Etu\Api\Http\Dumper;

use Etu\Api\Http\Response;

class JsonDumper implements DumperInterface
{
	/**
	 * @param array $content
	 * @return string
	 */
	public function dump($content)
	{
		return json_encode($content);
	}

	/**
	 * @param string $contentType
	 * @return boolean
	 */
	public function supports($contentType)
	{
		return $contentType == Response::CONTENT_TYPE_JSON;
	}
}

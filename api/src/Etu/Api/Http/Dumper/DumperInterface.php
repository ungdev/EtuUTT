<?php

namespace Etu\Api\Http\Dumper;

interface DumperInterface
{
	/**
	 * @param array $content
	 * @return string
	 */
	public function dump($content);

	/**
	 * @param string $contentType
	 * @return boolean
	 */
	public function supports($contentType);
}

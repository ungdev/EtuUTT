<?php

namespace Etu\Api\Extension;

class ApcLoader extends FilesystemLoader
{
	/**
	 * @param string $rootDir
	 * @throws \RuntimeException
	 */
	public function __construct($rootDir)
	{
		if (! extension_loaded('apc')) {
			throw new \RuntimeException('Unable to use ApcClassLoader as APC is not enabled.');
		}

		parent::__construct($rootDir);
	}

	/**
	 * @return array
	 */
	public function load()
	{
		if (false === $extensions = apc_fetch('etu_api_extensions')) {
			apc_store('etu_api_extensions', $extensions = parent::load());
		}

		return $extensions;
	}
}

<?php

namespace Etu\Api\Extension;

use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

class FilesystemLoader implements LoaderInterface
{
	/**
	 * @var string
	 */
	protected $rootDir;

	/**
	 * @var array
	 */
	protected $extensions = array();

	/**
	 * @param string $rootDir
	 */
	public function __construct($rootDir)
	{
		$this->rootDir = $rootDir;
	}

	/**
	 * @return array
	 */
	public function load()
	{
		$finder = new Finder();
		$finder->files()
			->in($this->rootDir.'/Etu/*/*Bundle/Api/Model')
			->in($this->rootDir.'/Etu/*/*Bundle/Api/Resource')
			->name('*.php');

		/** @var Extension[] $extensions */
		$extensions = array();

		/** @var $file SplFileInfo */
		foreach ($finder as $file) {
			$class = str_replace(
				array($this->rootDir.'/', '.php', '/'),
				array('', '', '\\'),
				$file->getPathname()
			);

			$parts = explode('\\Api\\', $class);
			$name = $parts[0];

			if (! isset($extensions[$name])) {
				$extension = new Extension();
				$extension->setName($name);
				$extensions[$name] = $extension;
			}

			if (substr($parts[1], 0, 6) == 'Model') {
				$extensions[$name]->addModel($class);
			} else {
				$extensions[$name]->addResource($class);
			}
		}

		return $extensions;
	}
}

<?php

namespace Etu\Api\Config;

use Symfony\Component\Yaml\Yaml;

class Loader
{
	/**
	 * @var string
	 */
	protected $rootDir;

	/**
	 * @param string $rootDir
	 */
	public function __construct($rootDir)
	{
		$this->rootDir = $rootDir;
	}

	/**
	 * @param $resource
	 * @return Collection
	 */
	public function load($resource)
	{
		$values = Yaml::parse($this->rootDir.'/'.$resource);

		if (isset($values['imports']) && is_array($values['imports'])) {
			$imports = $values['imports'];
			unset($values['imports']);
		} else {
			$imports = array();
		}

		if (count($values) == 1 && isset($values['parameters'])) {
			$values = $values['parameters'];
		}

		$collection = new Collection($values);

		foreach ($imports as $import) {
			if (isset($import['resource']) && is_string($import['resource'])) {
				$collection->merge($this->load($import['resource']));
			}
		}

		return $collection;
	}
}

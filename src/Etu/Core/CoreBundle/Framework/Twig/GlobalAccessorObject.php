<?php

namespace Etu\Core\CoreBundle\Framework\Twig;

use \Etu\Core\CoreBundle\Framework\Definition\Module;

/**
 * EtuUTT object to give acces from Twig to modules
 */
class GlobalAccessorObject
{
	/**
	 * @var Module[]
	 */
	protected $modules = array();

	/**
	 * @param Module[] $modules
	 */
	public function __construct(array $modules)
	{
		foreach ($modules as $module) {
			$this->modules[$module->getIdentifier()] = $module;
		}
	}

	/**
	 * @param string $identifier
	 * @return bool
	 */
	public function hasModule($identifier)
	{
		return isset($this->modules[$identifier]);
	}

	/**
	 * @param string $identifier
	 * @return bool
	 */
	public function moduleEnabled($identifier)
	{
		return isset($this->modules[$identifier]) && $this->modules[$identifier]->isEnabled();
	}
}

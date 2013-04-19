<?php

namespace Etu\Core\CoreBundle\Framework;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Exception\ModuleNotFoundException;
use Etu\Core\CoreBundle\Framework\Module\ModulesCollection;
use Symfony\Component\HttpKernel\Kernel;


/**
 * EtuUTT modules system kernel
 */
abstract class EtuKernel extends Kernel
{
	/**
	 * @var \Etu\Core\CoreBundle\Framework\Definition\Module[]
	 */
	protected $modules = array();

	/**
	 * Check the modules set integrity using the requirements of each module.
	 */
	public function checkModulesIntegrity()
    {
        $idenfitiers = array();

	    foreach ($this->getModulesDefinitions() as $module) {
		    $idenfitiers[] = $module->getIdentifier();
	    }

	    foreach ($this->getModulesDefinitions() as $module) {
		    foreach ((array) $module->getRequirements() as $requirement) {
			    if (! in_array($requirement, $idenfitiers)) {
				    throw new ModuleNotFoundException($requirement, $module->getIdentifier());
			    }
		    }
	    }
    }

	/**
	 * @param \Etu\Core\CoreBundle\Framework\Definition\Module $module
	 * @return \AppKernel
	 */
	public function registerModuleDefinition(Module $module)
	{
		$this->modules[$module->getName()] = $module;
		return $this;
	}

	/**
	 * @return ModulesCollection
	 */
	public function getModulesDefinitions()
	{
		return $this->modules;
	}
}

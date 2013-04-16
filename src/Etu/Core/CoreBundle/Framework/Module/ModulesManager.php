<?php

namespace Etu\Core\CoreBundle\Framework\Module;

use Etu\Core\CoreBundle\Framework\EtuKernel;
use Etu\Core\CoreBundle\Framework\Definition\Module;


/**
 * EtuUTT modules manager. Find all the modules (enabled and disabled), dump the enabled modules list
 * in app/config/modules.yml.
 */
class ModulesManager
{
	/**
	 * @var \Etu\Core\CoreBundle\Framework\Definition\Module[]
	 */
	protected $modules = array();

	/**
	 * @var string[]
	 */
	protected $modulesList = array();

	/**
	 * @var string
	 */
	protected $modulesDirectory;

	/**
	 * @param \Etu\Core\CoreBundle\Framework\EtuKernel $kernel
	 */
	public function __construct(EtuKernel $kernel)
	{
		$this->modules = $kernel->getModulesDefinitions();
		$this->modulesDirectory = $kernel->getRootDir().'/../src/Etu/Module';

		$this->iterateModules();
	}

	/**
	 * @return array|\Etu\Core\CoreBundle\Framework\Definition\Module[]
	 */
	public function getModules()
	{
		return $this->modules;
	}

	/**
	 * @param $identifer
	 * @return bool|Module
	 */
	public function getModuleByIdentifier($identifer)
	{
		foreach ($this->modules as $module) {
			if ($module->getIdentifier() == $identifer) {
				return $module;
			}
		}

		return false;
	}

	/**
	 * @return string
	 */
	public function getModulesDirectory()
	{
		return $this->modulesDirectory;
	}

	/**
	 * @return string[]
	 */
	public function getModulesList()
	{
		return $this->modulesList;
	}

	/**
	 * Iterate the Modules directory to list modules, even if they are disabled
	 */
	private function iterateModules()
	{
		// Store the loaded modules classes
		foreach ($this->modules as $key => $module) {
			unset($this->modules[$key]);

			$this->modules[get_class($module)] = $module;
			$this->modulesList[$module->getIdentifier()] = get_class($module);
		}

		// Iterate the modules directory
		$iterator = new \DirectoryIterator($this->modulesDirectory);

		foreach ($iterator as $dir) {
			if (! $dir->isDot() && $dir->isDir()) {

				// Check validity
				if (substr($dir->getBasename(), -6) == 'Bundle') {
					$module = 'Etu\\Module\\'.$dir->getBasename().'\\EtuModule'.$dir->getBasename();

					if (in_array($module, $this->modulesList)) {
						$this->modules[$module]->setEnabled(true);
					} else {
						$module = new $module();

						if ($module instanceof Module) {
							$this->modules[get_class($module)] = $module;
							$this->modulesList[$module->getIdentifier()] = get_class($module);
						}
					}
				}
			}
		}
	}
}

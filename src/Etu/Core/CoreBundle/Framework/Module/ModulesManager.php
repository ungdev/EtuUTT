<?php

namespace Etu\Core\CoreBundle\Framework\Module;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\EtuKernel;

/**
 * EtuUTT modules manager. Find all the modules (enabled and disabled), dump the enabled modules list
 * in app/config/modules.yml.
 */
class ModulesManager
{
    /**
     * @var \Etu\Core\CoreBundle\Framework\Definition\Module[]
     */
    protected $modules = [];

    /**
     * @var string[]
     */
    protected $modulesList = [];

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
     * @return ModulesCollection
     */
    public function getModules()
    {
        $modules = $this->modules;
        ksort($modules);

        return new ModulesCollection($modules);
    }

    /**
     * @return ModulesCollection
     */
    public function getEnabledModules()
    {
        $modules = [];

        foreach ($this->modules as $module) {
            if ($module->isEnabled()) {
                $modules[$module->getIdentifier()] = $module;
            }
        }

        return new ModulesCollection($modules);
    }

    /**
     * @param $identifer
     *
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
     * Iterate the Modules directory to list modules, even if they are disabled.
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
            if (!$dir->isDot() && $dir->isDir()) {
                // Check validity
                if (mb_substr($dir->getBasename(), -6) == 'Bundle') {
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

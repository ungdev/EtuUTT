<?php

namespace Etu\Core\CoreBundle\Framework;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Definition\OrgaPermission;
use Etu\Core\CoreBundle\Framework\Exception\ModuleNotFoundException;
use Etu\Core\CoreBundle\Framework\Module\ModulesCollection;
use Etu\Core\CoreBundle\Framework\Module\PermissionsCollection;
use Symfony\Component\HttpKernel\Kernel;

/**
 * EtuUTT modules system kernel.
 */
abstract class EtuKernel extends Kernel
{
    /**
     * @var \Etu\Core\CoreBundle\Framework\Definition\Module[]
     */
    protected $modules = [];

    /**
     * @var PermissionsCollection
     */
    protected static $_availablePermissions;

    /**
     * Check the modules set integrity using the requirements of each module.
     */
    public function checkModulesIntegrity()
    {
        $idenfitiers = [];

        foreach ($this->getModulesDefinitions() as $module) {
            $idenfitiers[] = $module->getIdentifier();
        }

        foreach ($this->getModulesDefinitions() as $module) {
            foreach ((array) $module->getRequirements() as $requirement) {
                if (!in_array($requirement, $idenfitiers)) {
                    throw new ModuleNotFoundException($requirement, $module->getIdentifier());
                }
            }
        }
    }

    /**
     * @param \Etu\Core\CoreBundle\Framework\Definition\Module $module
     *
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

    /**
     * @return PermissionsCollection
     */
    public function getAvailableOrganizationsPermissions()
    {
        $permissions = [
            new OrgaPermission('edit_desc', 'Peut modifier la description de l\'association'),
            new OrgaPermission('notify', 'Peut envoyer des notifications au nom de l\'association'),
            new OrgaPermission('deleguate', 'Peut distribuer les droits qu\'il possède aux membres de l\'association'),
        ];

        /** @var Module $module */
        foreach ($this->getModulesDefinitions() as $module) {
            foreach ($module->getAvailablePermissions() as $permission) {
                if ($permission instanceof OrgaPermission) {
                    $permissions[] = $permission;
                }
            }
        }

        return new PermissionsCollection($permissions);
    }
}

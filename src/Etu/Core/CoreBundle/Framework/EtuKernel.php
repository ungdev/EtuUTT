<?php

namespace Etu\Core\CoreBundle\Framework;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Definition\OrgaPermission;
use Etu\Core\CoreBundle\Framework\Definition\Permission;
use Etu\Core\CoreBundle\Framework\Exception\ModuleNotFoundException;
use Etu\Core\CoreBundle\Framework\Module\ModulesCollection;
use Etu\Core\CoreBundle\Framework\Module\PermissionsCollection;
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
	 * @var PermissionsCollection
	 */
	protected static $_availablePermissions;

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

	/**
	 * @return PermissionsCollection
	 */
	public function getAvailablePermissions()
	{
		$permissions = array(
			new Permission('pages.admin', Permission::DEFAULT_DISABLED, 'Peut administrer les pages statiques'),
		);

		/** @var Module $module */
		foreach ($this->getModulesDefinitions() as $module) {
			foreach ($module->getAvailablePermissions() as $permission) {
				if (! $permission instanceof OrgaPermission) {
					$permissions[] = $permission;
				}
			}
		}

		return new PermissionsCollection($permissions);
	}

	/**
	 * @return PermissionsCollection
	 */
	public function getAvailableOrganizationsPermissions()
	{
		$permissions = array(
			new OrgaPermission('notify', 'Peut envoyer des notifications au nom de l\'asso'),
			new OrgaPermission('deleguate', 'Peut donner/retirer ses droits aux membres de l\'asso'),
		);

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

	/**
	 * @return void
	 */
	public function freezePermissions()
	{
		self::$_availablePermissions = $this->getAvailablePermissions();
	}

	/**
	 * @return PermissionsCollection
	 */
	public static function getFrozenPermissions()
	{
		return self::$_availablePermissions;
	}
}

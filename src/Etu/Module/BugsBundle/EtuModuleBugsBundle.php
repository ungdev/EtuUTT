<?php

namespace Etu\Module\BugsBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Definition\Permission;

class EtuModuleBugsBundle extends Module
{
	/**
	 * Must boot only for connected users
	 *
	 * @return bool|void
	 */
	public function mustBoot()
	{
		return $this->getSessionLayer()->isUser();
	}

	/**
	 * At module boot, update the sidebar
	 */
	public function onModuleBoot()
	{
		$this->getSidebarBuilder()
			->getBlock('base.sidebar.etu.title')
				->add('bugs.menu.sidebar.bugs')
					->setIcon('exclamation-red.png')
					->setUrl($this->getRouter()->generate('bugs_index'))
				->end();
	}

	/**
	 * Module identifier (to be required by other modules)
	 *
	 * @return string
	 */
	public function getIdentifier()
	{
		return 'bugs';
	}

	/**
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return 'Bugs';
	}

	/**
	 * Module author
	 *
	 * @return string
	 */
	public function getAuthor()
	{
		return 'Titouan Galopin';
	}

	/**
	 * @return array
	 */
	public function getAvailablePermissions()
	{
		return array(
			new Permission('bugs.add', Permission::DEFAULT_ENABLED, 'Peut ajouter/commenter un bug'),
			new Permission('bugs.admin', Permission::DEFAULT_DISABLED, 'Peut administrer les bugs'),
		);
	}

	/**
	 * Module description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return 'Gestion des bugs et des suggestions';
	}

	/**
	 * Define the modules requirements (the other required modules) using their identifiers
	 *
	 * @return array
	 */
	public function getRequirements()
	{
		return array(
			// Insert your requirements here
		);
	}
}

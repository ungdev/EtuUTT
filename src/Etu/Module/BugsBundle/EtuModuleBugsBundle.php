<?php

namespace Etu\Module\BugsBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

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
				->add('bugs.sidebar.items.suggest')
					->setIcon('etu-icon-comment')
					->setUrl($this->getRouter()->generate('bugs_suggestions'))
				->end()
				->add('bugs.sidebar.items.bugs')
					->setIcon('etu-icon-warning')
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
	 * @return string
	 */
	public function getAvailablePermissions()
	{
		return array(
			'bugs.admin' => 'Administrer les bugs',
			'suggests.admin' => 'Administrer les suggestions',
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

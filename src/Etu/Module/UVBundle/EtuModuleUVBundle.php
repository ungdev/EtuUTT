<?php

namespace Etu\Module\UVBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleUVBundle extends Module
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
			->getBlock('base.sidebar.services.title')
				->add('base.sidebar.services.items.uvs')
					->setIcon('briefcase.png')
					->setUrl($this->getRouter()->generate('uvs_index'))
					->setPosition(0)
				->end();

		$this->getAdminMenuBuilder()
			->getBlock('base.admin_menu.title')
				->add('uvs.admin.menu_item')
					->setIcon('briefcase.png')
					->setUrl($this->getRouter()->generate('admin_uvs_index'))
					->setPosition(5)
				->end();
	}

	/**
	 * Module identifier (to be required by other modules)
	 *
	 * @return string
	 */
	public function getIdentifier()
	{
		return 'uv';
	}

	/**
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return 'UV';
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
	 * Module description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return 'Module de gestion des UV';
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

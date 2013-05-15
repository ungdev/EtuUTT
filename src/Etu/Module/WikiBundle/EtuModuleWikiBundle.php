<?php

namespace Etu\Module\WikiBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Definition\OrgaPermission;
use Etu\Core\CoreBundle\Framework\Definition\Permission;

class EtuModuleWikiBundle extends Module
{
	/**
	 * Must boot only for connected users
	 *
	 * @return bool|void
	 */
	public function mustBoot()
	{
		return $this->getSessionLayer()->isConnected();
	}

	/**
	 * At module boot, update the sidebar
	 */
	public function onModuleBoot()
	{
		$this->getSidebarBuilder()
			->getBlock('base.sidebar.services.title')
			->add('base.sidebar.services.items.wiki')
				->setIcon('information.png')
				->setUrl($this->getRouter()->generate('wiki_index'))
			->end();
	}

	/**
	 * @return bool
	 */
	public function isReadyToUse()
	{
		return true;
	}

	/**
	 * Module identifier (to be required by other modules)
	 *
	 * @return string
	 */
	public function getIdentifier()
	{
		return 'wiki';
	}

	/**
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return 'Wiki';
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
		return 'Wiki des associations';
	}

	/**
	 * @return array
	 */
	public function getAvailablePermissions()
	{
		return array(
			new OrgaPermission('wiki.edit', 'Peut modifier le wiki de l\'asscoation'),
			new OrgaPermission('wiki.create', 'Peut créer des pages dans le wiki de l\'asscoation'),
			new OrgaPermission('wiki.delete', 'Peut supprimer des pages dans le wiki de l\'asscoation'),
		);
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

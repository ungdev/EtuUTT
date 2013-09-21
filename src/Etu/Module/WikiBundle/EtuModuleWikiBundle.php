<?php

namespace Etu\Module\WikiBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

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
				->setItemAttribute('class', 'hidden-phone')
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
	 * Define the modules requirements (the other required modules) using their identifiers
	 *
	 * @return array
	 */
	public function getRequirements()
	{
		return array(
			'upload'
		);
	}
}

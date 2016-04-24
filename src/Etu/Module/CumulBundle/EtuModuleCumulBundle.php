<?php

namespace Etu\Module\CumulBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleCumulBundle extends Module
{
	/**
	 * At module boot, update the sidebar
	 */
	public function onModuleBoot()
	{
		$this->getSidebarBuilder()
			->getBlock('base.sidebar.services.title')
			->add('base.sidebar.services.items.table')
				->setIcon('table.png')
				->setItemAttribute('class', 'hidden-phone')
				->setUrl($this->router->generate('cumul_index'))
				->setRole('ROLE_CUMUL')
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
		return 'cumul';
	}

	/**
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return 'Cumul';
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
		return 'Cumul d\'emplois du temps';
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

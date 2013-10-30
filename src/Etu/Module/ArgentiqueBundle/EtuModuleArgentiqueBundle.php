<?php

namespace Etu\Module\ArgentiqueBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleArgentiqueBundle extends Module
{
	/**
	 * @return bool
	 */
	public function mustBoot()
	{
		return $this->getSessionLayer()->isStudent();
	}

	/**
	 * At module boot, update the sidebar
	 */
	public function onModuleBoot()
	{
		/*$this->getSidebarBuilder()
			->getBlock('base.sidebar.services.title')
				->add('base.user.menu.buckutt')
					->setIcon('duck.png')
					->setPosition(3)
					->setUrl($this->getRouter()->generate('buckutt_history'))
				->end();*/
	}

	/**
	 * Module identifier (to be required by other modules)
	 *
	 * @return string
	 */
	public function getIdentifier()
	{
		return 'argentique';
	}

	/**
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return 'Argentique';
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
		return 'Module pour l\'association Argentique (galeries de photos)';
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

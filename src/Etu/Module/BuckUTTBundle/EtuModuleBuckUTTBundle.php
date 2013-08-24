<?php

namespace Etu\Module\BuckUTTBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleBuckUTTBundle extends Module
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
		$this->getUserMenuBuilder()
			->add('base.user.menu.buckutt')
				->setIcon('duck.png')
				->setPosition(3)
				->setUrl($this->getRouter()->generate('buckutt_history'))
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
		return 'buckutt';
	}

	/**
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return 'BuckUTT';
	}

	/**
	 * Module author
	 *
	 * @return string
	 */
	public function getAuthor()
	{
		return 'Paul Chabanon et Titouan Galopin';
	}

	/**
	 * Module description
	 *
	 * @return string
	 */
	public function getDescription()
	{
		return 'Interface de gestion de son compte BuckUTT';
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

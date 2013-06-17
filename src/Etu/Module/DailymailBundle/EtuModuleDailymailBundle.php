<?php

namespace Etu\Module\DailymailBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleDailymailBundle extends Module
{
	/**
	 * @return bool
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
		$this->getUserMenuBuilder()
			->add('base.user.menu.dailymail')
				->setIcon('megaphone.png')
				->setUrl($this->router->generate('user_daymail'))
				->setPosition(4)
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
		return 'dailymail';
	}

	/**
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return 'Daymail';
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
		return 'Envoi du daymail et interface de préférences de l\'étudiant';
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

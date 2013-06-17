<?php

namespace Etu\Module\EvenementsBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleEvenementsBundle extends Module
{
	/**
	 * @return bool
	 */
	public function mustBoot()
	{
		return true;
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
		return 'events';
	}

	/**
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return 'Evènements';
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
		return 'Affichage et gestion des évènements';
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

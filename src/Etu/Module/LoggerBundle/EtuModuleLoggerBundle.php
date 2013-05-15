<?php

namespace Etu\Module\LoggerBundle;

use Etu\Core\CoreBundle\Framework\Definition\Module;

class EtuModuleLoggerBundle extends Module
{
	/**
	 * @return bool
	 */
	public function mustBoot()
	{
		return true;
	}

	/**
	 * Module is ready to use ?
	 *
	 * @return string
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
		return 'logger';
	}

	/**
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	public function getTitle()
	{
		return 'Logger';
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
		return 'Traque et créer un log de toutes les erreurs inattendues lors des visites utilisateurs';
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

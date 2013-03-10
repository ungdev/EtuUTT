<?php

namespace Etu\CoreBundle\Framework;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Module class. A module is a bndle with some more informations, as a title,
 * a description, an author and requirements.
 */
abstract class Module extends Bundle
{
	/**
	 * Module identifier (to be required by other modules)
	 *
	 * @return string
	 */
	abstract public function getIdentifier();

	/**
	 * Module author
	 *
	 * @return string
	 */
	public function getAuthor()
	{
		return 'anonymous';
	}

	/**
	 * Module description
	 *
	 * @return string
	 */
	abstract public function getDescription();

	/**
	 * Define the modules requirements (the other required modules) using the identifiers
	 *
	 * @return array
	 */
	abstract public function getRequirements();
}

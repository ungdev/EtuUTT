<?php

namespace Etu\Core\CoreBundle\Framework\Definition;

use Symfony\Component\HttpKernel\Bundle\Bundle;

/**
 * Module class. A module is a bundle with some more informations, as a title,
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
	 * Module title (describe shortly its aim)
	 *
	 * @return string
	 */
	abstract public function getTitle();

	/**
	 * Module description
	 *
	 * @return string
	 */
	abstract public function getDescription();

	/**
	 * Define the modules requirements (the other required modules) using their identifiers
	 *
	 * @return array
	 */
	abstract public function getRequirements();

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
	 * Module author
	 *
	 * @return string
	 */
	public function getRouting()
	{
		return array(
			'type' => 'annotation',
			'resource' => '@'.$this->getName().'/Controller/',
		);
	}
}

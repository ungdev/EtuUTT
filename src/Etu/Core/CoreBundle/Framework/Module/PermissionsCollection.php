<?php

namespace Etu\Core\CoreBundle\Framework\Module;

use Doctrine\Common\Collections\ArrayCollection;

/**
 * EtuUTT permissions collection.
 */
class PermissionsCollection extends ArrayCollection
{
	/**
	 * @param array $permissions
	 */
	public function __construct(array $permissions = array())
	{
		$constructed = array();

		foreach ($permissions as $permission) {
			$constructed[$permission->getName()] = $permission;
		}

		parent::__construct($constructed);
	}

	/**
	 * @param string $identifier
	 * @return bool
	 */
	public function has($identifier)
	{
		return $this->containsKey($identifier);
	}
}

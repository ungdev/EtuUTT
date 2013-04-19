<?php

namespace Etu\Core\CoreBundle\Framework\Module;

use Doctrine\Common\Collections\ArrayCollection;
use Etu\Core\CoreBundle\Framework\Definition\Module;

/**
 * EtuUTT modules collection.
 */
class ModulesCollection extends ArrayCollection
{
	/**
	 * @param string $identifier
	 * @return bool
	 */
	public function has($identifier)
	{
		return $this->containsKey($identifier);
	}

	/**
	 * @param string $identifier
	 * @return bool
	 */
	public function isEnabled($identifier)
	{
		if (! $this->has($identifier)) {
			return false;
		}

		return $this->get($identifier)->isEnabled();
	}
}

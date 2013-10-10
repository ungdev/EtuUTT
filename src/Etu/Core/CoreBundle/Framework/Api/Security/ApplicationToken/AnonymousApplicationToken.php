<?php

namespace Etu\Core\CoreBundle\Framework\Api\Security\ApplicationToken;

use Tga\Api\Common\Collection\ArrayCollection;

class AnonymousApplicationToken implements ApplicationTokenInterface
{
	/**
	 * Get the token string used by the application
	 *
	 * @return string
	 */
	public function getToken()
	{
		return false;
	}
}

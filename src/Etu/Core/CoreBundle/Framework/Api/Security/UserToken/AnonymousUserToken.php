<?php

namespace Etu\Core\CoreBundle\Framework\Api\Security\UserToken;

use Tga\Api\Common\Collection\ArrayCollection;

class AnonymousUserToken implements UserTokenInterface
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

	/**
	 * Get the expiration date
	 *
	 * @return \DateTime
	 */
	public function getExpireAt()
	{
		return \DateTime::createFromFormat('d-m-Y', '01-01-2150');
	}
}

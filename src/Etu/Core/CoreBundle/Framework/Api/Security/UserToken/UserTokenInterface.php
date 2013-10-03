<?php

namespace Etu\Core\CoreBundle\Framework\Api\Security\UserToken;

use Tga\Api\Common\Collection\ArrayCollection;

interface UserTokenInterface
{
	/**
	 * Get the token string used by the user
	 *
	 * @return string
	 */
	public function getToken();

	/**
	 * Get the expiration date
	 *
	 * @return \DateTime
	 */
	public function getExpireAt();
}

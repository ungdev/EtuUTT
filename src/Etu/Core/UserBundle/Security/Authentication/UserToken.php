<?php

namespace Etu\Core\UserBundle\Security\Authentication;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * User token for CAS authentication
 */
class UserToken extends AbstractToken
{
	public function __construct(UserInterface $user)
	{
		$roles = $user->getRolesNames();

		if(! $roles)
			$roles = array();

		parent::__construct($roles);

		$this->setUser($user);
		$this->setAuthenticated($user->isConnected());
	}

	public function getCredentials()
	{
		return '';
	}
}
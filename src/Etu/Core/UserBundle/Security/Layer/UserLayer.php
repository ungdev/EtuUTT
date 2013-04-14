<?php

/*
 * This file is part of the TgaDrupalBridgeBundle package.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Etu\Core\UserBundle\Security\Layer;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

/**
 * Layer based on Symfony security to find if the current user is an organization, a student,
 * a UTT member, an external or an anonymous.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class UserLayer
{
	/**
	 * @var User
	 */
	protected $user;

	/**
	 * @param User|null $user
	 */
	public function __construct($user)
	{
		$this->user = $user;
	}

	/**
	 * Connected user or organization
	 *
	 * @return bool
	 */
	public function isConnected()
	{
		return is_object($this->user);
	}

	/**
	 * Connected user
	 *
	 * @return bool
	 */
	public function isUser()
	{
		return $this->isConnected() && $this->user instanceof User;
	}

	/**
	 * Connected organization
	 *
	 * @return bool
	 */
	public function isOrga()
	{
		return $this->isConnected() && $this->user instanceof Organization;
	}

	/**
	 * Connected organization
	 *
	 * @return bool
	 */
	public function isOrganization()
	{
		return $this->isOrga();
	}

	/**
	 * Connected user as student
	 *
	 * @return bool
	 */
	public function isStudent()
	{
		return $this->isUser() && $this->user->getIsStudent();
	}

	/**
	 * Connected user as UTT member
	 *
	 * @return bool
	 */
	public function isUttMember()
	{
		return $this->isUser() && ! $this->user->getIsStudent() && ! $this->user->getKeepActive();
	}

	/**
	 * Connected user as external
	 *
	 * @return bool
	 */
	public function isExternal()
	{
		return $this->isUser() && $this->user->getKeepActive();
	}

	/**
	 * Get the user or the organization
	 *
	 * @return \Etu\Core\UserBundle\Entity\User|\Etu\Core\UserBundle\Entity\Organization
	 */
	public function getUser()
	{
		return $this->user;
	}
}
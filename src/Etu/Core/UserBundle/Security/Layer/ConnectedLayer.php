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
 * Abstract layer to find if the given user is an organization, a student,
 * a UTT member, an external or an anonymous.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class ConnectedLayer
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
	 * @return bool
	 */
	public function isConnected()
	{
		return is_object($this->user);
	}

	/**
	 * @return bool
	 */
	public function isUser()
	{
		return $this->isConnected() && $this->user instanceof User;
	}

	/**
	 * @return bool
	 */
	public function isOrga()
	{
		return $this->isConnected() && $this->user instanceof Organization;
	}

	/**
	 * @return bool
	 */
	public function isOrganization()
	{
		return $this->isOrga();
	}

	/**
	 * @return bool
	 */
	public function isStudent()
	{
		return $this->isUser() && $this->user->getIsStudent();
	}

	/**
	 * @return bool
	 */
	public function isUttMember()
	{
		return $this->isUser() && ! $this->user->getIsStudent() && ! $this->user->getKeepActive();
	}

	/**
	 * @return bool
	 */
	public function isExternal()
	{
		return $this->isUser() && $this->user->getKeepActive();
	}

	/**
	 * @return \Etu\Core\UserBundle\Entity\User
	 */
	public function getUser()
	{
		return $this->user;
	}
}
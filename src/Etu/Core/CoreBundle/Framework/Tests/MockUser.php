<?php

namespace Etu\Core\CoreBundle\Framework\Tests;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

/**
 * Class MockUser
 * @package Etu\Core\CoreBundle\Framework\Tests
 */
class MockUser
{
	/**
	 * @return User
	 */
	public static function createUser()
	{
		$user = new User();

		$user->setFullName('User USER');
		$user->setLogin('user');
		$user->setMail('user@utt.fr');
		$user->setIsAdmin(false);
		$user->setIsStudent(true);
		$user->setAvatar('user.png');
		$user->testingContext = true;

		$reflection = new \ReflectionObject($user);
		$property = $reflection->getProperty('id');
		$property->setAccessible(true);
		$property->setValue($user, 2);

		return $user;
	}

	/**
	 * @return Organization
	 */
	public static function createOrga()
	{
		$orga = new Organization();

		$orga->setName('Orga ORGA');
		$orga->setLogin('orga');
		$orga->setContactMail('orga@utt.fr');
		$orga->testingContext = true;

		$reflection = new \ReflectionObject($orga);
		$property = $reflection->getProperty('id');
		$property->setAccessible(true);
		$property->setValue($orga, 1);

		return $orga;
	}

	/**
	 * @return User
	 */
	public static function createAdminUser()
	{
		$user = new User();

		$user->setFullName('Admin ADMIN');
		$user->setLogin('admin');
		$user->setMail('admin@utt.fr');
		$user->setIsAdmin(true);
		$user->setAvatar('admin.png');
		$user->testingContext = true;

		$reflection = new \ReflectionObject($user);
		$property = $reflection->getProperty('id');
		$property->setAccessible(true);
		$property->setValue($user, 1);

		return $user;
	}
}
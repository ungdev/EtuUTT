<?php

namespace Etu\Core\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\FixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

class LoadUsersData implements FixtureInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function load(ObjectManager $manager)
	{
		$admin = new User();

		$admin->setFullName('Admin ADMIN');
		$admin->setLogin('admin');
		$admin->setMail('admin@utt.fr');
		$admin->setIsAdmin(true);
		$admin->setAvatar('admin.png');

		$user = new User();

		$user->setFullName('User USER');
		$user->setLogin('user');
		$user->setMail('user@utt.fr');
		$user->setIsAdmin(false);
		$user->setAvatar('user.png');

		$orga = new Organization();

		$orga->setName('Orga ORGA');
		$orga->setLogin('orga');
		$orga->setContactMail('orga@utt.fr');

		$manager->persist($admin);
		$manager->persist($user);
		$manager->persist($orga);

		$manager->flush();

		$GLOBALS['etu.test.admin'] = $admin;
		$GLOBALS['etu.test.user'] = $user;
		$GLOBALS['etu.test.orga'] = $orga;
	}
}
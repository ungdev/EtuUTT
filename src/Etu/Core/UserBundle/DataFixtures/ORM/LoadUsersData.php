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
		$admin->setBirthday(new \DateTime());
		$admin->setLastVisitHome(new \DateTime());
		$admin->setReadOnlyExpirationDate(new \DateTime());

		$user = new User();

		$user->setFullName('User USER');
		$user->setLogin('user');
		$user->setMail('user@utt.fr');
		$user->setIsAdmin(false);
		$user->setAvatar('user.png');
		$user->setBirthday(new \DateTime());
		$user->setLastVisitHome(new \DateTime());
		$user->setReadOnlyExpirationDate(new \DateTime());

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
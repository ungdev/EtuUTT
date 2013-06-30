<?php

namespace Etu\Core\UserBundle\DataFixtures\ORM;

use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;

class LoadUsersData extends AbstractFixture implements OrderedFixtureInterface
{
	/**
	 * {@inheritDoc}
	 */
	public function getOrder()
	{
		return 1;
	}

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

		$membership = new Member();

		$membership->setOrganization($orga);
		$membership->setUser($user);
		$membership->addPermission('daymail');

		$manager->persist($membership);

		$manager->flush();

		$this->addReference('user_admin', $admin);
		$this->addReference('user_user', $user);
		$this->addReference('user_orga', $orga);
		$this->addReference('user_membership', $orga);
	}
}
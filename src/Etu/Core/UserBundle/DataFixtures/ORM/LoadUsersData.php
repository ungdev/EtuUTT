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
     * {@inheritdoc}
     */
    public function getOrder()
    {
        return 1;
    }

    /**
     * {@inheritdoc}
     */
    public function load(ObjectManager $manager)
    {
        $admin = new User();

        $admin->setFullName('Admin ADMIN');
        $admin->setLogin('admin');
        $admin->setMail('admin@utt.fr');
        $admin->storeRole('ROLE_SUPERADMIN');
        $admin->setIsStudent(true);
        $admin->setPassword('$2a$12$2DKCL27MlY0KvDnI.Fofme7wRQU6AuU2kEvcemM2PbqNu.ixNY4JK'); // = 'admin'
        $admin->setAvatar('admin.png');
        $admin->setIsKeepingAccount(true);
        $admin->setIsDeletingEverything(false);
        $admin->setBirthday(new \DateTime());
        $admin->setLastVisitHome(new \DateTime());
        $admin->setReadOnlyExpirationDate(new \DateTime());
        $admin->setDaymail(false);

        $user = new User();

        $user->setFullName('User USER');
        $user->setLogin('user');
        $user->setIsStudent(true);
        $user->setPassword('$2a$12$NRBE4VdkpnngzVFZ.BA3uOTvLM/tlY54XteSi6/GK0ymEgHR2Euli'); // = 'user'
        $user->setMail('user@utt.fr');
        $user->setAvatar('user.png');
        $user->setIsDeletingEverything(true);
        $user->setIsKeepingAccount(true);
        $user->setBirthday(new \DateTime());
        $user->setLastVisitHome(new \DateTime());
        $user->setReadOnlyExpirationDate(new \DateTime());
        $user->setDaymail(false);

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
        $membership->setGroup($orga->getGroups()[0]);
        $membership->addPermission('daymail');

        $orga->addCountMembers();
        $manager->persist($membership);
        $manager->persist($orga);

        $manager->flush();

        $this->addReference('user_admin', $admin);
        $this->addReference('user_user', $user);
        $this->addReference('user_orga', $orga);
        $this->addReference('user_membership', $orga);
    }
}

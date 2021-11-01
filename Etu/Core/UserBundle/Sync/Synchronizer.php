<?php

namespace Etu\Core\UserBundle\Sync;

use Doctrine\Bundle\DoctrineBundle\Registry as Doctrine;
use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Ldap\LdapManager;

/**
 * Synchronization process manager.
 */
class Synchronizer
{
    /**
     * @var LdapManager
     */
    protected $ldap;

    /**
     * @var Doctrine
     */
    protected $doctrine;

    public function __construct(LdapManager $ldap, Doctrine $doctrine)
    {
        $this->ldap = $ldap;
        $this->doctrine = $doctrine;
    }

    /**
     * Create a process to synchronize users between the LDAP and the database
     * (import LDAP users and set old LDAP users as not in LDAP anymoe).
     *
     * @return Process
     */
    public function createUsersSyncProcess()
    {
        // LDAP
        $ldapUsers = $this->ldap->getUsers();
        $ldapLogins = [];

        foreach ($ldapUsers as $key => $ldapUser) {
            $ldapLogins[] = $ldapUser->getLogin();

            unset($ldapUsers[$key]);
            $ldapUsers[$ldapUser->getLogin()] = $ldapUser;
        }

        // Database
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        /** @var User[] $dbUsers */
        $dbUsers = $em->getRepository('EtuUserBundle:User')->findAll();
        $dbLogins = [];

        foreach ($dbUsers as $key => $dbUser) {
            $dbLogins[] = $dbUser->getLogin();

            unset($dbUsers[$key]);
            $dbUsers[$dbUser->getLogin()] = $dbUser;
        }

        // Differences
        $toAddInDb = array_diff($ldapLogins, $dbLogins);
        $toRemoveFromDb = array_diff($dbLogins, $ldapLogins);
        $toUpdate = $dbUsers;

        foreach ($toAddInDb as $key => $login) {
            unset($toAddInDb[$key]);

            $toAddInDb[$login] = $ldapUsers[$login];
        }

        foreach ($toRemoveFromDb as $key => $login) {
            unset($toRemoveFromDb[$key]);
            if ($dbUsers[$login]->getIsInLdap()) {
                $toRemoveFromDb[$login] = $dbUsers[$login];
            }
        }

        foreach ($toUpdate as $login => $dbUser) {
            if (isset($ldapUsers[$login]) && isset($dbUsers[$login])) {
                $toUpdate[$login] = [
                    'database' => $dbUsers[$login],
                    'ldap' => $ldapUsers[$login],
                ];
            } else {
                unset($toUpdate[$login]);
            }
        }

        return new Process($this->doctrine, $toAddInDb, $toRemoveFromDb, $toUpdate);
    }
}

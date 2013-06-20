<?php

namespace Etu\Core\UserBundle\Sync;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Ldap\LdapManager;

/**
 * Synchronization process manager
 */
class Synchronizer
{
	/**
	 * @var LdapManager
	 */
	protected $ldap;

	/**
	 * @var Registry
	 */
	protected $doctrine;

	/**
	 * @param LdapManager $ldap
	 * @param Registry    $doctrine
	 */
	public function __construct(LdapManager $ldap, Registry $doctrine)
	{
		$this->ldap = $ldap;
		$this->doctrine = $doctrine;
	}

	/**
	 * Create a process to synchronize users between the LDAP and the database
	 * (import LDAP users and remove old databse users)
	 *
	 * @return Process
	 */
	public function createUsersSyncProcess()
	{
		// LDAP
		$ldapUsers = $this->ldap->getUsers();
		$ldapLogins = array();

		foreach ($ldapUsers as $key => $ldapUser) {
			$ldapLogins[] = $ldapUser->getLogin();

			unset($ldapUsers[$key]);
			$ldapUsers[$ldapUser->getLogin()] = $ldapUser;
		}

		// Database
		/** @var EntityManager $em */
		$em = $this->doctrine->getManager();

		/** @var User[] $dbUsers */
		$dbUsers = $em->getRepository('EtuUserBundle:User')->findBy(array('keepActive' => false));
		$dbLogins = array();

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

			$toRemoveFromDb[$login] = $dbUsers[$login];
		}

		foreach ($toUpdate as $login => $dbUser) {
			if (isset($ldapUsers[$login]) && isset($dbUsers[$login])) {
				$toUpdate[$login] = array(
					'database' => $dbUsers[$login],
					'ldap' => $ldapUsers[$login]
				);
			} else {
				unset($toUpdate[$login]);
			}
		}

		return new Process($this->doctrine, $toAddInDb, $toRemoveFromDb, $toUpdate);
	}
}

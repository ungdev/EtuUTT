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

		foreach ($toAddInDb as $key => $login) {
			unset($toAddInDb[$key]);

			$toAddInDb[$login] = $ldapUsers[$login];
		}

		foreach ($toRemoveFromDb as $key => $login) {
			unset($toRemoveFromDb[$key]);

			$toRemoveFromDb[$login] = $dbUsers[$login];
		}

		return new Process($this->doctrine, $toAddInDb, $toRemoveFromDb);
	}

	/**
	 * Create a process to import organizations from the LDAP
	 * (this process is not able to remove organizations from database)
	 *
	 * @return Process
	 */
	public function createOrgasSyncProcess()
	{
		// LDAP
		$ldapOrgas = $this->ldap->getOrgas();
		$ldapLogins = array();

		foreach ($ldapOrgas as $key => $ldapOrga) {
			$ldapLogins[] = $ldapOrga->getLogin();

			unset($ldapOrgas[$key]);
			$ldapOrgas[$ldapOrga->getLogin()] = $ldapOrga;
		}

		// Database
		/** @var EntityManager $em */
		$em = $this->doctrine->getManager();

		/** @var Organization[] $dbOrgas */
		$dbOrgas = $em->getRepository('EtuUserBundle:Organization')->findAll();
		$dbLogins = array();

		foreach ($dbOrgas as $key => $dbOrga) {
			$dbLogins[] = $dbOrga->getLogin();

			unset($dbOrgas[$key]);
			$dbOrgas[$dbOrga->getLogin()] = $dbOrga;
		}

		// Differences
		$toAddInDb = array_diff($ldapLogins, $dbLogins);

		foreach ($toAddInDb as $key => $login) {
			unset($toAddInDb[$key]);

			$toAddInDb[$login] = $ldapOrgas[$login];
		}

		return new Process($this->doctrine, $toAddInDb, array());
	}
}

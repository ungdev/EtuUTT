<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Ldap\LdapManager;
use Imagine\Exception\InvalidArgumentException;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

class SyncLdapCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:users:sync')
			->setDescription('Synchronize users with the LDAP')
		;
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return void
	 * @throws \RuntimeException
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$dialog = $this->getHelperSet()->get('dialog');

		$output->writeln('
	Welcome to the EtuUTT users synchronisation manager

This command synchronise LDAP users with database users easily.
Each new user from LDAP will be created with basic informations
and formation informations about existing users will be updated.

For each user that don\'t exit anymore in the LDAP, the command will
ask you to keep or delete him or her.
');

		$start = $dialog->ask($output, 'Start now (y/n) [y]? ', 'y');

		if ($start != 'y') {
			return;
		}

		/*
		 * LDAP
		 */
		$output->writeln("\nLoading LDAP users ...");

		$ldap = new LdapManager(
			$this->getContainer()->getParameter('etu.ldap.host'),
			$this->getContainer()->getParameter('etu.ldap.port')
		);

		$ldapStudents = $ldap->getStudents();
		$ldapLogins = array();

		foreach ($ldapStudents as $key => $ldapStudent) {
			$ldapLogins[] = $ldapStudent->getLogin();

			unset($ldapStudents[$key]);
			$ldapStudents[$ldapStudent->getLogin()] = $ldapStudent;
		}

		/*
		 * Database
		 */
		$output->writeln('Loading database users ...');

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		/** @var User[] $dbStudents */
		$dbStudents = $em->getRepository('EtuUserBundle:User')->findAll();
		$dbLogins = array();

		foreach ($dbStudents as $key => $dbStudent) {
			$dbLogins[] = $dbStudent->getLogin();

			unset($dbStudents[$key]);
			$dbStudents[$dbStudent->getLogin()] = $dbStudent;
		}

		/*
		 * Differences
		 */
		$toAddInDb = array_diff($ldapLogins, $dbLogins);
		$toRemoveFromDb = array_diff($dbLogins, $ldapLogins);

		// Add users in database from LDAP
		if (empty($toAddInDb) && empty($toRemoveFromDb)) {
			$output->writeln("Database already sync with LDAP.");
			return;
		}

		$output->writeln("Creating avatars ...");

		$imagine = new Imagine();

		$bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($toAddInDb));
		$bar->update(0);

		$i = 0;

		foreach ($toAddInDb as $login) {
			$ldapUser = $ldapStudents[$login];

			// Resize photo
			try {
				$image = $imagine->open('http://local-sig.utt.fr/Pub/trombi/individu/'.$ldapUser->getStudentId().'.jpg');

				$image->copy()
					->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND)
					->save(__DIR__.'/../../../../../web/photos/'.$ldapUser->getLogin().'.jpg');

				$avatar = $ldapUser->getLogin().'.jpg';
			} catch (InvalidArgumentException $e) {
				$avatar = 'default-avatar.png';
			}

			$user = new User();
			$user->setAvatar($avatar);
			$user->setLogin($ldapUser->getLogin());
			$user->setFullName($ldapUser->getFullName());
			$user->setFirstName($ldapUser->getFirstName());
			$user->setLastName($ldapUser->getLastName());
			$user->setFiliere($ldapUser->getFiliere());
			$user->setFormation(ucfirst(strtolower($ldapUser->getFormation())));
			$user->setNiveau($ldapUser->getNiveau());
			$user->setMail($ldapUser->getMail());
			$user->setPhoneNumber($ldapUser->getPhoneNumber());
			$user->setRoom($ldapUser->getRoom());
			$user->setStudentId($ldapUser->getStudentId());
			$user->setTitle($ldapUser->getTitle());
			$user->setLdapInformations($ldapUser);
			$user->setIsStudent(true);
			$user->setKeepActive(false);
			$user->setCountNotifications(0);

			$em->persist($user);

			$i++;
			$bar->update($i);
		}

		$output->writeln("Importing users ...");

		$em->flush();

		$output->writeln("Imported.\n");

		if (empty($toRemoveFromDb)) {
			return;
		}
	}
}

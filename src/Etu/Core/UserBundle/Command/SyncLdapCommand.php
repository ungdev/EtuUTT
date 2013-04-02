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

		$startNow = $dialog->ask($output, 'Start now (y/n) [y]? ', 'y') == 'y';

		if (! $startNow) {
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
		$output->writeln("Loading database users ...\n");

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		/** @var User[] $dbStudents */
		$dbStudents = $em->getRepository('EtuUserBundle:User')->findBy(array('keepActive' => false));
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

		// Already sync ?
		if (empty($toAddInDb) && empty($toRemoveFromDb)) {
			$output->writeln("Database already sync with LDAP.\n");
			return;
		}

		// Need to add some users ?
		if (! empty($toAddInDb)) {
			$output->writeln("Creating avatars ...");

			$imagine = new Imagine();

			$bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($toAddInDb));
			$bar->update(0);

			$i = 0;

			foreach ($toAddInDb as $login) {
				$ldapUser = $ldapStudents[$login];

				$avatar = $ldapUser->getLogin().'.jpg';

				if (! file_exists(__DIR__.'/../../../../../web/photos/'.$ldapUser->getLogin().'.jpg')) {
					// Resize photo
					try {
						$image = $imagine->open('http://local-sig.utt.fr/Pub/trombi/individu/'.$ldapUser->getStudentId().'.jpg');

						$image->copy()
							->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND)
							->save(__DIR__.'/../../../../../web/photos/'.$ldapUser->getLogin().'.jpg');
					} catch (InvalidArgumentException $e) {
						$avatar = 'default-avatar.png';
					}
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
				$user->setPassword($this->getContainer()->get('etu.user.crypting')->encrypt($user->getPassword()));

				$em->persist($user);

				$i++;
				$bar->update($i);
			}

			$output->writeln("\nImporting users ...");

			$em->flush();

			$output->writeln("Imported.\n");
		}

		// Need to delete some users ?
		if (! empty($toRemoveFromDb)) {
			if (count($toRemoveFromDb) == 1) {
				$output->writeln('There is 1 user (`'.reset($toRemoveFromDb).'`) which is not in the LDAP but in the database.');
				$output->writeln("How do you want to deal with it?\n");

				$output->writeln("1 - Delete it");
				$output->writeln("2 - Keep it\n");

				$choice = $dialog->ask($output, 'What do you choose [1]? ', '1');

				if ($choice == '2') {
					$choice = '3';
				}
			} else {
				$output->writeln('There are '.count($toRemoveFromDb).' users which are not in the LDAP but in the database.');
				$output->writeln("How do you want to deal with them?\n");

				$output->writeln("1 - Delete all of them");
				$output->writeln("2 - Ask me for some to keep, delete the rest");
				$output->writeln("3 - Keep all of them\n");

				$choice = $dialog->ask($output, 'What do you choose [2]? ', '2');
			}

			// First choice: do nothing, all will be do after

			// Second choice: aske for which to keep
			if ($choice == '2') {
				$output->writeln("Keep blank to finish the list\n");

				$loginToKeep = null;
				$count = 0;

				while (1) {
					$loginToKeep = $dialog->ask($output, 'Login to keep: ');

					if (empty($loginToKeep)) {
						break;
					}

					if (($key = array_search($loginToKeep, $toRemoveFromDb)) !== false) {
						// Set the user as keep for the next sync
						$user = $dbStudents[$loginToKeep];
						$user->setKeepActive(true);
						$em->persist($user);

						// Unset the user from remove process
						unset($toRemoveFromDb[$key]);

						$count++;
					} else {
						$output->writeln("The login can not be found. Please retry.\n");
					}
				}

				$output->writeln($count.' user(s) to keep');

				$em->flush();
			}

			// Keep all the users
			elseif ($choice == '3') {
				$count = 0;

				foreach ($toRemoveFromDb as $login) {
					// Set the user as keep for the next sync
					$user = $dbStudents[$login];
					$user->setKeepActive(true);
					$em->persist($user);

					// Unset the user from remove process
					$key = array_search($login, $toRemoveFromDb);
					unset($toRemoveFromDb[$key]);

					$count++;
				}

				$output->writeln($count.' user(s) to keep');
			}

			// Remove the not-kept users
			foreach ($toRemoveFromDb as $login) {
				$em->remove($dbStudents[$login]);
			}

			$em->flush();

			$output->writeln("Done.\n");
		}
	}
}

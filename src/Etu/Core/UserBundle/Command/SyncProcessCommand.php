<?php

namespace Etu\Core\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Sync\Iterator\Element\ElementToImport;
use Etu\Core\UserBundle\Sync\Iterator\Element\ElementToRemove;
use Etu\Core\UserBundle\Sync\Iterator\Element\ElementToUpdate;
use Etu\Core\UserBundle\Sync\Iterator\ImportIterator;
use Etu\Core\UserBundle\Sync\Iterator\RemoveIterator;
use Etu\Core\UserBundle\Sync\Iterator\UpdateIterator;
use Etu\Core\UserBundle\Sync\Synchronizer;
use Imagine\Gd\Image;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncProcessCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:db:sync')
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
	Welcome to the EtuUTT users manager

This command helps you to synchronise database with LDAP.

For each user that don\'t exit anymore in the LDAP, the command will
ask you to keep or delete him/her.
');

		$container = $this->getContainer();


		// Users
		$output->writeln('Finding users differences ...');

		/** @var $synchronizer Synchronizer */
		$synchronizer = $container->get('etu.user.sync');

		$output->writeln('----------------------------------------');

		/** @var $usersImportIterator ImportIterator */
		$usersImportIterator = $synchronizer->createUsersSyncProcess()->getImportIterator();

		/** @var $usersRemoveIterator RemoveIterator */
		$usersRemoveIterator = $synchronizer->createUsersSyncProcess()->getRemoveIterator();

		/** @var $usersUpdateIterator UpdateIterator */
		$usersUpdateIterator = $synchronizer->createUsersSyncProcess()->getUpdateIterator();

		$output->writeln(sprintf('%s user(s) to import from LDAP', $usersImportIterator->count()));
		$output->writeln(sprintf('%s user(s) to remove/keep in database', $usersRemoveIterator->count()));
		$output->writeln(sprintf('%s user(s) to update from LDAP', $usersUpdateIterator->count()));

		$output->write("\n");

		$startNow = $dialog->ask($output, 'Start the synchronization now (y/n) [y]? ', 'y') == 'y';

		if (! $startNow) {
			$output->writeln("Aborted.\n");
			return;
		}

		// Import users
		if ($usersImportIterator->count() > 0) {
			$output->write("\n");
			$output->writeln('Importing users ...');

			/** @var $em EntityManager */
			$em = $container->get('doctrine')->getManager();

			$bde = $em->createQueryBuilder()
				->select('o')
				->from('EtuUserBundle:Organization', 'o')
				->where('o.login = :login')
				->setParameter('login', 'bde')
				->setMaxResults(1)
				->getQuery()
				->getOneOrNullResult();

			$bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, $usersImportIterator->count());
			$bar->update(0);
			$i = 1;

			/** @var $user ElementToImport */
			foreach($usersImportIterator as $user) {
				$user->import(false, $bde);
				$bar->update($i);
				$i++;
			}

			$output->writeln('Saving in database ...');
			$container->get('doctrine')->getManager()->flush();
		}


		// Updating users
		if ($usersUpdateIterator->count() > 0) {
			$output->write("\n");
			$output->writeln('Updating users ...');

			$bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, $usersUpdateIterator->count());
			$bar->update(0);
			$i = 1;

			/** @var $user ElementToUpdate */
			foreach($usersUpdateIterator as $user) {
				$user->update();
				$bar->update($i);
				$i++;
			}

			$output->writeln('Saving in database ...');
			$container->get('doctrine')->getManager()->flush();
		}


		// Remove users
		$output->write("\n\n");

		if ($usersRemoveIterator->count() > 0) {
			if ($usersRemoveIterator->count() == 1) {
				$item = $usersRemoveIterator->get(0);

				$output->writeln(sprintf(
					'There is 1 user (`%s`) which is not in the LDAP but in the database.',
					$item->getElement()->getLogin()
				));
				$output->writeln("How do you want to deal with it?\n");

				$output->writeln("1 - Delete it");
				$output->writeln("2 - Keep it\n");

				$choice = $dialog->ask($output, 'What do you choose [1]? ', '1');

				if ($choice == 2) {
					$password = $container->get('etu.user.crypting')->encrypt(
						substr($item->getElement()->getSalt(), 0, 6)
					);

					$item->keep($password);

					$output->writeln("\n1 user kept");
				} else {
					$item->remove();

					$output->writeln("\n1 user removed");
				}
			} else {
				$logins = array();

				/** @var $item ElementToRemove */
				foreach ($usersRemoveIterator as $item) {
					$logins[] = $item->getElement()->getLogin();
				}

				if ($usersRemoveIterator->count() <= 20) {
					$output->writeln(sprintf(
						'There are %s users which are not in the LDAP but in the database (`%s`).',
						$usersRemoveIterator->count(), implode('`, `', $logins)
					));
				} else {
					$output->writeln(sprintf(
						'There are %s users which are not in the LDAP but in the database.',
						$usersRemoveIterator->count()
					));
				}

				$output->writeln("How do you want to deal with them?\n");

				$choice = 0;

				while (! in_array($choice, array(1, 2, 3))) {
					$output->writeln("1 - Delete all of them");
					$output->writeln("2 - Ask me for some to keep, delete the rest");
					$output->writeln("3 - Keep all of them\n");
					$output->writeln("4 - Display the list\n");

					$choice = $dialog->ask($output, 'What do you choose [2]? ', '2');

					if ($choice == 4) {
						$names = array();

						foreach ($usersRemoveIterator as $user) {
							$names[] = $user->getDatabaseUser()->getFullName().' ('.$user->getDatabaseUser()->getLogin().')';
						}

						$output->writeln(implode("\n", $names)."\n");
					}
				}

				$remove = array();
				$keep = array();

				if ($choice == 1) {
					$remove = $usersRemoveIterator->all();
				} elseif ($choice == 2) {
					$remove = $usersRemoveIterator->all();

					$output->writeln("Keep blank to finish the list\n");

					$loginToKeep = null;

					while (1) {
						$loginToKeep = $dialog->ask($output, 'Login to keep: ');

						if (empty($loginToKeep)) {
							break;
						}

						if (($key = array_search($loginToKeep, $logins)) !== false) {
							$keep[] = $usersRemoveIterator->get($key);
							unset($remove[$key]);
						} else {
							$output->writeln("The login can not be found in the list. Please retry.\n");
						}
					}
				} else {
					$keep = $usersRemoveIterator->all();
				}

				// Keep
				foreach ($keep as $item) {
					$password = $container->get('etu.user.crypting')->encrypt(
						substr($item->getElement()->getSalt(), 0, 6)
					);

					$item->keep($password);
				}

				$container->get('doctrine')->getManager()->flush();

				// Remove
				foreach ($remove as $item) {
					$item->remove();
				}

				$container->get('doctrine')->getManager()->flush();

				$output->writeln(sprintf("\n%s user(s) kept, %s user(s) removed", count($keep), count($remove)));
			}
		}

		$output->writeln("Done.\n");
	}
}

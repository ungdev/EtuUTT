<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Sync\Synchronizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncStatusCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:users:sync-status')
			->setDescription('Find differences between LDAP and database.')
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
		$container = $this->getContainer();


		// Users

		$output->writeln("\nFinding users differences ...");

		/** @var $synchronizer Synchronizer */
		$synchronizer = $container->get('etu.user.sync');

		$output->writeln('----------------------------------------');

		$output->writeln(sprintf(
			'%s user(s) to import from LDAP',
			$synchronizer->createUsersSyncProcess()->getImportIterator()->count()
		));
		$output->writeln(sprintf(
			'%s user(s) to remove/keep in database',
			$synchronizer->createUsersSyncProcess()->getRemoveIterator()->count()
		));

		$output->write("\n");


		// Organizations

		$output->writeln("\nFinding organizations differences ...");

		/** @var $synchronizer Synchronizer */
		$synchronizer = $container->get('etu.user.sync');

		$output->writeln('----------------------------------------');

		$output->writeln(sprintf(
			'%s organization(s) to import from LDAP',
			$synchronizer->createOrgasSyncProcess()->getImportIterator()->count()
		));

		$output->write("\n");
	}
}

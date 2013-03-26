<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Ldap\LdapManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncLdapCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:sync:users')
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

		$output->writeln("\n");
		$output->writeln("Loading LDAP users ...");

		$ldap = new LdapManager(
			$this->getContainer()->getParameter('etu.ldap.host'),
			$this->getContainer()->getParameter('etu.ldap.port')
		);

		$students = $ldap->getStudents();

		var_dump(count($students));
	}
}

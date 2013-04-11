<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

class CreateUserCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:users:create')
			->setDescription('Create a user')
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

This command helps you to create a user (which be accessible using external connexion).
');

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		$user = new User();
		$user->setKeepActive(true);
		$user->setLogin($dialog->ask($output, 'Identifier: '));
		$user->setFirstName($dialog->ask($output, 'First name: '));
		$user->setLastName($dialog->ask($output, 'Last name: '));
		$user->setFullName($user->getFirstName().' '.$user->getLastName());
		$user->setPassword($this->getContainer()->get('etu.user.crypting')->encrypt($dialog->ask($output, 'Password: ')));
		$user->setMail($dialog->ask($output, 'Public e-mail: '));

		$em->persist($user);
		$em->flush();

		$output->writeln("\nDone.\n");
	}
}

<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Doctrine\ORM\EntityManager;

class CreateOrgaCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:orgas:create')
			->setDescription('Create an organization')
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
	Welcome to the EtuUTT organizations manager

This command helps you to create an organization using the command.
');

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		$orga = new Organization();
		$orga->setName($dialog->ask($output, 'Name: '));
		$orga->setLogin($dialog->ask($output, 'Identifier: '));

		$em->persist($orga);
		$em->flush();

		$output->writeln("\nDone.\n");
	}
}

<?php

namespace Etu\Core\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\Badge;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Sync\Synchronizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class CreateBadgeCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:users:create-badge')
			->setDescription('Create a badge (see documentation)')
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
	Welcome to the EtuUTT badges manager

This command helps you to create a simple badge to use in your module. See the documentation for
more informations.
');

		/** @var EntityManager $em */
		$em = $this->getContainer()->get('doctrine')->getManager();

		$badge = new Badge(
			$dialog->ask($output, 'Serie: '),
			$dialog->ask($output, 'Name: '),
			$dialog->ask($output, 'Description: '),
			$dialog->ask($output, 'Picture: '),
			$dialog->ask($output, 'Level [1]: ', 1),
			$dialog->ask($output, 'Number of levels [1]: ', 1)
		);

		$em->persist($badge);
		$em->flush();

		$output->writeln("\nDone.\n");
	}
}

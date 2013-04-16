<?php

namespace Etu\Core\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Schedule\ScheduleApi;
use Etu\Core\UserBundle\Sync\Synchronizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;

class SyncScheduleCommand extends ContainerAwareCommand
{
	/**
	 * Configure the command
	 */
	protected function configure()
	{
		$this
			->setName('etu:db:sync-schedule')
			->setDescription('Synchronize officials schedules with database schedules.')
			->addOption('force', 'f', InputOption::VALUE_OPTIONAL)
		;
	}

	/**
	 * @param \Symfony\Component\Console\Input\InputInterface $input
	 * @param \Symfony\Component\Console\Output\OutputInterface $output
	 * @return void
	 * @throws \RuntimeException
	 *
	 * @todo
	 */
	protected function execute(InputInterface $input, OutputInterface $output)
	{
		$container = $this->getContainer();

		$output->writeln('
	This command is not available yet.
');

		return;

		$output->writeln('
	Welcome to the EtuUTT schedules manager

This command helps you to synchronise database\'s with officials schedules.

The command will NOT remove users modification on their schedules by default.
To force this, use --force.
');

		$output->writeln("\nCreating officials schedules (this will last long) ...");
		$output->writeln("------------------------------------------------------------\n");

		$scheduleApi = new ScheduleApi();
		$content = array();

		for ($page = 1; true; $page++) {
			// Requesting CRI API
			$output->writeln('Requesting CRI API (page '.$page.') ...');

			$pageContent = $scheduleApi->findPage($page);

			if (empty($pageContent)) {
				break;
			}

			$content = array_merge($content, $pageContent);
		}

		var_dump(count($content));
		exit;
	}
}

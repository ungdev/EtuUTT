<?php

namespace Etu\Core\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Schedule\Model\Course;
use Etu\Core\UserBundle\Schedule\ScheduleApi;
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
			->setName('etu:users:sync-schedule')
			->setDescription('Synchronize officials schedules with database schedules.')
			->addOption('force', 'f', InputOption::VALUE_NONE, 'Force to re-downlaod files')
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
		$output->writeln('
	Welcome to the EtuUTT schedules manager

This command helps you to synchronise database\'s with officials schedules.

By default, the command will use cached version of schedules. If you want to
re-download schedules, use --force or -f.
');

		$output->writeln("\nGetting officials schedules (this will last long) ...");
		$output->writeln("------------------------------------------------------------\n");

		$tempDirectory = __DIR__.'/../Resources/temp';

		if (! file_exists($tempDirectory.'/schedules')) {
			mkdir($tempDirectory.'/schedules');
		}

		$scheduleApi = new ScheduleApi();

		$content = array();
		$readingFromCacheWritten = false;

		for ($page = 1; true; $page++) {
			if (! file_exists($tempDirectory.'/schedules/page-'.$page.'.temp') OR $input->getOption('force')) {
				// Requesting CRI API
				$output->writeln('Requesting CRI API (page '.$page.') ...');

				$pageContent = $scheduleApi->findPage($page);
				$readingFromCacheWritten = false;

				file_put_contents($tempDirectory.'/schedules/page-'.$page.'.temp', serialize($pageContent));
			} else {
				if (! $readingFromCacheWritten) {
					$output->writeln('Reading from cache ...');
					$readingFromCacheWritten = true;
				}

				$pageContent = unserialize(file_get_contents($tempDirectory.'/schedules/page-'.$page.'.temp'));
			}

			if (empty($pageContent)) {
				break;
			}

			/** @var $content Course[] */
			$content = array_merge($content, $pageContent);
		}

		$output->writeln('Loading users from database ...');

		/** @var $em EntityManager */
		$em = $this->getContainer()->get('doctrine')->getManager();

		/** @var $users User[] */
		$users = $em->getRepository('EtuUserBundle:User')->findAll();

		foreach ($users as $key => $user) {
			unset($users[$key]);

			$users[$user->getStudentId()] = $user;
		}

		$output->writeln('Deleteing old schedules ...');

		$em->createQuery('DELETE FROM EtuUserBundle:Course')->execute();

		$output->writeln('Creating schedules ...');

		$bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($content));
		$bar->update(0);
		$i = 1;

		foreach ($content as $criCourse) {
			if (! isset($users[$criCourse->getStudentId()])) {
				continue;
			}

			$course = new \Etu\Core\UserBundle\Entity\Course();
			$course->setUser($users[$criCourse->getStudentId()]);
			$course->setDay(str_replace('day_', '', $criCourse->getDay()));
			$course->setStart($criCourse->getStart());
			$course->setEnd($criCourse->getEnd());
			$course->setUv($criCourse->getUv());
			$course->setType($criCourse->getType());
			$course->setWeek($criCourse->getWeek());

			if ($criCourse->getRoom()) {
				$course->setRoom($criCourse->getRoom());
			}

			$em->persist($course);

			// Flush each 500 elements
			if ($i % 500 == 0) {
				$em->flush();
			}

			$bar->update($i);
			$i++;
		}

		$em->flush();
		$bar->update(count($content));

		$output->writeln("\nDone.\n");
	}
}

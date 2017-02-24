<?php

namespace Etu\Core\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Schedule\Browser\CriBrowser;
use Etu\Core\UserBundle\Schedule\Model\Course;
use Etu\Core\UserBundle\Schedule\ScheduleApi;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncScheduleCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:users:sync-schedule')
            ->setDescription('Synchronize officials schedules with database schedules.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('
	Welcome to the EtuUTT schedules manager

This command helps you to synchronise database\'s with officials schedules.
');

        $output->writeln("\nGetting officials schedules ...");
        $output->writeln('------------------------------------------------------------');

        $tempDirectory = __DIR__.'/../Resources/temp';

        if (!file_exists($tempDirectory.'/schedules')) {
            mkdir($tempDirectory.'/schedules');
        }

        $scheduleApi = new ScheduleApi();

        $output->writeln('Requesting CRI API ('.CriBrowser::ROOT_URL.') ...');

        $pageContent = $scheduleApi->findPage(1);
        $content = $pageContent['courses'];

        $bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, $pageContent['paging']->totalPages);
        $bar->update(1);

        for ($page = 2; true; ++$page) {
            $pageContent = $scheduleApi->findPage($page);
            $courses = $pageContent['courses'];

            if (empty($courses)) {
                break;
            }

            /** @var $content Course[] */
            $content = array_merge($content, $courses);

            $bar->update($page);
        }

        $output->writeln('Loading users from database ...');

        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine')->getManager();

        /** @var $users User[] */
        $users = $em->getRepository('EtuUserBundle:User')->findAll();

        foreach ($users as $key => $user) {
            unset($users[$key]);

            if (!empty($user->getStudentId())) {
                $users[$user->getStudentId()] = $user;
            }
        }

        $output->writeln('Creating schedules ...');

        $bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($content));
        $bar->update(0);
        $i = 1;

        foreach ($content as $criCourse) {
            if (!isset($users[$criCourse->getStudentId()])) {
                continue;
            }

            $course = new \Etu\Core\UserBundle\Entity\Course();
            $course->setUser($users[$criCourse->getStudentId()]);
            $course->setDay(str_replace('day_', '', $criCourse->getDay()));
            $course->setStart($criCourse->getStart());
            $course->setEnd($criCourse->getEnd());
            $course->setUv($criCourse->getUv());
            $course->setType($criCourse->getType());

            if($criCourse->getWeek()) {
                $course->setWeek($criCourse->getWeek());
            } else {
                $course->setWeek('T');
            }

            if ($criCourse->getRoom()) {
                $course->setRoom($criCourse->getRoom());
            }

            $em->persist($course);

            $bar->update($i);
            ++$i;
        }

        $bar->update(count($content));

        $output->writeln('Deleting old schedules ...');
        $em->createQuery('DELETE FROM EtuUserBundle:Course')->execute();

        $output->writeln('Inserting new schedules ...');
        $em->flush();

        $output->writeln("\nDone.\n");
    }
}

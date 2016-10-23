<?php

namespace Etu\Core\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class ClearStatsCommand extends ContainerAwareCommand
{
    /**
     * @var string
     */
    protected $modulesDirectory;

    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:stats:clear')
            ->setDescription('Clear the EtuUTT stats.')
        ;
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var $em EntityManager */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $calls = $em->getRepository('TgaAudienceBundle:VisitorCall')->findAll();
        $sessions = $em->getRepository('TgaAudienceBundle:VisitorSession')->findAll();

        foreach ($calls as $call) {
            $em->remove($call);
        }

        foreach ($sessions as $session) {
            $em->remove($session);
        }

        $em->flush();

        $output->writeln(sprintf("%s calls and %s sessions deleted.\n", count($calls), count($sessions)));
    }
}

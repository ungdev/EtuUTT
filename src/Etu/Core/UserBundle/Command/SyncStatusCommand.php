<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Sync\Synchronizer;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
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
            ->setDescription('Find differences between LDAP and database.');
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

        $output->writeln(
            '
	Welcome to the EtuUTT users manager

This command helps you to check the state of synchronization between LDAP
and database.
'
        );

        // Users

        $output->writeln("\nFinding users differences ...");

        /** @var $synchronizer Synchronizer */
        $synchronizer = $container->get('etu.user.sync');

        $output->writeln('----------------------------------------');

        $output->writeln(
            sprintf(
                '%s user(s) to import from LDAP',
                $synchronizer->createUsersSyncProcess()->getImportIterator()->count()
            )
        );
        $output->writeln(
            sprintf(
                '%s user(s) to remove/keep in database',
                $synchronizer->createUsersSyncProcess()->getRemoveIterator()->count()
            )
        );

        $output->write("\n");
    }
}

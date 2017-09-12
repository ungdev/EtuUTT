<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SyncDaymailCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:users:sync-daymail')
            ->setDescription('Synchronize subscription to daymail');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $output->writeln('
	Welcome to the EtuUTT daymail updater

This command helps you to synchronise daymail subscription from EtuUTT DB to sympa lists.

This command should not be necessary because *etu:users:sync* already register new users.
But if you think that emails couldn\'t be sent from EtuUTT to sympa to update it,
then you can run this command to ensure that everyone registered in the db is registered under sympa.
However, note that this command cannot unregister user that are not in the ldap anymore,
so you will have to delete them manually on Sympa web UI.
');

        $startNow = $helper->ask($input, $output, new Question('Start the synchronization now (y/n) [y]? ', 'y')) == 'y';

        if (!$startNow) {
            $output->writeln("Aborted.\n");

            return;
        }

        $container = $this->getContainer();

        $sympaCommands = '';

        $em = $this->getContainer()->get('doctrine')->getManager();
        $users = $em->getRepository('EtuUserBundle:User')->findAll();

        foreach ($users as $key => $user) {
            if (!empty($user->getMail())) {
                if ($user instanceof User && $user->getDaymail()) {
                    $sympaCommands .= 'QUIET ADD daymail '.$user->getMail().' '.$user->getFullName()."\n";
                } else {
                    $sympaCommands .= 'QUIET DELETE daymail '.$user->getMail()."\n";
                }
            }
        }

        // Update daymail list
        $mailer = $this->getContainer()->get('mailer');
        $output->writeln("Commands sent to sympa to update daymail list: \n".$sympaCommands."\n\n");
        $message = \Swift_Message::newInstance('Daymail subscription')
           ->setFrom(['ung@utt.fr' => 'UNG'])
           ->setTo(['sympa@utt.fr'])
           ->setBody($sympaCommands);
        $result = $mailer->send($message);

        $output->writeln("Done.\n");
    }
}

<?php

namespace Etu\Core\UserBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SendNewcomersMailCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('etu:newcomer:mail')
            ->setDescription('Send mail to newcomer')
            ->setHelp('Send a mail to new comers, to make them discover the etu web site.');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine');
        $users = $em->getRepository('EtuUserBundle:User')->findby(
            ['firstLogin' => 0]
        );

        $twig = $this->getContainer()->get('twig');
        $message = \Swift_Message::newInstance()
            ->setSubject('Hello world')
            ->setFrom(['ung@utt.fr' => 'UNG'])
            ->setBody($twig->render('EtuUserBundle:Mail:newcomer_mail.html.twig'), 'text/html');
        foreach ($users as $user) {
            $message->setTo($user->getMail());
            $output->writeln($user->getFirstName());
            $this->getContainer()->get('mailer')->send($message);
        }
    }
}

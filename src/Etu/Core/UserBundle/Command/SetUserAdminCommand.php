<?php

namespace Etu\Core\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class SetUserAdminCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:users:set-admin')
            ->setDescription('Promote given user as global super admin');
    }

    /**
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $helper = $this->getHelper('question');

        $output->writeln('
	Welcome to the EtuUTT users grant tool

This command will help you to promote given user as global admin.
');

        $user = null;

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        while (!$user instanceof User) {
            $login = $helper->ask($input, $output, new Question('User login: '));

            $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

            if (!$user) {
                $output->writeln("The given login can not be found. Please retry.\n");
            }
        }

        $user->storeRole('ROLE_SUPERADMIN');

        $em->persist($user);
        $em->flush();

        $output->writeln('The user '.$user->getLogin()." has been promoted as global admin.\n");
    }
}

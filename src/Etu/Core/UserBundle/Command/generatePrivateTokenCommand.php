<?php

namespace Etu\Core\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class generatePrivateTokenCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:users:generate-private-token')
            ->setDescription('Generate private token for every user');
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
	Welcome to the EtuUTT users manager

This command will generate a random private Token for all user.
');

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        foreach ($em->getRepository('EtuUserBundle:User')->findAll() as $user) {
            $user->generatePrivateToken();
            $em->persist($user);

            $output->writeln($user->getFullName().' mis à jour !');
        }
        $em->flush();
    }
}

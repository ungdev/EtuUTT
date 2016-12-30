<?php

namespace Etu\Core\UserBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Entity\Organization;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateOrgaCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:orgas:create')
            ->setDescription('Create an organization');
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

        $output->writeln(
            '
	Welcome to the EtuUTT organizations manager

This command helps you to create an organization using the command.
'
        );

        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $orga = new Organization();
        $orga->setName($helper->ask($input, $output, new Question('Name: ')));
        $orga->setLogin($helper->ask($input, $output, new Question('Identifier: ')));

        $em->persist($orga);
        $em->flush();

        $output->writeln("\nDone.\n");
    }
}

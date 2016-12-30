<?php

namespace Etu\Core\ApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthScope;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateScopeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('etu:oauth:create-scope')
            ->setDescription('Create a OAuth2 scope')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $helper = $this->getHelper('question');

        $scope = new OauthScope();
        $scope->setName($helper->ask($input, $output, new Question('Name: ')));
        $scope->setDescription($helper->ask($input, $output, new Question('Description: ')));
        $scope->setIsDefault(false);

        $em->persist($scope);
        $em->flush();

        $output->writeln('<fg=green>Scope '.$scope->getName().' created</fg=green>');
    }
}

<?php

namespace Etu\Core\ApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthScope;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateScopeCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('oauth:create-scope')
            ->setDescription('Create a OAuth2 scope')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $dialog = $this->getHelperSet()->get('dialog');

        $scope = new OauthScope();
        $scope->setScope($dialog->ask($output, 'Name: '));
        $scope->setDescription($dialog->ask($output, 'Description: '));
        $scope->setIsDefault(false);

        $em->persist($scope);
        $em->flush();

        $output->writeln('<fg=green>Scope ' . $scope->getScope() . ' created</fg=green>');
    }
}
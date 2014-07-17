<?php

namespace Etu\Core\ApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class CreateClientCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('oauth:create-client')
            ->setDescription('Create a OAuth2 client')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $dialog = $this->getHelperSet()->get('dialog');

        $client = new OauthClient();
        $client->setClientId($dialog->ask($output, 'Identifier: '));
        $client->setName($dialog->ask($output, 'Name: '));
        $client->setUserId($dialog->ask($output, 'Owner ID: '));
        $client->setRedirectUri($dialog->ask($output, 'Redirect URL: '));

        $client->setClientSecret(md5(uniqid(time(), true)));

        $em->persist($client);
        $em->flush();

        $output->writeln('<fg=green>Client ' . $client->getClientId() . ' created with secret ' . $client->getClientSecret() . '</fg=green>');
    }
}
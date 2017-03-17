<?php

namespace Etu\Core\ApiBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\ApiBundle\Entity\OauthClient;
use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;

class CreateClientCommand extends ContainerAwareCommand
{
    protected function configure()
    {
        $this
            ->setName('etu:oauth:create-client')
            ->setDescription('Create a OAuth2 client');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $helper = $this->getHelper('question');

        $client = new OauthClient();
        $client->setName($helper->ask($input, $output, new Question('Name: ')));
        $user = null;
        while (!$user instanceof User) {
            $login = $helper->ask($input, $output, new Question('Owner login: '));

            $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $login]);

            if (!$user) {
                $output->writeln("The given login can not be found. Please retry.\n");
            }
        }
        $client->setUser($user);
        $client->setRedirectUri($helper->ask($input, $output, new Question('Redirect URL: ')));

        $client->generateClientId();
        $client->generateClientSecret();

        $em->persist($client);
        $em->flush();

        $output->writeln('<fg=green>Client '.$client->getClientId().' created with secret '.$client->getClientSecret().'</fg=green>');
    }
}

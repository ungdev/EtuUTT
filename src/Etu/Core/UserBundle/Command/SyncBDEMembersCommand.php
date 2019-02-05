<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class SyncBDEMembersCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:users:sync-bde-members')
            ->setDescription('Synchronize bde membership from BDE member database.');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $container = $this->getContainer();
        $em = $container->get('doctrine')->getManager();

        $output->writeln('
	Welcome to the EtuUTT BDE member manager

This command helps you to synchronise database with BDE member db.
');

        // Pull all user informations
        $output->writeln("\nGetting BDE member list from ".$container->getParameter('etu.dolibarr.host').'... (may take a few minutes)');
        $data = [];
        $url = $container->getParameter('etu.dolibarr.host').'/api/index.php/members?limit=500&DOLAPIKEY='.$container->getParameter('etu.dolibarr.key').'&page=';
        $page = 0;
        while($data_temp = @file_get_contents($url.$page))
        {
            $data = array_merge($data, json_decode($data_temp));
            $page++;
        }

        $output->writeln('Sync it with our database...');
        $bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, count($data));
        $i = 0;
        foreach ($data as $key => $userdata) {
            $bar->update(++$i);

            // Try to match this userdata with our database
            if (!empty($userdata->login)) {
                // Try to find user with login
                $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $userdata->login]);
            }
            if (!$user && !empty($userdata->email)) {
                // Try to find user with official email
                $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['mail' => $userdata->email]);
            }
            if (!$user && !empty($userdata->personnalMail)) {
                // Try to find user with personnal email
                $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['personnalMail' => $userdata->email]);
            }

            // Update bde membership if found
            if ($user) {
                $user->setBdeMembershipEnd($userdata->datefin ? \DateTime::createFromFormat('U', $userdata->datefin) : null);
                $em->persist($user);

                // Flush every X to not reach memory limit
                if ($i % 25 == 0) {
                    $em->flush();
                }
            }
        }

        $output->writeln("\nDone.\n");
    }
}

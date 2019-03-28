<?php

namespace Etu\Core\UserBundle\Command;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\OrganizationGroup;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class OrganizationGroupMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:migration:organizationGroup')
            ->setDescription('Migrate existing group membership to group & add slug to group.');
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
        $output->writeln('This command will generate organization group membership from older relation');

        // First create default groups
        $em = $this->getContainer()->get('doctrine')->getManager();
        $organisations = $em->getRepository('EtuUserBundle:Organization')->findAll();

        foreach ($organisations as $orga) {
            $output->write('- Organisation '.$orga->getName());

            $bureauGroup = new OrganizationGroup();
            $bureauGroup->setName('Bureau')
                ->setOrganization($orga);
            $em->persist($bureauGroup);

            $membreGroup = new OrganizationGroup();
            $membreGroup->setName('Membres')
                ->setOrganization($orga);
            $em->persist($membreGroup);
            $em->flush();

            $i = 0;

            foreach ($orga->getMemberships() as $membership) {
                if ($membership->getGroup()) {
                    continue;
                }

                if ($membership->isFromBureau()) {
                    $membership->setGroup($bureauGroup);
                } else {
                    $membership->setGroup($membreGroup);
                }
                $em->persist($membership);
                ++$i;
            }
            $em->flush();

            $output->writeln(" : $i membership migrated");
        }

        $em->flush();
    }
}

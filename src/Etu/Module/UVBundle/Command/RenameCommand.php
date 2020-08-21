<?php

namespace Etu\Module\UVBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\DependencyInjection\Container;

class RenameCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:ue:rename')
            ->setDescription('Rename an UE')
            ->addArgument('oldcode', InputArgument::REQUIRED, 'The old UE code must be given')
            ->addArgument('newcode', InputArgument::REQUIRED, 'The new UE code must be given');
    }

    /**
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $old = $input->getArgument('oldcode');
        $new = $input->getArgument('newcode');

        /*
         * Init the components
         */
        /** @var Container $container */
        $container = $this->getContainer();

        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();
        $ue = $em->getRepository('EtuModuleUVBundle:UV')->findOneBy(['code' => $old]);
        if (!$ue) {
            throw new \RuntimeException('That UE could not be found.');
        }

        $ue->setCode($new);
        $em->persist($ue);
        $em->flush();
        $output->writeln("Done.\n");
    }
}

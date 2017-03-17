<?php

namespace Etu\Module\UVBundle\Command;

use Doctrine\ORM\EntityManager;
use Etu\Core\UserBundle\Command\Util\ProgressBar;
use Etu\Module\UVBundle\Entity\UV;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Question\Question;
use Symfony\Component\DependencyInjection\Container;

class SyncCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:uv:sync')
            ->setDescription('Synchronize imported information with the database');
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
	Welcome to the EtuUTT UV manager

This command helps you to syncronize downloaded UV with the database.

If you did not import the guide previously, please use

	etu:uv:import url

This manager is very carfeul: it won\'t remove any UV from the
databsse, it will only update them.
');

        /*
         * Init the components
         */
        /** @var Container $container */
        $container = $this->getContainer();

        /** @var EntityManager $em */
        $em = $container->get('doctrine')->getManager();

        $helper = $this->getHelper('question');

        /*
         * Loading registry
         */
        $output->writeln('Loading registry ...');

        if (!file_exists(__DIR__.'/../Resources/objects/registry.json')) {
            throw new \RuntimeException(
                'The registry can not be loaded. Please run "etu:uv:import url" to create it.'
            );
        }

        /** @var UV[] $registry */
        $registry = unserialize(file_get_contents(__DIR__.'/../Resources/objects/registry.json'));

        if (!$registry || !is_array($registry)) {
            throw new \RuntimeException(
                'The registry can not be loaded as its file is corrupted. Please run "etu:uv:import url" to recreate it.'
            );
        }

        $regitryCodes = [];

        foreach ($registry as $key => $registryUV) {
            unset($registry[$key]);
            $registry[$registryUV->getCode()] = $registryUV;

            $regitryCodes[] = $registryUV->getCode();
        }

        /*
         * Loading database
         */
        $output->writeln('Loading database ...');

        /** @var UV[] $database */
        $database = $em->getRepository('EtuModuleUVBundle:UV')->findBy(['isOld' => false]);

        $databaseCodes = [];

        foreach ($database as $key => $databaseUV) {
            unset($database[$key]);
            $database[$databaseUV->getCode()] = $databaseUV;

            $databaseCodes[] = $databaseUV->getCode();
        }

        /*
         * Finding differences
         */
        $output->writeln("\nFinding differences ...");
        $output->writeln('----------------------------------------');

        $toAdd = array_diff($regitryCodes, $databaseCodes);
        $toRemove = array_diff($databaseCodes, $regitryCodes);
        $toUpdate = [];

        $codes = array_intersect($databaseCodes, $regitryCodes);

        foreach ($codes as $code) {
            if (!$this->areEquals($registry[$code], $database[$code])) {
                $toUpdate[] = $code;
            }
        }

        $toAddCount = count($toAdd);
        $toRemoveCount = count($toRemove);
        $toUpdateCount = count($toUpdate);

        $output->writeln(sprintf('%s UV to add', $toAddCount));
        $output->writeln(sprintf('%s UV to update', $toUpdateCount));
        $output->writeln(sprintf('%s UV to set as not existent anymore', $toRemoveCount));

        $output->write("\n");

        if ($toAddCount + $toRemoveCount + $toUpdateCount == 0) {
            $output->writeln("Nothing to sync.\n");

            return;
        }

        $startNow = $helper->ask($input, $output, new Question('Start the synchronization now (y/n) [y]? ', 'y')) == 'y';

        if (!$startNow) {
            $output->writeln("Aborted.\n");

            return;
        }

        // Add
        if ($toAddCount > 0) {
            $output->writeln('Adding ...');

            $bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, $toAddCount);
            $bar->update(0);
            $i = 1;

            foreach ($toAdd as $code) {
                $entity = $registry[$code];

                $em->persist($entity);

                $bar->update($i);
                ++$i;

                if ($i % 10 == 0) {
                    $em->flush();
                }
            }

            $em->flush();

            $output->writeln("\n");
        }

        // Update
        if ($toUpdateCount > 0) {
            $output->writeln('Updating ...');

            $bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, $toUpdateCount);
            $bar->update(0);
            $i = 1;

            foreach ($toUpdate as $code) {
                $regEntity = $registry[$code];
                $entity = $database[$code];

                $entity
                    ->setCategory($regEntity->getCategory())
                    ->setName($regEntity->getName())
                    ->setCm($regEntity->getCm())
                    ->setTd($regEntity->getTd())
                    ->setTp($regEntity->getTp())
                    ->setThe($regEntity->getThe())
                    ->setAutomne($regEntity->getAutomne())
                    ->setPrintemps($regEntity->getPrintemps())
                    ->setCredits($regEntity->getCredits())
                    ->setObjectifs($regEntity->getObjectifs())
                    ->setProgramme($regEntity->getProgramme());

                $em->persist($entity);

                if ($i % 10 == 0) {
                    $em->flush();
                }

                $bar->update($i);
                ++$i;
            }

            $em->flush();

            $output->writeln("\n");
        }

        // Remove (set as old)
        if ($toRemoveCount > 0) {
            $output->writeln('Setting as old ...');

            $bar = new ProgressBar('%fraction% [%bar%] %percent%', '=>', ' ', 80, $toRemoveCount);
            $bar->update(0);
            $i = 1;

            foreach ($toRemove as $code) {
                $entity = $database[$code];
                $entity->setIsOld(true);

                $em->persist($entity);

                if ($i % 10 == 0) {
                    $em->flush();
                }

                $bar->update($i);
                ++$i;
            }

            $em->flush();

            $output->writeln("\n");
        }

        $output->writeln("Done.\n");
    }

    /**
     * @param UV $registryUV
     * @param UV $databaseUV
     *
     * @return bool
     */
    protected function areEquals(UV $registryUV, UV $databaseUV)
    {
        $values = [
            'Category',
            'Name',
            'Cm',
            'Td',
            'Tp',
            'The',
            'Automne',
            'Printemps',
            'Credits',
            'Objectifs',
            'Programme',
        ];

        foreach ($values as $value) {
            $value = 'get'.$value;
            if ($registryUV->$value() !== $databaseUV->$value()) {
                return false;
            }
        }

        return true;
    }
}

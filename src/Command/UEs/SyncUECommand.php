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

class SyncUECommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:ue:sync')
            ->setDescription('Synchronize imported information with the database');
    }

    /**
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln('
	Welcome to the EtuUTT UE manager

This command helps you to syncronize downloaded UE with the database.

If you did not import the guide previously, please use

	etu:uv:import url

This manager is very carfeul: it won\'t remove any UE from the
database, it will only update them or create them.
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
            throw new \RuntimeException('The registry can not be loaded. Please run "etu:ue:import url" to create it.');
        }

        /** @var UV[] $registry */
        $registry = unserialize(file_get_contents(__DIR__.'/../Resources/objects/registry.json'));

        if (!$registry || !is_array($registry)) {
            throw new \RuntimeException('The registry can not be loaded as its file is corrupted. Please run "etu:ue:import url" to recreate it.');
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

        $output->writeln(sprintf('%s UE to add', $toAddCount));
        foreach ($toAdd as $ue) {
            $output->write($ue.' ');
        }
        $output->writeln(sprintf("\n%s UE to update", $toUpdateCount));
        $output->writeln(sprintf('%s UE to set as not existent anymore', $toRemoveCount));
        foreach ($toRemove as $ue) {
            $output->write($ue.' ');
        }

        $output->write("\n");

        if (0 == $toAddCount + $toRemoveCount + $toUpdateCount) {
            $output->writeln("Nothing to sync.\n");

            return;
        }

        $startNow = 'y' == $helper->ask($input, $output, new Question('Start the synchronization now (y/n) [y]? ', 'y'));

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

                if (0 == $i % 10) {
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
                    ->setProjet($regEntity->getProjet())
                    ->setStage($regEntity->getStage())
                    ->setAutomne($regEntity->getAutomne())
                    ->setPrintemps($regEntity->getPrintemps())
                    ->setCredits($regEntity->getCredits())
                    ->setDiplomes($regEntity->getDiplomes())
                    ->setMineurs($regEntity->getMineurs())
                    ->setAntecedents($regEntity->getAntecedents())
                    ->setLanguages($regEntity->getLanguages())
                    ->setCommentaire($regEntity->getCommentaire())
                    ->setObjectifs($regEntity->getObjectifs())
                    ->setProgramme($regEntity->getProgramme());

                $em->persist($entity);

                if (0 == $i % 10) {
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

                if (0 == $i % 10) {
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
            'Projet',
            'Stage',
            'Automne',
            'Printemps',
            'Credits',
            'Diplomes',
            'Mineurs',
            'Antecedents',
            'Languages',
            'Commentaire',
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

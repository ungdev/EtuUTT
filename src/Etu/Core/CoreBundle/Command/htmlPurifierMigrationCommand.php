<?php

namespace Etu\Core\CoreBundle\Command;

use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class htmlPurifierMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:migration:htmlpurifier')
            ->setDescription('Migrate all editor fields from html to "purified" html');
    }

    /**
     * @param \Symfony\Component\Console\Input\InputInterface   $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     *
     * @throws \RuntimeException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $em = $this->getContainer()->get('doctrine')->getManager();
        $purifier = $this->getContainer()->get('etu.menu.html_purifier');
        $em->getFilters()->disable('softdeleteable');
        $bbcode = null;

        $orgas = $em->getRepository('EtuUserBundle:Organization')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert organization description');
        $progress = new ProgressBar($output, count($orgas));
        foreach ($orgas as $orga) {
            $orga->setDescription($purifier->purify($orga->getDescription()));
            $em->persist($orga);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $entities = $em->getRepository('EtuModuleCovoitBundle:Covoit')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert Covoit notes');
        $progress = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $entity->setNotes($purifier->purify($entity->getNotes()));
            $em->persist($entity);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $entities = $em->getRepository('EtuModuleCovoitBundle:CovoitMessage')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert Covoit message');
        $progress = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $entity->setText($purifier->purify($entity->getText()));
            $em->persist($entity);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $entities = $em->getRepository('EtuModuleEventsBundle:Event')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert events description');
        $progress = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $entity->setDescription($purifier->purify($entity->getDescription()));
            $em->persist($entity);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $entities = $em->getRepository('EtuModuleForumBundle:Message')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert forum message');
        $progress = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $entity->setContent($purifier->purify($entity->getContent()));
            $em->persist($entity);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $entities = $em->getRepository('EtuModuleBugsBundle:Issue')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert issue content');
        $progress = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $entity->setBody($purifier->purify($entity->getBody()));
            $em->persist($entity);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $entities = $em->getRepository('EtuModuleBugsBundle:Comment')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert issue comment content');
        $progress = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $entity->setBody($purifier->purify($entity->getBody()));
            $em->persist($entity);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $entities = $em->getRepository('EtuModuleUVBundle:Comment')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert UV comments');
        $progress = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $entity->setBody($purifier->purify($entity->getBody()));
            $em->persist($entity);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $entities = $em->getRepository('EtuModuleWikiBundle:WikiPage')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert wiki pages');
        $progress = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $entity->setContent($purifier->purify($entity->getContent()));
            $em->persist($entity);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $entities = $em->getRepository('EtuModuleDaymailBundle:DaymailPart')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert Daymail parts');
        $progress = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $entity->setBody($purifier->purify($entity->getBody(), 'email'));
            $em->persist($entity);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $output->writeln('------------------------------------------------------------');
        $output->writeln('Flush to DB');
        $em->flush();
        $output->writeln("\nDone.\n");
    }
}

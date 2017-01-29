<?php

namespace Etu\Core\CoreBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class editorMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:migration:editor')
            ->setDescription('Migrate all editor fields from BBcode to filtered html')
        ;
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
        $em->getFilters()->disable('softdeleteable');
        $bbcode = $this->getApplication()->getKernel()->getContainer()->get('fm_bbcode.templating.helper');

        $orgas = $em->getRepository('EtuUserBundle:Organization')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert organization description');
        $progress = new ProgressBar($output, count($orgas));
        foreach ($orgas as $orga) {
            $orga->setDescription(html_entity_decode($bbcode->filter($orga->getDescription() ?? '', 'default_filter')));
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
            $entity->setNotes(html_entity_decode($bbcode->filter($entity->getNotes() ?? '', 'default_filter')));
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
            $entity->setText(html_entity_decode($bbcode->filter($entity->getText() ?? '', 'default_filter')));
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
            $entity->setDescription(html_entity_decode($bbcode->filter($entity->getDescription() ?? '', 'default_filter')));
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
            $entity->setContent(html_entity_decode($bbcode->filter($entity->getContent() ?? '', 'default_filter')));
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
            $entity->setBody(html_entity_decode($bbcode->filter($entity->getBody() ?? '', 'default_filter')));
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
            $entity->setBody(html_entity_decode($bbcode->filter($entity->getBody() ?? '', 'default_filter')));
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
            $entity->setBody(html_entity_decode($bbcode->filter($entity->getBody() ?? '', 'default_filter')));
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
            $entity->setContent(html_entity_decode($bbcode->filter($entity->getContent() ?? '', 'default_filter')));
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

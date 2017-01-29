<?php

namespace Etu\Core\CoreBundle\Command;

use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class emoticonsMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:migration:emoticons')
            ->setDescription('Migrate all emoticons from old bbcode bundle to new tinymce CDN')
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

        $migrationArray = [
            '/assets/img/emoticons/angry.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-yell.gif',
            '/assets/img/emoticons/aw.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-surprised.gif',
            '/assets/img/emoticons/cool.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-cool.gif',
            '/assets/img/emoticons/ecstatic.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-laughing.gif',
            '/assets/img/emoticons/furious.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-laughing.gif',
            '/assets/img/emoticons/gah.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-surprised.gif',
            '/assets/img/emoticons/happy.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-smile.gif',
            '/assets/img/emoticons/heart.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-kiss.gif',
            '/assets/img/emoticons/hm.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-undecided.gif',
            '/assets/img/emoticons/kiss.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-embarassed.gif',
            '/assets/img/emoticons/meh.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-undecided.gif',
            '/assets/img/emoticons/mmf.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-undecided.gif',
            '/assets/img/emoticons/sad.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-cry.gif',
            '/assets/img/emoticons/tongue.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-tongue-out.gif',
            '/assets/img/emoticons/what.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-surprised.gif',
            '/assets/img/emoticons/wink.png' => 'https://cdn.tinymce.com/4/plugins/emoticons/img/smiley-wink.gif',
        ];

        $orgas = $em->getRepository('EtuUserBundle:Organization')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert organization description');
        $progress = new ProgressBar($output, count($orgas));
        foreach ($orgas as $orga) {
            $orga->setDescription(str_replace(array_keys($migrationArray), array_values($migrationArray), $orga->getDescription() ?? ''));
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
            $entity->setNotes(str_replace(array_keys($migrationArray), array_values($migrationArray), $entity->getNotes() ?? ''));
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
            $entity->setText(str_replace(array_keys($migrationArray), array_values($migrationArray), $entity->getText() ?? ''));
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
            $entity->setDescription(str_replace(array_keys($migrationArray), array_values($migrationArray), $entity->getDescription() ?? ''));
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
            $entity->setContent(str_replace(array_keys($migrationArray), array_values($migrationArray), $entity->getContent() ?? ''));
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
            $entity->setBody(str_replace(array_keys($migrationArray), array_values($migrationArray), $entity->getBody() ?? ''));
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
            $entity->setBody(str_replace(array_keys($migrationArray), array_values($migrationArray), $entity->getBody() ?? ''));
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
            $entity->setBody(str_replace(array_keys($migrationArray), array_values($migrationArray), $entity->getBody() ?? ''));
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
            $entity->setContent(str_replace(array_keys($migrationArray), array_values($migrationArray), $entity->getContent() ?? ''));
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

<?php

namespace Etu\Core\CoreBundle\Command;

use Etu\Module\UploadBundle\Entity\UploadedFile;
use Symfony\Component\Console\Helper\ProgressBar;
use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\HttpFoundation\File\File;

class imagesMigrationCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:migration:images')
            ->setDescription('Migrate all images from old uploaded module to the new one (including links in text)')
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
        $validator = $this->getContainer()->get('validator');

        $output->writeln('------------------------------------------------------------');
        $output->writeln('Migrates user images to new upload system');

        $migrationArray = [];
        // Copy all images and add them to the replacement array ($migrationArray)
        $mainIterator = new \DirectoryIterator($this->getContainer()->get('kernel')->getRootDir().'/../web/uploads/users_files/');
        $progress = new ProgressBar($output, iterator_count($mainIterator));

        foreach ($mainIterator as $dir) {
            if ($dir->isDir() && $dir->getFilename() != '.' && $dir->getFilename() != '..') {
                $iterator = new \DirectoryIterator($dir->getRealPath());

                // Find user
                $user_login = $dir->getFilename();
                $user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => $user_login]);
                if ($user) {
                    foreach ($iterator as $file) {
                        if ($file->isFile() && in_array(
                                strtolower($file->getExtension()),
                                ['png', 'jpg', 'jpeg', 'gif', 'bmp']
                            )
                        ) {
                            // Create symfony file object
                            $sFile = new File($file->getRealPath());

                            $uFile = new UploadedFile();
                            $name = preg_replace(
                                '/[\/\:\*\?"\|\\\\]/',
                                '-',
                                pathinfo($file->getFilename())['filename']
                            );
                            $uFile->setName(substr(urldecode($name), 0, 30));
                            if (strlen($uFile->getName()) < 2) {
                                $uFile->setName($uFile->getName().'--');
                            } elseif (strlen($uFile->getName()) < 3) {
                                $uFile->setName($uFile->getName().'-');
                            }
                            $uFile->setExtension($sFile->guessExtension());
                            $uFile->setAuthor($user);
                            $uFile->setDescription('Importation du fichier '.$uFile->getName());
                            $uFile->setFile($sFile);
                            $uFile->setValidated(false);

                            if (!count($validator->validate($uFile))) {
                                $em->persist($uFile);
                                $em->flush();
                                $uFile->file->move(
                                    $this->getContainer()->get('kernel')->getRootDir().'/../web/uploads/users_files/',
                                    $uFile->getId()
                                );
                                $migrationArray['/uploads/users_files/'.$user_login.'/'.$file->getFilename()] = '/upload/view/'.$uFile->getId().'/'.$uFile->getName().'.'.$uFile->getExtension();
                            } else {
                                echo "\n\n-----\n Erreur pour le fichier ".$file->getRealPath()."\n";
                                echo (string) $validator->validate($uFile);
                            }
                        }
                    }
                }
            }
            $progress->advance();
        }
        $progress->finish();
        $output->writeln("\n".'------------------------ $migrationArray ----------------------------');
        print_r($migrationArray);

        // Replace in text
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

        $entities = $em->getRepository('EtuModuleDaymailBundle:DaymailPart')->findAll();
        $output->writeln('------------------------------------------------------------');
        $output->writeln('Convert Daymail parts');
        $progress = new ProgressBar($output, count($entities));
        foreach ($entities as $entity) {
            $entity->setBody(str_replace(array_keys($migrationArray), array_values($migrationArray), $entity->getBody() ?? ''));
            $em->persist($entity);
            $progress->advance();
        }
        $progress->finish();
        $output->writeln('');

        $output->writeln('------------------------------------------------------------');
        $output->writeln('Flush to DB');
        $em->flush();
        $output->writeln("\nDone. You can now delete all user directory in /uploads/users_files/. But don't delete files:\nrmdir web/uploads/users_files/*");
    }
}

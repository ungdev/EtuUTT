<?php

namespace Etu\Core\UserBundle\Command;

use DateTime;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Util\SendSlack;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\BugsBundle\Entity\Issue;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class DeleteOldUsersCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:users:deleteold')
            ->setDescription('Delete old users accounts');
    }

    /**
     * @throws \RuntimeException
     * @throws \Doctrine\ORM\ORMException
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();

        $dateActuelle = new DateTime();
        $basePhotosDir = __DIR__.'/../../../../../web/uploads/photos/';
        $i = 0;
        /** @var User[] $users */
        $users = $em->getRepository('EtuUserBundle:User')->findAll();
        //14180
        $deleted_user = $em->getRepository('EtuUserBundle:User')->findOneBy(['login' => 'deleted_user']);

        foreach ($users as $user) {
            $toDelete = $user->getId() != $deleted_user->getId() &&
                (
                    !$user->getIsInLDAP() &&
                    (!$user->getIsKeepingAccount() ||
                    ($user->getIsKeepingAccount() && date_diff($user->getLastVisitHome(), $dateActuelle, true)->y >= 2))
                );
            if ($toDelete) {
                $output->writeln('Deleting '.$user->getId().' - '.$user->getStudentId().' - '.$user->getLogin());
                foreach ($em->getRepository('EtuModuleUVBundle:Comment')->findBy(['user' => $user]) as $comment) {
                    $comment->setUser($deleted_user);
                    $em->persist($comment);
                }
                $em->flush();
                foreach ($em->getRepository('EtuModuleUVBundle:Review')->findBy(['sender' => $user]) as $review) {
                    $review->setSender($deleted_user);
                    $em->persist($review);
                }
                $em->flush();
                foreach ($em->getRepository('EtuUserBundle:Organization')->findBy(['president' => $user]) as $organization) {
                    $organization->setPresident($deleted_user);
                    $em->persist($organization);
                }
                $em->flush();
                foreach ($em->getRepository('EtuModuleBugsBundle:Issue')->findBy(['assignee' => $user]) as $issue) {
                    $issue->setAssignee($deleted_user);
                    $em->persist($issue);
                }
                $em->flush();
                foreach ($em->getRepository('EtuModuleBugsBundle:Issue')->findBy(['user' => $user]) as $issue) {
                    $issue->setUser($deleted_user);
                    $em->persist($issue);
                }
                $em->flush();
                foreach ($em->getRepository('EtuModuleWikiBundle:WikiPage')->findBy(['author' => $user]) as $wikiPage) {
                    $wikiPage->setAuthor($deleted_user);
                    $em->persist($wikiPage);
                }
                $em->flush();
                foreach ($em->getRepository('EtuModuleForumBundle:Thread')->findBy(['author' => $user]) as $thread) {
                    /* @var Issue $issue */
                    $thread->setAuthor($deleted_user);
                    $em->persist($thread);
                }
                $em->flush();
                foreach ($em->getRepository('EtuModuleForumBundle:Message')->findBy(['author' => $user]) as $message) {
                    $message->setAuthor($deleted_user);
                    $em->persist($message);
                }
                $em->flush();
                foreach ($em->getRepository('EtuModuleBugsBundle:Comment')->findBy(['user' => $user]) as $comment) {
                    $comment->setUser($deleted_user);
                    $em->persist($comment);
                }
                $em->flush();
                foreach ($em->getRepository('EtuModuleUploadBundle:UploadedFile')->findBy(['author' => $user]) as $file) {
                    /**if (file_exists(__DIR__.'/../../../../../web/uploads/users_files/'.$file->getId())) {
                        unlink(__DIR__.'/../../../../../web/uploads/users_files/'.$file->getId());
                    }
                    $em->remove($file);**/
                    $file->setAuthor($deleted_user);
                    $em->persist($file);
                }
                $em->flush();

                if ('default-avatar.png' != $user->getAvatar() && file_exists($basePhotosDir.$user->getAvatar())) {
                    unlink($basePhotosDir.$user->getAvatar());
                }
                if (file_exists($basePhotosDir.$user->getLogin().'_official.jpg')) {
                    unlink($basePhotosDir.$user->getLogin().'_official.jpg');
                }
                $em->remove($user);
                $em->flush();
                ++$i;
            }

            //Si l'utilisateur veut encore son compte, qu'il n'est plus à l'UTT mais qu'il n'a pas de mot de passe
            if ($user->getIsKeepingAccount() && !$user->getIsInLDAP() && empty($user->getPassword())) {
                $jsonData = json_encode(['blocks' => [
                    [
                        'type' => 'header',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'Un utilisateur a besoin de créer son mot de passe',
                        ],
                    ],
                    [
                        'type' => 'divider',
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => 'Vous pouvez taper la commande `php bin/console etu:users:set-password` en fournissant le login : `'.$user->getLogin().'` puis lui envoyer par mail à '.$user->getPersonnalMail(),
                        ],
                    ],
                ],
                ]);
                SendSlack::curl_send($this->getContainer()->getParameter('slack_webhook_moderation'), $jsonData);
            }
        }

        $em->flush();

        $output->writeln("\nDone, ".$i." users deleted.\n");
    }
}

<?php

namespace Etu\Core\CoreBundle\Command;

use Doctrine\ORM\EntityManager;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RemoveSoftDeleteCommand extends ContainerAwareCommand
{
    /**
     * Configure the command.
     */
    protected function configure()
    {
        $this
            ->setName('etu:cleandb')
            ->setDescription('Remove soft deleteable elements and remove expired elements');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManager();
        $em->getFilters()->disable('softdeleteable');


        $output->writeln("============================");
        $output->writeln("Supression des clients OAuth");
        $output->writeln("============================");
        $elements = $em->getRepository('EtuCoreApiBundle:OauthClient')
            ->createQueryBuilder('u')
            ->where('u.deletedAt IS NOT NULL')
            ->getQuery()->getResult();
        foreach ($elements as $todelete) {
            $output->writeln('Suppression du client '.$todelete->getId());
            $elementsInside = $em->getRepository('EtuCoreApiBundle:OauthAuthorization')
                ->createQueryBuilder('u')
                ->where('u.client = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln('Deleting authorization '.$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $em->flush();
            $elementsInside = $em->getRepository('EtuCoreApiBundle:OauthAuthorizationCode')
                ->createQueryBuilder('u')
                ->where('u.client = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln('Deleting auth code '.$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $em->flush();
            $elementsInside = $em->getRepository('EtuCoreApiBundle:OauthAccessToken')
                ->createQueryBuilder('u')
                ->where('u.client = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln('Deleting access token '.$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $em->flush();
            $elementsInside = $em->getRepository('EtuCoreApiBundle:OauthRefreshToken')
                ->createQueryBuilder('u')
                ->where('u.client = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln('Deleting refresh token '.$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $em->remove($todelete);
            $em->flush();
        }

        $output->writeln("\n\n=========================");
        $output->writeln("Supression des utilisateurs");
        $output->writeln("===========================");

        $elements = $em->getRepository('EtuUserBundle:User')
            ->createQueryBuilder('u')
            ->where('u.deletedAt IS NOT NULL')
            ->getQuery()->getResult();

        foreach ($elements as $todelete) {
            $output->writeln('Suppression de l\'utilisateur '.$todelete->getId());
            $elementsInside = $em->getRepository('EtuCoreApiBundle:OauthAuthorization')
                ->createQueryBuilder('u')
                ->where('u.user = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln("Suppression de l'auth ".$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $em->flush();
            $elementsInside = $em->getRepository('EtuCoreApiBundle:OauthAuthorizationCode')
                ->createQueryBuilder('u')
                ->where('u.user = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln("Suppression de l'auth code ".$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $elementsInside = $em->getRepository('EtuCoreApiBundle:OauthAccessToken')
                ->createQueryBuilder('u')
                ->where('u.user = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln("Suppression de l'access token ".$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $em->flush();
            $elementsInside = $em->getRepository('EtuCoreApiBundle:OauthRefreshToken')
                ->createQueryBuilder('u')
                ->where('u.user = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln("Suppression du refresh token ".$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $elementsInside = $em->getRepository('EtuModuleUploadBundle:UploadedFile')
                ->createQueryBuilder('u')
                ->where('u.author = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $path = __DIR__.'/../../../../../web/uploads/users_files/'.$delete->getId();
                if (file_exists($path)) {
                    unlink($path);
                } else {
                    $output->writeln("Can't delete ".$path);
                }
                $em->remove($delete);
                $em->flush();
            }
            $em->remove($todelete);
            $em->flush();
        }

        $output->writeln("\n\n===========================");
        $output->writeln("Supression des associations");
        $output->writeln("===========================");
        $elements = $em->getRepository('EtuUserBundle:Organization')
            ->createQueryBuilder('u')
            ->where('u.deletedAt IS NOT NULL')
            ->getQuery()->getResult();
        foreach ($elements as $todelete) {
            $output->writeln('Deleting Orga '.$todelete->getId());
            $elementsInside = $em->getRepository('EtuModuleWikiBundle:WikiPage')
                ->createQueryBuilder('u')
                ->where('u.organization = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $em->remove($delete);
                $em->flush();
            }
            $elementsInside = $em->getRepository('EtuModuleDaymailBundle:DaymailPart')
                ->createQueryBuilder('u')
                ->where('u.orga = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $em->remove($delete);
                $em->flush();
            }
            $elementsInside = $em->getRepository('EtuModuleEventsBundle:Event')
                ->createQueryBuilder('u')
                ->where('u.orga = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $elementsInInside = $em->getRepository('EtuModuleEventsBundle:Answer')
                    ->createQueryBuilder('u')
                    ->where('u.event = :org')
                    ->setParameter('org', $delete)
                    ->getQuery()->getResult();
                foreach ($elementsInInside as $dedelete) {
                    $em->remove($dedelete);
                    $em->flush();
                }
                $em->remove($delete);
                $em->flush();
            }
            $elementsInside = $em->getRepository('EtuModuleUploadBundle:UploadedFile')
                ->createQueryBuilder('u')
                ->where('u.organization = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $path = __DIR__.'/../../../../../web/uploads/users_files/'.$delete->getId();
                if (file_exists($path)) {
                    unlink($path);
                } else {
                    $output->writeln("Can't delete ".$path);
                }
                $em->remove($delete);
                $em->flush();
            }
            $em->remove($todelete);
        }

        $em->flush();

        $date = new \DateTime();
        $limite = 5000;

        $em->flush();
        $query = $em->getRepository('EtuCoreApiBundle:OauthAuthorizationCode')
            ->createQueryBuilder('u')
            ->where('u.expireAt < :org')
            ->setParameter('org', $date)
            ->setMaxResults($limite)
            ->getQuery();
        while (count($query->getResult()) > 0) {
            $elementsInside = $query->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln('Deleting Code '.$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $em->flush();
        }
        $query = $em->getRepository('EtuCoreApiBundle:OauthAccessToken')
            ->createQueryBuilder('u')
            ->where('u.expireAt < :org')
            ->setParameter('org', $date)
            ->setMaxResults($limite)
            ->getQuery();
        while (count($query->getResult()) > 0) {
            $elementsInside = $query->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln('Deleting Token '.$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $em->flush();
        }
        $query = $em->getRepository('EtuCoreApiBundle:OauthRefreshToken')
            ->createQueryBuilder('u')
            ->where('u.expireAt < :org')
            ->setParameter('org', $date)
            ->setMaxResults($limite)
            ->getQuery();
        while (count($query->getResult()) > 0) {
            $elementsInside = $query->getResult();
            foreach ($elementsInside as $delete) {
                $output->writeln('Deleting Refresh '.$delete->getId());
                $em->remove($delete);
                $em->flush();
            }
            $em->flush();
        }
        $em->getFilters()->enable('softdeleteable');
    }
}

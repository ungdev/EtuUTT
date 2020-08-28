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

        $output->writeln("\n\n=========================");
        $output->writeln('Supression des utilisateurs');
        $output->writeln('===========================');

        $elements = $em->getRepository('EtuUserBundle:User')
            ->createQueryBuilder('u')
            ->where('u.deletedAt IS NOT NULL')
            ->andWhere('u.login != :org')
            ->setParameter('org', 'deleted_user')
            ->getQuery()->getResult();

        foreach ($elements as $todelete) {
            $output->writeln('Suppression de l\'utilisateur '.$todelete->getId());
            $elementsInside = $em->getRepository('EtuModuleUploadBundle:UploadedFile')
                ->createQueryBuilder('u')
                ->where('u.author = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $delete->deleteFile();
                $em->remove($delete);
                $em->flush();
            }
            $em->remove($todelete);
            $em->flush();
        }

        $output->writeln("\n\n===========================");
        $output->writeln('Supression des associations');
        $output->writeln('===========================');
        $elements = $em->getRepository('EtuUserBundle:Organization')
            ->createQueryBuilder('u')
            ->where('u.deletedAt IS NOT NULL')
            ->getQuery()->getResult();
        foreach ($elements as $todelete) {
            $output->writeln('Deleting Orga '.$todelete->getId());
            $elementsInside = $em->getRepository('EtuModuleUploadBundle:UploadedFile')
                ->createQueryBuilder('u')
                ->where('u.organization = :org')
                ->setParameter('org', $todelete)
                ->getQuery()->getResult();
            foreach ($elementsInside as $delete) {
                $delete->deleteFile();
                $em->remove($delete);
                $em->flush();
            }
            $em->remove($todelete);
        }

        $em->flush();

        $date = new \DateTime();
        $limite = 5000;

        $em->flush();
        $query = $em->getRepository('EtuCoreBundle:Notification')
            ->createQueryBuilder('u')
            ->where('u.expiration < :org')
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

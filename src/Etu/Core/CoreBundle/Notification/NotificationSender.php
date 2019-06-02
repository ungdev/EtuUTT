<?php

namespace Etu\Core\CoreBundle\Notification;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Solvecrew\ExpoNotificationsBundle\Manager\NotificationManager;

class NotificationSender
{
    /**
     * @var Registry
     */
    protected $doctrine;
    /**
     * @var NotificationManager
     */
    protected $notification_manager;

    /**
     * @param Registry $doctrine
     */
    public function __construct(Registry $doctrine, NotificationManager $notification_manager)
    {
        $this->doctrine = $doctrine;
        $this->notification_manager = $notification_manager;
    }

    /**
     * Send a notification.
     *
     * @param Notification $notif
     * @param bool         $tryCompile
     *
     * @return bool
     */
    public function send(Notification $notif, $tryCompile = false)
    {
        /** @var $em EntityManager */
        $em = $this->doctrine->getManager();

        $this->sendToMobiles($notif);

        if (!$notif->getIsSuper() && $tryCompile) {
            $oldDate = new \DateTime();
            $oldDate->setTime(date('h') - 1, date('i'), date('s'));

            $oldNotif = $em->createQueryBuilder()
                ->select('n')
                ->from('EtuCoreBundle:Notification', 'n')
                ->where('n.createdAt > :oldDate')
                ->andWhere('n.isSuper = 0')
                ->andWhere('n.helper = :helper')
                ->andWhere('n.entityType = :entityType')
                ->andWhere('n.entityId = :entityId')
                ->setParameter('oldDate', $oldDate)
                ->setParameter('helper', $notif->getHelper())
                ->setParameter('entityType', $notif->getEntityType())
                ->setParameter('entityId', $notif->getEntityId())
                ->setMaxResults(1)
                ->getQuery()
                ->getOneOrNullResult();

            if ($oldNotif instanceof Notification) {
                $oldNotif->setEntities(array_merge($oldNotif->getEntities(), $notif->getEntities()));
                $oldNotif->setCreatedAt($notif->getDate());

                $em->persist($oldNotif);
            } else {
                $em->persist($notif);
            }
        } else {
            $em->persist($notif);
        }

        $em->flush();

        return true;
    }


    /**
     * 
     * Trouve tout les clients natif capable de recevoir des notifications push
     * Parmi ces devices, ne garde que ceux dont l'utilisateur peur recevoir la notification
     * Envoie la notification
     * 
     * @param Module[] $enabledModules
     *
     * @return \Etu\Core\CoreBundle\Entity\Notification[]
     */
    public function sendToMobiles($notification)
    {
        $em = $this->doctrine->getManager();

        $all_devices = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy(['native' => 1, 'deletedAt' => null]);
        $filter = [];
        foreach($all_devices as $client) { //filter to get only devices with a push token
          if($client->getPushToken() != null) {
            array_push($filter, $client);
          }
        }
        $all_devices = $filter;

        /** @var $subscriptions Subscription[] */
        $subscriptions = $em->getRepository('EtuCoreBundle:Subscription')
          ->findBy([
            'entityType' => $notification->getEntityType(),
            'entityId' => $notification->getEntityId()
            ]);

        $tokens = [];
        foreach($subscriptions as $subscription) {
          $user = $subscription->getUser();
          foreach($all_devices as $client) {
            if($client->getUser() == $user) {
              array_push($tokens, $client->getPushToken());
            }
          }
        }

        $notificationManager = $this->notification_manager;
        $title = 'New Notification';
        $message = 'Hello there!';
        //$token = $body['token'];
        $data = ['title' => $title, 'message' => $message];

        $notificationManager->sendNotifications(
            [$message],
            $tokens,
            [$title],
            [$data]
        );
       
    }
}

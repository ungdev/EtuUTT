<?php

namespace Etu\Core\CoreBundle\Notification;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperManager;
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
     * @var HelperManager
     */
    protected $helperManager;

    /**
     * @param Registry            $doctrine
     * @param NotificationManager $notification_manager
     * @param HelperManager       $helperManager
     */
    public function __construct(Registry $doctrine, NotificationManager $notification_manager, HelperManager $helperManager)
    {
        $this->doctrine = $doctrine;
        $this->notification_manager = $notification_manager;
        $this->helperManager = $helperManager;
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
     * Trouve tout les clients natif capable de recevoir des notifications push
     * Parmi ces devices, ne garde que ceux dont l'utilisateur peur recevoir la notification
     * Envoie la notification.
     *
     * @param Module[] $enabledModules
     * @param mixed    $notification
     *
     * @return \Etu\Core\CoreBundle\Entity\Notification[]
     */
    public function sendToMobiles($notification)
    {
        $em = $this->doctrine->getManager();

        $all_devices = $em->getRepository('EtuCoreApiBundle:OauthClient')->findBy(['native' => 1, 'deletedAt' => null]);
        $filter = [];
        foreach ($all_devices as $client) { //filter to get only devices with a push token
            if ($client->getPushToken() != null) {
                array_push($filter, $client);
            }
        }
        $all_devices = $filter;

        /** @var $subscriptions Subscription[] */
        $subscriptions = $em->getRepository('EtuCoreBundle:Subscription')
          ->findBy([
            'entityType' => $notification->getEntityType(),
            'entityId' => $notification->getEntityId(),
            ]);

        $mobile = $this->helperManager->getHelper($notification->getHelper())->renderMobile($notification);
        $title = $mobile['title'];
        $message = $mobile['message'];
        $data = ['title' => $title, 'message' => $message];

        $titles = [];
        $messages = [];
        $datas = [];
        $tokens = [];
        foreach ($subscriptions as $subscription) {
            $user = $subscription->getUser();
            foreach ($all_devices as $client) {
                if ($client->getUser() == $user) {
                    array_push($tokens, $client->getPushToken());
                    array_push($titles, $title);
                    array_push($messages, $message);
                    array_push($datas, $data);
                }
            }
        }
        $this->notification_manager->sendNotifications(
            $messages,
            $tokens,
            $titles,
            $datas
        );
    }
}

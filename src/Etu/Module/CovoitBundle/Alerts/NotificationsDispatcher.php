<?php

namespace Etu\Module\CovoitBundle\Alerts;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\NotificationSender;
use Etu\Module\CovoitBundle\Entity\Covoit;
use Etu\Module\CovoitBundle\Entity\CovoitAlert;

/**
 * Dispatch notifications using alerts for a givne covoit.
 */
class NotificationsDispatcher
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var NotificationSender
     */
    protected $sender;

    public function __construct(Registry $doctrine, NotificationSender $sender)
    {
        $this->doctrine = $doctrine;
        $this->sender = $sender;
    }

    /**
     * Dispatch a covoit using database alerts.
     * Find matching alerts, create notifications and send them.
     */
    public function dispatch(Covoit $covoit)
    {
        /** @var EntityManager $em */
        $em = $this->doctrine->getManager();

        /*
         * We find all the alerts and try to match these objects with the covoit. However, as the number of result could
         * be big, we use some simple conditions to limit results : he startCity, the endCity and the price. Matching
         * date is difficult directly in SQL as the condition depends of which fields are filled.
         */
        /** @var CovoitAlert[] $alerts */
        $alerts = $em->createQueryBuilder()
            ->select('a, s, e, u')
            ->from('EtuModuleCovoitBundle:CovoitAlert', 'a')
            ->leftJoin('a.startCity', 's')
            ->leftJoin('a.endCity', 'e')
            ->leftJoin('a.user', 'u')
            ->where('a.startCity = :startCiy OR a.startCity IS NULL')
            ->andWhere('a.endCity = :endCity OR a.endCity IS NULL')
            ->andWhere('a.priceMax <= :price OR a.priceMax IS NULL')
            ->setParameters([
                    'startCiy' => $covoit->getStartCity()->getId(),
                    'endCity' => $covoit->getEndCity()->getId(),
                    'price' => $covoit->getPrice(),
                ])
            ->getQuery()
            ->getResult();

        // Notifications - Send only one notification per user, even if covoit match several alerts
        $notifications = [];

        foreach ($alerts as $alert) {
            if ($this->match($alert, $covoit)) {
                $notif = $this->createNotification($covoit);
                $notif->setEntityId($alert->getId());
                $notif->setAuthorId($covoit->getAuthor()->getId());

                $notifications[$alert->getUser()->getId()] = $notif;
            }
        }

        // Send the notifications
        foreach ($notifications as $notification) {
            $this->sender->send($notification);
        }
    }

    /**
     * Does the given alert match the given covoit.
     *
     * @return bool
     */
    private function match(CovoitAlert $alert, Covoit $covoit)
    {
        if ($alert->getStartCity() && $alert->getStartCity()->getId() != $covoit->getStartCity()->getId()) {
            return false;
        }

        if ($alert->getEndCity() && $alert->getEndCity()->getId() != $covoit->getEndCity()->getId()) {
            return false;
        }

        if ($alert->getPriceMax() && $alert->getPriceMax() < $covoit->getPrice()) {
            return false;
        }

        if ($alert->getStartDate() && $alert->getEndDate()) {
            if ($alert->getStartDate() > $covoit->getDate() || $alert->getEndDate() < $covoit->getDate()) {
                return false;
            }
        } elseif ($alert->getStartDate() && !$alert->getEndDate()) {
            if ($alert->getStartDate() != $covoit->getDate()) {
                return false;
            }
        } elseif (!$alert->getStartDate() && $alert->getEndDate()) {
            if ($alert->getEndDate() < $covoit->getDate()) {
                return false;
            }
        }

        return true;
    }

    /**
     * @return Notification
     */
    private function createNotification(Covoit $covoit)
    {
        $notif = new Notification();

        $notif
            ->setModule('covoit')
            ->setHelper('covoit_alert')
            ->setEntityType('covoit-alert')
            ->addEntity($covoit);

        return $notif;
    }
}

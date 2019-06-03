<?php

namespace Etu\Module\EventsBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

class EventCreatedAllHelper implements HelperInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @param \Twig_Environment $twig
     */
    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'event_created_all';
    }

    /**
     * @param Notification $notification
     *
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleEventsBundle:Notification:eventCreatedAll.html.twig', [
            'notif' => $notification,
        ]);
    }

    /**
     * @param Notification $notification
     *
     * @return string
     */
    public function renderMobile(Notification $notification)
    {
        return ['title' => 'Nouvel événement de '.$notification->getFirstEntity()['orga']['name'], 'message' => 'L\'événement '.$notification->getFirstEntity()['title'].' pourrait vous intéresser'];
    }
}

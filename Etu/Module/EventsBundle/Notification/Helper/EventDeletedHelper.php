<?php

namespace Etu\Module\EventsBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

class EventDeletedHelper implements HelperInterface
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    public function __construct(\Twig_Environment $twig)
    {
        $this->twig = $twig;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'event_deleted';
    }

    /**
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleEventsBundle:Notification:eventDeleted.html.twig', [
            'notif' => $notification,
        ]);
    }

    /**
     * @return string
     */
    public function renderMobile(Notification $notification)
    {
        return ['title' => 'Événement supprimé', 'message' => 'L\'événement '.$notification->getFirstEntity()['title'].' a été supprimé'];
    }
}

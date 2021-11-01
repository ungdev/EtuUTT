<?php

namespace Etu\Module\CovoitBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

/**
 * Notification for an alert.
 */
class AlertHelper implements HelperInterface
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
        return 'covoit_alert';
    }

    /**
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleCovoitBundle:Notification:alert.html.twig', [
            'notif' => $notification,
        ]);
    }

    /**
     * @return string
     */
    public function renderMobile(Notification $notification)
    {
        return ['title' => 'Nouveau covoiturage', 'message' => 'Le covoiturage de '.$notification->getFirstEntity()->getAuthor()->getFullName().' pourrait vous intéresser'];
    }
}

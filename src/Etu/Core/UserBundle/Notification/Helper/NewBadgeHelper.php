<?php

namespace Etu\Core\UserBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

/**
 * Notification for a bug closed by an admin.
 */
class NewBadgeHelper implements HelperInterface
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
        return 'new_badge';
    }

    /**
     * @param Notification $notification
     *
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuUserBundle:Notification:newBadge.html.twig', [
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
        return ['title' => 'Vous avez reçu un nouveau badge', 'message' => 'Ce badge se nomme : '.$notification->getEntities()[0]->name];
    }
}

<?php

namespace Etu\Module\CovoitBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

/**
 * Notification for a new message on a followed covoit.
 */
class NewMessageHelper implements HelperInterface
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
        return 'covoit_new_message';
    }

    /**
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleCovoitBundle:Notification:newMessage.html.twig', [
            'notif' => $notification,
        ]);
    }

    /**
     * @return string
     */
    public function renderMobile(Notification $notification)
    {
        return ['title' => 'Nouveau message de '.$notification->getFirstEntity()->getAuthor()->getFullName().' pour votre covoiturage', 'message' => $notification->getFirstEntity()->getText()];
    }
}

<?php

namespace Etu\Module\ForumBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

class ThreadAnsweredHelper implements HelperInterface
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
        return 'thread_answered';
    }

    /**
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleForumBundle:Notification:threadAnswered.html.twig', [
            'notif' => $notification,
        ]);
    }

    /**
     * @return string
     */
    public function renderMobile(Notification $notification)
    {
        return ['title' => 'Nouvelle réponse', 'message' => $notification->getFirstEntity()->getAuthor().' a répondu à '.$notification->getFirstEntity()->getThread()->getTitle()];
    }
}

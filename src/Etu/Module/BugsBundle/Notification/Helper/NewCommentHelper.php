<?php

namespace Etu\Module\BugsBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

/**
 * Notification for a new comment on an issue.
 */
class NewCommentHelper implements HelperInterface
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
        return 'bugs_new_comment';
    }

    /**
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleBugsBundle:Notification:newComment.html.twig', [
            'notif' => $notification,
        ]);
    }

    /**
     * @return string
     */
    public function renderMobile(Notification $notification)
    {
        if (1 == $notification->countEntities()) {
            return ['title' => 'Réponse à votre bug : '.$notification->getFirstEntity()->getIssue()->getTitle(), 'message' => $notification->getFirstEntity()->getUser()->getFullName().'a répondu à votre signalement.'];
        } elseif (2 == $notification->countEntities()) {
            return ['title' => 'Réponse à votre bug : '.$notification->getFirstEntity()->getIssue()->getTitle(), 'message' => $notification->getFirstEntity()->getUser()->getFullName().' et '.$notification->getEntities()[1]->getUser()->getFullName().' ont répondu à votre signalement.'];
        } elseif (3 == $notification->countEntities()) {
            return ['title' => 'Réponse à votre bug : '.$notification->getFirstEntity()->getIssue()->getTitle(), 'message' => $notification->getFirstEntity()->getUser()->getFullName().', '.$notification->getEntities()[1]->getUser()->getFullName().' et 1 autre ont répondu à votre signalement.'];
        }

        return ['title' => 'Réponse à votre bug : '.$notification->getFirstEntity()->getIssue()->getTitle(), 'message' => $notification->getFirstEntity()->getUser()->getFullName().', '.$notification->getEntities()[1]->getUser()->getFullName().' et '.($notification->countEntities() - 2).' autres ont répondu à votre signalement.'];
    }
}

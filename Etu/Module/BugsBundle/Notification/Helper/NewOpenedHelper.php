<?php

namespace Etu\Module\BugsBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

/**
 * Notification for a new issue in opened issues.
 */
class NewOpenedHelper implements HelperInterface
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
        return 'bugs_new_opened';
    }

    /**
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleBugsBundle:Notification:newOpened.html.twig', [
            'notif' => $notification,
        ]);
    }

    /**
     * @return string
     */
    public function renderMobile(Notification $notification)
    {
        if (1 == $notification->countEntities()) {
            return ['title' => 'Nouveau bug de '.$notification->getFirstEntity()->getUser()->getFullName().'.', 'message' => $notification->getFirstEntity()->getTitle()];
        } elseif (2 == $notification->countEntities()) {
            return ['title' => 'Nouveau bug de '.$notification->getFirstEntity()->getUser()->getFullName().' et '.$notification->getEntities()[1]->getUser()->getFullName().'.', 'message' => $notification->getFirstEntity()->getTitle()];
        } elseif (3 == $notification->countEntities()) {
            return ['title' => 'Nouveau bug de '.$notification->getFirstEntity()->getUser()->getFullName().', '.$notification->getEntities()[1]->getUser()->getFullName().' et 1 autre personne', 'message' => $notification->getFirstEntity()->getTitle()];
        }

        return ['title' => 'Nouveau bug de '.$notification->getFirstEntity()->getUser()->getFullName().', '.$notification->getEntities()[1]->getUser()->getFullName().' et '.($notification->countEntities() - 2).' autres personnes', 'message' => $notification->getFirstEntity()->getTitle()];
    }
}

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
        return 'bugs_new_opened';
    }

    /**
     * @param Notification $notification
     *
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleBugsBundle:Notification:newOpened.html.twig', [
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
        if ($notification->countEntities() == 1) {
            return ['title' => 'Nouveau bug de '.$notification->getFirstEntity()->getUser()->getFullName().'.', 'message' => $notification->getTitle()];
        } elseif ($notification->countEntities() == 2) {
            return ['title' => 'Nouveau bug de '.$notification->getFirstEntity()->getUser()->getFullName().' et '.$notification->getEntities()[1]->getUser()->getFullName().'.', 'message' => $notification->getTitle()];
        } elseif ($notification->countEntities() == 3) {
            return ['title' => 'Nouveau bug de '.$notification->getFirstEntity()->getUser()->getFullName().', '.$notification->getEntities()[1]->getUser()->getFullName().' et 1 autre personne', 'message' => $notification->getTitle()];
        }

        return ['title' => 'Nouveau bug de '.$notification->getFirstEntity()->getUser()->getFullName().', '.$notification->getEntities()[1]->getUser()->getFullName().' et '.($notification->countEntities() - 2).' autres personnes', 'message' => $notification->getTitle()];
    }
}

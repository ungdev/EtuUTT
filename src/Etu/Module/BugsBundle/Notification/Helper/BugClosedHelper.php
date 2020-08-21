<?php

namespace Etu\Module\BugsBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

/**
 * Notification for a bug closed by an admin.
 */
class BugClosedHelper implements HelperInterface
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
        return 'bugs_closed';
    }

    /**
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleBugsBundle:Notification:closed.html.twig', [
            'notif' => $notification,
        ]);
    }

    /**
     * @return string
     */
    public function renderMobile(Notification $notification)
    {
        if (null != $notification->getFirstEntity()->getAssignee()) {
            return ['title' => $notification->getFirstEntity()->getAssignee()->getFullName().' a fermé un bug', 'message' => 'Le bug "'.$notification->getFirstEntity()->getTitle().'" est résolu'];
        }

        return ['title' => 'Bug fermé', 'message' => 'Le bug "'.$notification->getFirstEntity()->getTitle().'" est résolu'];
    }
}

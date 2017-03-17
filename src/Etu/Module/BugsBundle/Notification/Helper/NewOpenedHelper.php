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
}

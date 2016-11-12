<?php

namespace Etu\Module\AssosBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

/**
 * Notification for a message by an organisation.
 */
class OrgaMessageHelper implements HelperInterface
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
        return 'orga_message';
    }

    /**
     * @param Notification $notification
     *
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleAssosBundle:Notification:orgaMessage.html.twig', [
            'notif' => $notification,
        ]);
    }
}

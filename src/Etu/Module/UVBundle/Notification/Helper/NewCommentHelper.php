<?php

namespace Etu\Module\UVBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

/**
 * Notification for a new comment on an UV.
 */
class NewCommentHelper implements HelperInterface
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
        return 'uv_new_comment';
    }

    /**
     * @param Notification $notification
     *
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleUVBundle:Notification:newComment.html.twig', [
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
        return ['title' => 'Nouveau commentaire dans l\'UE '.$notification->getFirstEntity()->getUV()->getCode(), 'message' => $notification->getFirstEntity()->getAuthor()->getFullName().' a ajouté un commentaire à l\'UE '.$notification->getFirstEntity()->getUV()->getCode()];
    }
}

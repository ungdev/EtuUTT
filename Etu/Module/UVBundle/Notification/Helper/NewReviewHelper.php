<?php

namespace Etu\Module\UVBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

/**
 * Notification for a new review on an UV.
 */
class NewReviewHelper implements HelperInterface
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
        return 'uv_new_review';
    }

    /**
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->twig->render('EtuModuleUVBundle:Notification:newReview.html.twig', [
            'notif' => $notification,
        ]);
    }

    /**
     * @return string
     */
    public function renderMobile(Notification $notification)
    {
        return ['title' => 'Nouvelle annale dans l\'UE '.$notification->getFirstEntity()->getUV()->getCode(), 'message' => $notification->getFirstEntity()->getSender()->getFullName().' a ajouté une annale à l\'UE '.$notification->getFirstEntity()->getUV()->getCode()];
    }
}

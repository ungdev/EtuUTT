<?php

namespace Etu\Core\CoreBundle\Framework\Definition;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\Controller as BaseController;

class Controller extends BaseController
{
    /**
     * @return \Etu\Core\CoreBundle\Menu\Sidebar\SidebarBuilder
     */
    public function getSidebarBuilder()
    {
        return $this->get('etu.menu.sidebar_builder');
    }

    /**
     * @return \Etu\Core\CoreBundle\Menu\UserMenu\UserMenuBuilder
     */
    public function getUserMenuBuilder()
    {
        return $this->get('etu.menu.user_builder');
    }

    /**
     * @return \Etu\Core\CoreBundle\Menu\AdminMenu\AdminBuilder
     */
    public function getAdminMenuBuilder()
    {
        return $this->get('etu.menu.admin_builder');
    }

    /**
     * @return \Etu\Core\CoreBundle\Framework\EtuKernel
     */
    public function getKernel()
    {
        return $this->get('kernel');
    }

    /**
     * @return \Etu\Core\CoreBundle\Notification\SubscriptionsManager
     */
    public function getSubscriptionsManager()
    {
        return $this->get('etu.notifs.subscriber');
    }

    /**
     * @return \Etu\Core\CoreBundle\Notification\NotificationSender
     */
    public function getNotificationsSender()
    {
        return $this->get('etu.notifs.sender');
    }

    /**
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     */
    public function createAccessDeniedResponse()
    {
        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'error',
            'message' => 'user.denied',
        ]);

        return $this->redirect($this->generateUrl('user_connect'));
    }

    /**
     * Get a user from the Security Context.
     *
     * @throws \LogicException If SecurityBundle is not available
     *
     * @return User|Organization
     *
     * @see Symfony\Component\Security\Core\Authentication\Token\TokenInterface::getUser()
     */
    public function getUser()
    {
        return parent::getUser();
    }
}

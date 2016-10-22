<?php

namespace Etu\Core\CoreBundle\Notification\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\CoreBundle\Framework\Twig\GlobalAccessorObject;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\Security\Core\Authorization\AuthorizationChecker;

class NewNotifsListener
{
    /**
     * @var Registry
     */
    protected $doctrine;

    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var AuthorizationChecker
     */
    protected $authorizationChecker;

    /**
     * @var GlobalAccessorObject
     */
    protected $globalAccessor;

    /**
     * @var \AppKernel
     */
    protected $kernel;

    /**
     * @param TokenStorage         $tokenStorage
     * @param AuthorizationChecker $authorizationChecker
     * @param Registry             $doctrine
     * @param GlobalAccessorObject $globalAccessor
     * @param \AppKernel           $kernel
     */
    public function __construct(TokenStorage $tokenStorage,
                                AuthorizationChecker $authorizationChecker,
                                Registry $doctrine,
                                GlobalAccessorObject $globalAccessor,
                                \AppKernel $kernel)
    {
        $this->doctrine = $doctrine;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
        $this->globalAccessor = $globalAccessor;
        $this->kernel = $kernel;
    }

    /**
     * Event to find subscriptions on page laod.
     */
    public function onKernelRequest()
    {
        if (!$this->tokenStorage->getToken()) {
            return;
        }

        $user = $this->tokenStorage->getToken()->getUser();
        $subscriptions = array();

        if ($this->authorizationChecker->isGranted('ROLE_CORE_SUBSCRIBE')) {
            /** @var $em EntityManager */
            $em = $this->doctrine->getManager();
            $subscriptions = $em->getRepository('EtuCoreBundle:Subscription')->findBy(array('user' => $user));
        }

        $this->globalAccessor->set('notifs', new ArrayCollection());
        $this->globalAccessor->get('notifs')->set('subscriptions', $subscriptions);
        $this->globalAccessor->get('notifs')->set('new', []);
        $this->globalAccessor->get('notifs')->set('new_count', 0);
    }
}

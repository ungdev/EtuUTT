<?php

namespace Etu\Core\CoreBundle\Notification\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\CoreBundle\Framework\Twig\GlobalAccessorObject;
use Etu\Core\UserBundle\Security\Layer\UserLayer;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\SecurityContext;

class NewNotifsListener
{
	/**
	 * @var Registry
	 */
	protected $doctrine;

	/**
	 * @var SecurityContext
	 */
	protected $securityContext;

	/**
	 * @var GlobalAccessorObject
	 */
	protected $globalAccessor;

	/**
	 * @var \AppKernel
	 */
	protected $kernel;

	/**
	 * @param SecurityContext      $securityContext
	 * @param Registry             $doctrine
	 * @param GlobalAccessorObject $globalAccessor
	 * @param \AppKernel           $kernel
	 */
	public function __construct(SecurityContext $securityContext,
	                            Registry $doctrine,
	                            GlobalAccessorObject $globalAccessor,
	                            \AppKernel $kernel)
	{
		$this->doctrine = $doctrine;
		$this->securityContext = $securityContext;
		$this->globalAccessor = $globalAccessor;
		$this->kernel = $kernel;
	}

    /**
     * Event to find subscriptions on page laod
     */
	public function onKernelRequest()
	{
		$layer = new UserLayer($this->securityContext->getToken()->getUser());
		$subscriptions = array();

		if ($layer->isUser()) {
			/** @var $em EntityManager */
			$em = $this->doctrine->getManager();

            $subscriptions = $em->getRepository('EtuCoreBundle:Subscription')->findBy(array('user' => $layer->getUser()));
		}

		$this->globalAccessor->set('notifs', new ArrayCollection());
		$this->globalAccessor->get('notifs')->set('subscriptions', $subscriptions);
		$this->globalAccessor->get('notifs')->set('new', []);
		$this->globalAccessor->get('notifs')->set('new_count', 0);
	}
}

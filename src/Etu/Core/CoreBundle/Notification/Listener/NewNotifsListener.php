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
	 * @param GetResponseEvent $event
	 */
	public function onKernelRequest($event)
	{
		$layer = new UserLayer($this->securityContext->getToken()->getUser());
		$notifications = array();
		$subscriptions = array();

		if ($layer->isUser()) {
			/** @var $em EntityManager */
			$em = $this->doctrine->getManager();

			// Load only notifications we should display, ie. notifications sent from
			// currently enabled modules

			$query = $em
				->createQueryBuilder()
				->select('n')
				->from('EtuCoreBundle:Notification', 'n')
				->where('n.date > :lastVisitHome')
				->andWhere('n.authorId != :userId')
				->setParameter('userId', $layer->getUser()->getId())
				->setParameter('lastVisitHome', $layer->getUser()->getLastVisitHome());

			/*
			 * Subscriptions
			 */
			/** @var $subscriptions Subscription[] */
			$subscriptions = $em->getRepository('EtuCoreBundle:Subscription')->findBy(array('user' => $layer->getUser()));
			$subscriptionsWhere = array();

			foreach ($subscriptions as $key => $subscription) {
				$subscriptionsWhere[] = '(n.entityType = :type_'.$key.' AND n.entityId = :id_'.$key.')';

				$query->setParameter('type_'.$key, $subscription->getEntityType());
				$query->setParameter('id_'.$key, $subscription->getEntityId());
			}

			if (! empty($subscriptionsWhere)) {
				$query = $query->andWhere(implode(' OR ', $subscriptionsWhere));
			}

			/*
			 * Modules
			 */
			$modulesWhere = array('n.module = \'core\'', 'n.module = \'user\'');

			foreach ($this->kernel->getModulesDefinitions() as $module) {
				$identifier = $module->getIdentifier();
				$modulesWhere[] = 'n.module = :module_'.$identifier;

				$query->setParameter('module_'.$identifier, $identifier);
			}

			if (! empty($modulesWhere)) {
				$query = $query->andWhere(implode(' OR ', $modulesWhere));
			}

			// Query
			$notifications = $query->getQuery()->getResult();
		}

		$this->globalAccessor->set('notifs', new ArrayCollection());
		$this->globalAccessor->get('notifs')->set('subscriptions', $subscriptions);
		$this->globalAccessor->get('notifs')->set('new', $notifications);
		$this->globalAccessor->get('notifs')->set('new_count', count($notifications));
	}
}

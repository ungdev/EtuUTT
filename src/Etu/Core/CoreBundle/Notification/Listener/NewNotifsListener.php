<?php

namespace Etu\Core\CoreBundle\Notification\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
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

		if ($layer->isUser()) {
			/** @var $em EntityManager */
			$em = $this->doctrine->getManager();

			// Load only notifications we should display, ie. notifications sent from
			// currently enabled modules
			$where = array('n.module = \'core\'', 'n.module = \'user\'');

			$query = $em
				->createQueryBuilder()
				->select('n')
				->from('EtuCoreBundle:Notification', 'n')
				->where('n.user = :user')
				->andWhere('n.isNew = 1')
				->setParameter('user', $layer->getUser()->getId());

			foreach ($this->kernel->getModulesDefinitions() as $module) {
				$identifier = $module->getIdentifier();

				$where[] = 'n.module = :'.$identifier;
				$query->setParameter($identifier, $identifier);
			}

			$notifications = $query->andWhere(implode(' OR ', $where))->getQuery()->getResult();
		}

		$this->globalAccessor->set('notifs', new ArrayCollection());
		$this->globalAccessor->get('notifs')->set('new', $notifications);
		$this->globalAccessor->get('notifs')->set('new_count', count($notifications));
	}
}

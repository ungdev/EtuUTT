<?php

namespace Etu\Core\CoreBundle\Notification\Listener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\EtuKernel;
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
	 * @var \Twig_Environment
	 */
	protected $twig;

	/**
	 * @var EtuKernel
	 */
	protected $kernel;

	/**
	 * @param SecurityContext   $securityContext
	 * @param Registry          $doctrine
	 * @param \Twig_Environment $twig
	 * @param EtuKernel         $kernel
	 */
	public function __construct(SecurityContext $securityContext, Registry $doctrine, \Twig_Environment $twig, EtuKernel $kernel)
	{
		$this->doctrine = $doctrine;
		$this->securityContext = $securityContext;
		$this->twig = $twig;
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
			$where = array();

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

		$this->twig->addGlobal('etu_count_new_notifs', count($notifications));
		$this->twig->addGlobal('etu_new_notifs', $notifications);
	}
}

<?php

namespace Etu\Core\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;

use Symfony\Component\HttpFoundation\Response;

// Import @Route() and @Template() annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class NotificationsController extends Controller
{
	/**
	 * @Route(
	 *      "/subscribe/{entityType}/{entityId}",
	 *      requirements={"entityType"="[a-z]+", "entityId" = "\d+"},
	 *      defaults={"_format"="json"},
	 *      name="notifs_subscribe",
	 *      options={"expose"=true}
	 * )
	 */
	public function subscribeAction($entityType, $entityId)
	{
		if (! $this->getUserLayer()->isUser()) {
			return new Response(json_encode(array(
				'status' => 403,
				'message' => 'You are not allowed to access this URL as anonymous.'
			)));
		}

		$this->getSubscriptionsManager()->subscribe($this->getUser(), $entityType, $entityId);

		return new Response(json_encode(array(
			'status' => 200,
			'message' => $this->get('translator')->trans('notifs.subscribe.confirm')
		)));
	}

	/**
	 * @Route(
	 *      "/unsubscribe/{entityType}/{entityId}",
	 *      requirements={"entityType"="[a-z]+", "entityId" = "\d+"},
	 *      defaults={"_format"="json"},
	 *      name="notifs_unsubscribe",
	 *      options={"expose"=true}
	 * )
	 */
	public function unsubscribeAction($entityType, $entityId)
	{
		if (! $this->getUserLayer()->isUser()) {
			return new Response(json_encode(array(
				'status' => 403,
				'message' => 'You are not allowed to access this URL as anonymous.'
			)));
		}

		$this->getSubscriptionsManager()->unsubscribe($this->getUser(), $entityType, $entityId);

		return new Response(json_encode(array(
			'status' => 200,
			'message' => $this->get('translator')->trans('notifs.unsubscribe.confirm')
		)));
	}

	/**
	 * @Route(
	 *      "/notifs/new",
	 *      name="notifs_new",
	 *      options={"expose"=true}
	 * )
	 * @Cache(expire=60)
	 */
	public function newAction()
	{
		if (! $this->getUserLayer()->isUser()) {
			return new Response(json_encode(array(
				'status' => 403,
				'message' => 'You are not allowed to access this URL as anonymous.'
			)));
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		// Load only notifications we should display, ie. notifications sent from
		// currently enabled modules
		$where = array();

		$query = $em
			->createQueryBuilder()
			->select('COUNT(n)')
			->from('EtuCoreBundle:Notification', 'n')
			->where('n.user = :user')
			->andWhere('n.isNew = 1')
			->setParameter('user', $this->getUser()->getId());

		foreach ($this->getKernel()->getModulesDefinitions() as $module) {
			$identifier = $module->getIdentifier();

			$where[] = 'n.module = :'.$identifier;
			$query->setParameter($identifier, $identifier);
		}

		$count = $query->andWhere(implode(' OR ', $where))->getQuery()->getScalarResult();

		if (! isset($count[0][1])) {
			$count[0][1] = 0;
		}

		return new Response(json_encode(array(
			'status' => 200,
			'result' => $count[0][1]
		)));
	}
}

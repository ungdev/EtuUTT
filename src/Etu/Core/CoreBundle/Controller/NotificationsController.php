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
		$this->getSubscriptionsManager()->unsubscribe($this->getUser(), $entityType, $entityId);

		return new Response(json_encode(array(
			'status' => 200,
			'message' => $this->get('translator')->trans('notifs.unsubscribe.confirm')
		)));
	}
}

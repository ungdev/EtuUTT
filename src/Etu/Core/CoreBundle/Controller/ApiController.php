<?php

namespace Etu\Core\CoreBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;

use Symfony\Component\HttpFoundation\Response;

// Import @Route() and @Template() annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
	/**
	 * @Route(
	 *      "/follow/{entityType}/{entityId}",
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
	 *      "/unfollow/{entityType}/{entityId}",
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
	 */
	public function newAction()
	{
		if (! $this->getUserLayer()->isUser()) {
			return new Response(json_encode(array(
				'status' => 403,
				'message' => 'You are not allowed to access this URL as anonymous.'
			)));
		}

		$globals = $this->get('twig')->getGlobals();

		return new Response(json_encode(array(
			'status' => 200,
			'result' => array('count' => $globals['etu_count_new_notifs'], 'notifs' => $globals['etu_new_notifs'])
		)));
	}
}

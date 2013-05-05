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
				'message' => 'You are not allowed to access this URL as anonymous or organization.'
			)), 403);
		}

		if (! $this->getUser()->testingContext) {
			$this->getSubscriptionsManager()->subscribe($this->getUser(), $entityType, $entityId);
		}

		return new Response(json_encode(array(
			'status' => 200,
			'message' => $this->get('translator')->trans('core.subscriptions.api.confirm_follow')
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
				'message' => 'You are not allowed to access this URL as anonymous or organization.'
			)), 403);
		}

		if (! $this->getUser()->testingContext) {
			$this->getSubscriptionsManager()->unsubscribe($this->getUser(), $entityType, $entityId);
		}

		return new Response(json_encode(array(
			'status' => 200,
			'message' => $this->get('translator')->trans('core.subscriptions.api.confirm_unfollow')
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
				'message' => 'You are not allowed to access this URL as anonymous or organization.'
			)), 403);
		}

		return new Response(json_encode(array(
			'status' => 200,
			'result' => array(
				'count' => $this->get('etu.twig.global_accessor')->get('notifs')->get('new_count'),
				'notifs' => $this->get('etu.twig.global_accessor')->get('notifs')->get('new')
			)
		)));
	}
}

<?php

namespace Etu\Core\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;

use Etu\Core\UserBundle\Model\Badge;
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
	 *      requirements={"entityType"="[a-z\-_]+", "entityId" = "\d+"},
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

		$user = $this->getUser();

		if (! $user->testingContext) {
			$this->getSubscriptionsManager()->subscribe($user, $entityType, $entityId);
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$count = $em->createQueryBuilder()
			->select('COUNT(s) as nb')
			->from('EtuCoreBundle:Subscription', 's')
			->where('s.user = :user')
			->setParameter('user', $user->getId())
			->getQuery()
			->getSingleScalarResult();

		$count = (int) $count;

		$user->removeBadge('subscriber');

		if ($count >= 1) {
			$user->addBadge(new Badge('subscriber', 1));
		}
		if ($count >= 10) {
			$user->getBadge('subscriber')->setLevel(2);
		}
		if ($count >= 30) {
			$user->getBadge('subscriber')->setLevel(3);
		}

		$em->persist($user);
		$em->flush();

		return new Response(json_encode(array(
			'status' => 200,
			'message' => $this->get('translator')->trans('core.subscriptions.api.confirm_follow')
		)));
	}

	/**
	 * @Route(
	 *      "/unfollow/{entityType}/{entityId}",
	 *      requirements={"entityType"="[a-z\-_]+", "entityId" = "\d+"},
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

		$user = $this->getUser();

		if (! $user->testingContext) {
			$this->getSubscriptionsManager()->unsubscribe($user, $entityType, $entityId);
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$count = $em->createQueryBuilder()
			->select('COUNT(s) as nb')
			->from('EtuCoreBundle:Subscription', 's')
			->where('s.user = :user')
			->setParameter('user', $user->getId())
			->getQuery()
			->getSingleScalarResult();

		$count = (int) $count;

		$user->removeBadge('subscriber');

		if ($count >= 1) {
			$user->addBadge(new Badge('subscriber', 1));
		}
		if ($count >= 10) {
			$user->getBadge('subscriber')->setLevel(2);
		}
		if ($count >= 30) {
			$user->getBadge('subscriber')->setLevel(3);
		}

		$em->persist($user);
		$em->flush();

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

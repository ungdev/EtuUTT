<?php

namespace Etu\Module\UVBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Util\RedactorJsEscaper;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Model\BadgesManager;
use Etu\Module\UVBundle\Entity\Comment;
use Etu\Module\UVBundle\Entity\Review;
use Symfony\Component\HttpFoundation\Request;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\UVBundle\Entity\UV;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/uvs")
 */
class ViewController extends Controller
{
	/**
	 * @Route("/{slug}-{name}/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="uvs_view")
	 * @Template()
	 */
	public function viewAction(Request $request, $slug, $name, $page = 1)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		/** @var UV $uv */
		$uv = $em->getRepository('EtuModuleUVBundle:UV')
			->findOneBy(array('slug' => $slug));

		if (! $uv) {
			throw $this->createNotFoundException(sprintf('UV for slug %s not found', $slug));
		}

		if (StringManipulationExtension::slugify($uv->getName()) != $name) {
			return $this->redirect($this->generateUrl('uvs_view', array(
				'slug' => $uv->getSlug(), 'name' => StringManipulationExtension::slugify($uv->getName())
			)), 301);
		}

		$comment = new Comment();
		$comment->setUv($uv)
			->setUser($this->getUser());

		$commentForm = $this->createFormBuilder($comment)
			->add('body', 'redactor')
			->getForm();

		if ($request->getMethod() == 'POST' && $commentForm->submit($request)->isValid()) {
			$comment->setBody(RedactorJsEscaper::escape($comment->getBody()));

			$em->persist($comment);
			$em->flush();

			// Notify subscribers
			$notif = new Notification();

			$notif
				->setModule($this->getCurrentBundle()->getIdentifier())
				->setHelper('uv_new_comment')
				->setAuthorId($this->getUser()->getId())
				->setEntityType('uv')
				->setEntityId($uv->getId())
				->addEntity($comment);

			$this->getNotificationsSender()->send($notif);

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'uvs.main.comment.confirm'
			));

			return $this->redirect($this->generateUrl('uvs_view', array(
				'slug' => $slug,
				'name' => $name
			)));
		}

		/** @var Review[] $results */
		$results = $em->createQueryBuilder()
			->select('r, s')
			->from('EtuModuleUVBundle:Review', 'r')
			->leftJoin('r.sender', 's')
			->where('r.uv = :uv')
			->setParameter('uv', $uv->getId())
			->orderBy('r.semester', 'DESC')
			->addOrderBy('r.validated', 'DESC')
			->getQuery()
			->getResult();

		$reviews = array();
		$reviewsCount = 0;

		foreach ($results as $result) {
			if (! isset($reviews[$result->getSemester()]['count'])) {
				$reviews[$result->getSemester()]['count'] = 0;
			}

			if (! isset($reviews[$result->getSemester()]['validated'])) {
				$reviews[$result->getSemester()]['validated'] = array();
			}

			if (! isset($reviews[$result->getSemester()]['pending'])) {
				$reviews[$result->getSemester()]['pending'] = array();
			}

			$key = ($result->getValidated()) ? 'validated' : 'pending';
			$reviews[$result->getSemester()][$key][] = $result;
			$reviews[$result->getSemester()]['count']++;
			$reviewsCount++;
		}

		$query = $em->createQueryBuilder()
			->select('c, u')
			->from('EtuModuleUVBundle:Comment', 'c')
			->leftJoin('c.user', 'u')
			->where('c.uv = :uv')
			->setParameter('uv', $uv->getId())
			->orderBy('c.createdAt', 'DESC')
			->getQuery();

		$pagination = $this->get('knp_paginator')->paginate($query, $page, 10);

		return array(
			'uv' => $uv,
			'semesters' => $reviews,
			'reviewsCount' => $reviewsCount,
			'pagination' => $pagination,
			'commentForm' => $commentForm->createView(),
		);
	}

	/**
	 * @Route("/{slug}-{name}/send-review", name="uvs_view_send_review")
	 * @Template()
	 */
	public function sendReviewAction(Request $request, $slug, $name)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		/** @var UV $uv */
		$uv = $em->getRepository('EtuModuleUVBundle:UV')
			->findOneBy(array('slug' => $slug));

		if (! $uv) {
			throw $this->createNotFoundException(sprintf('UV for slug %s not found', $slug));
		}

		if (StringManipulationExtension::slugify($uv->getName()) != $name) {
			return $this->redirect($this->generateUrl('uvs_view_send_review', array(
				'slug' => $uv->getSlug(), 'name' => StringManipulationExtension::slugify($uv->getName())
			)), 301);
		}

		$review = new Review();
		$review->setUv($uv)
			->setSender($this->getUser())
			->setSemester(User::currentSemester());

		$form = $this->createFormBuilder($review)
			->add('type', 'choice', array('choices' => Review::$types, 'required' => true))
			->add('semester', 'choice', array('choices' => Review::availableSemesters(), 'required' => true))
			->add('file', null, array('required' => true))
			->getForm();

		if ($request->getMethod() == 'POST' && $form->submit($request)->isValid()) {
			$review->upload();

			$em->persist($review);
			$em->flush();

			// Notify subscribers
			$notif = new Notification();

			$review->file = null;

			$notif
				->setModule($this->getCurrentBundle()->getIdentifier())
				->setHelper('uv_new_review')
				->setAuthorId($this->getUser()->getId())
				->setEntityType('uv')
				->setEntityId($uv->getId())
				->addEntity($review);

			$this->getNotificationsSender()->send($notif);

			// Add badges
			$count = $em->createQueryBuilder()
				->select('COUNT(r) as nb')
				->from('EtuModuleUVBundle:Review', 'r')
				->where('r.sender = :user')
				->setParameter('user', $this->getUser()->getId())
				->getQuery()
				->getSingleScalarResult();

			if ($count >= 1) {
				BadgesManager::userAddBadge($user, 'uvs_reviews', 1);
			} else {
				BadgesManager::userRemoveBadge($user, 'uvs_reviews', 1);
			}

			if ($count >= 2) {
				BadgesManager::userAddBadge($user, 'uvs_reviews', 2);
			} else {
				BadgesManager::userRemoveBadge($user, 'uvs_reviews', 2);
			}

			if ($count >= 4) {
				BadgesManager::userAddBadge($user, 'uvs_reviews', 3);
			} else {
				BadgesManager::userRemoveBadge($user, 'uvs_reviews', 3);
			}

			if ($count >= 10) {
				BadgesManager::userAddBadge($user, 'uvs_reviews', 4);
			} else {
				BadgesManager::userRemoveBadge($user, 'uvs_reviews', 4);
			}

			BadgesManager::userPersistBadges($user);
			$em->persist($this->getUser());
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'uvs.main.sendReview.confirm'
			));

			return $this->redirect($this->generateUrl('uvs_view', array(
				'slug' => $slug,
				'name' => $name
			)));
		}

		return array(
			'uv' => $uv,
			'form' => $form->createView(),
		);
	}
}


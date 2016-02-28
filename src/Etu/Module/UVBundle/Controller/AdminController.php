<?php

namespace Etu\Module\UVBundle\Controller;

use Doctrine\ORM\EntityManager;
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
 * @Route("/admin/uvs")
 */
class AdminController extends Controller
{
	/**
	 * @Route("", name="admin_uvs_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('uvs.admin')) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		$comments = $em->createQueryBuilder()
			->select('c, u, a')
			->from('EtuModuleUVBundle:Comment', 'c')
			->leftJoin('c.uv', 'u')
			->leftJoin('c.user', 'a')
			->orderBy('c.createdAt', 'DESC')
			->setMaxResults(10)
			->getQuery()
			->getResult();

		$reviews = $em->createQueryBuilder()
			->select('r, u, s')
			->from('EtuModuleUVBundle:Review', 'r')
			->leftJoin('r.uv', 'u')
			->leftJoin('r.sender', 's')
			->orderBy('r.createdAt', 'DESC')
			->setMaxResults(20)
			->getQuery()
			->getResult();

		return array(
			'comments' => $comments,
			'reviews' => $reviews
		);
	}

	/**
	 * @Route("/reviews/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="admin_uvs_reviews")
	 * @Template()
	 */
	public function reviewsAction($page = 1)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('uvs.admin')) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		$query = $em->createQueryBuilder()
			->select('r, u, s')
			->from('EtuModuleUVBundle:Review', 'r')
			->leftJoin('r.uv', 'u')
			->leftJoin('r.sender', 's')
			->orderBy('r.createdAt', 'DESC')
			->getQuery();

		$pagination = $this->get('knp_paginator')->paginate($query, $page, 40);

		return array(
			'pagination' => $pagination,
		);
	}

	/**
	 * @Route("/review/{id}/validate", name="admin_uvs_review_validate")
	 * @Template()
	 */
	public function validateReviewAction(Review $review)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('uvs.admin')) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		$review->setValidated(true);

		$em->persist($review);
		$em->flush();

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'uvs.admin.validateReview.confirm'
		));

		return $this->redirect($this->generateUrl('admin_uvs_reviews'));
	}

	/**
	 * @Route("/review/{id}/unvalidate", name="admin_uvs_review_unvalidate")
	 * @Template()
	 */
	public function unvalidateReviewAction(Review $review)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('uvs.admin')) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		$review->setValidated(false);

		$em->persist($review);
		$em->flush();

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'uvs.admin.unvalidateReview.confirm'
		));

		return $this->redirect($this->generateUrl('admin_uvs_reviews'));
	}

	/**
	 * @Route("/review/{id}/delete", name="admin_uvs_review_delete")
	 * @Template()
	 */
	public function deleteReviewAction(Review $review)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('uvs.admin')) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		$review->setDeletedAt(new \DateTime());

		$em->persist($review);
		$em->flush();

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'uvs.admin.deleteReview.confirm'
		));

		return $this->redirect($this->generateUrl('admin_uvs_reviews'));
	}

	/**
	 * @Route("/comments/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="admin_uvs_comments")
	 * @Template()
	 */
	public function commentsAction($page = 1)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('uvs.admin')) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		$query = $em->createQueryBuilder()
			->select('c, u, a')
			->from('EtuModuleUVBundle:Comment', 'c')
			->leftJoin('c.uv', 'u')
			->leftJoin('c.user', 'a')
			->orderBy('c.createdAt', 'DESC')
			->getQuery();

		$pagination = $this->get('knp_paginator')->paginate($query, $page, 20);

		return array(
			'pagination' => $pagination,
		);
	}

	/**
	 * @Route("/comment/{id}/delete", name="admin_uvs_comment_delete")
	 * @Template()
	 */
	public function deleteCommentAction(Comment $comment)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('uvs.admin')) {
			return $this->createAccessDeniedResponse();
		}

		/** @var EntityManager $em */
		$em = $this->getDoctrine()->getManager();

		$comment->setDeletedAt(new \DateTime());

		$em->persist($comment);
		$em->flush();

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'uvs.admin.deleteComment.confirm'
		));

		return $this->redirect($this->generateUrl('admin_uvs_comments'));
	}
}


<?php

namespace Etu\Module\BugsBundle\Controller;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\BugsBundle\Entity\Comment;
use Etu\Module\BugsBundle\Entity\Issue;

use Doctrine\ORM\EntityManager;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/bugs")
 */
class BugsController extends Controller
{
	/**
	 * @Route("/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="bugs_index")
	 * @Template()
	 */
	public function indexAction($page = 1)
	{
		if (! $this->getUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$query = $em->createQueryBuilder()
			->select('i, u, a')
			->from('EtuModuleBugsBundle:Issue', 'i')
			->leftJoin('i.user', 'u')
			->leftJoin('i.assignee', 'a')
			->where('i.isOpened = 1')
			->orderBy('i.createdAt', 'DESC')
			->setMaxResults(20);

		$pagination = $this->get('knp_paginator')->paginate($query, $page, 20);

		return array('pagination' => $pagination);
	}

	/**
	 * @Route("/closed", name="bugs_closed")
	 * @Template()
	 */
	public function closedAction()
	{
		if (! $this->getUser()) {
			return $this->createAccessDeniedResponse();
		}

		return array(
			'bugs' => array()
		);
	}

	/**
	 * @Route("/create", name="bugs_create")
	 * @Template()
	 */
	public function createAction()
	{
		if (! $this->getUser()) {
			return $this->createAccessDeniedResponse();
		}

		$bug = new Issue();
		$bug->setUser($this->getUser());

		$form = $this->createFormBuilder($bug)
			->add('title')
			->add('criticality', 'choice', array(
				'choices' => array(
					Issue::CRITICALITY_SECURITY => 'bugs.criticality.security',
					Issue::CRITICALITY_CRITICAL => 'bugs.criticality.critical',
					Issue::CRITICALITY_MAJOR => 'bugs.criticality.major',
					Issue::CRITICALITY_MINOR => 'bugs.criticality.minor',
					Issue::CRITICALITY_VISUAL => 'bugs.criticality.visual',
					Issue::CRITICALITY_TYPO => 'bugs.criticality.typo',
				)))
			->add('body')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em = $this->getDoctrine()->getManager();

			$bug->setBody($this->stripRedactorTags($bug->getBody()));

			$em->persist($bug);
			$em->flush();

			// Subscribe automatically the user at the issue
			$subscription = new Subscription();
			$subscription->setUser($this->getUser());
			$subscription->setEntityType('issue');
			$subscription->setEntityId($bug->getId());

			$em->persist($subscription);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'bugs.create.confirm'
			));

			return $this->redirect($this->generateUrl('bugs_view', array(
				'id' => $bug->getId(),
				'slug' => StringManipulationExtension::slugify($bug->getTitle()),
			)));
		}

		return array(
			'form' => $form->createView()
		);
	}

	/**
	 * @Route("/{id}-{slug}", requirements = {"number" = "\d+"}, name="bugs_view")
	 * @Template()
	 */
	public function viewAction($id, $slug)
	{
		if (! $this->getUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $bug Issue */
		$bug = $em->createQueryBuilder()
			->select('i, u, a')
			->from('EtuModuleBugsBundle:Issue', 'i')
			->leftJoin('i.user', 'u')
			->leftJoin('i.assignee', 'a')
			->where('i.id = :id')
			->setParameter('id', $id)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $bug) {
			throw $this->createNotFoundException('Issue #'.$id.' not found');
		}

		if (StringManipulationExtension::slugify($bug->getTitle()) != $slug) {
			throw $this->createNotFoundException('Invalid slug');
		}

		/** @var $bug Issue */
		$comments = $em->createQueryBuilder()
			->select('c, u')
			->from('EtuModuleBugsBundle:Comment', 'c')
			->leftJoin('c.user', 'u')
			->where('c.issue = :issue')
			->setParameter('issue', $bug->getId())
			->getQuery()
			->getResult();


		$comment = new Comment();
		$comment->setIssue($bug);
		$comment->setUser($this->getUser());

		/*
		$notif = new Notification();
		$notif->setModule($this->getCurrentBundle()->getIdentifier());
		$notif->setHelper('bugs_new_comment');
		$notif->addEntity($comment);

		$this->getNotificationsSender()->sendTo(array($this->getUser()), $notif);
		*/

		$form = $this->createFormBuilder($comment)
			->add('body')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {

			// Create the comment
			$comment->setBody($this->stripRedactorTags($comment->getBody()));
			$em->persist($comment);
			$em->flush();

			// Subscribe automatically the user at the issue
			$this->getSubscriptionsManager()->subscribe($this->getUser(), 'issue', $bug->getId());

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'bugs.view.message.creation'
			));

			return $this->redirect($this->generateUrl('bugs_view', array(
				'id' => $bug->getId(),
				'slug' => StringManipulationExtension::slugify($bug->getTitle()),
			)));
		} else {
			$message = null;
		}

		$updateForm = $this->createFormBuilder($bug)
			->add('criticality', 'choice', array(
				'choices' => array(
					Issue::CRITICALITY_CRITICAL => 'bugs.criticality.critical',
					Issue::CRITICALITY_SECURITY => 'bugs.criticality.security',
					Issue::CRITICALITY_MAJOR => 'bugs.criticality.major',
					Issue::CRITICALITY_MINOR => 'bugs.criticality.minor',
					Issue::CRITICALITY_VISUAL => 'bugs.criticality.visual',
					Issue::CRITICALITY_TYPO => 'bugs.criticality.typo',
				)
			))
			->getForm();

		return array(
			'bug' => $bug,
			'comments' => $comments,
			'form' => $form->createView(),
			'updateForm' => $updateForm->createView(),
			'message' => $message
		);
	}

	/**
	 * Protect a string from XSS injections allowing RedactorJS tags
	 *
	 * @param $str
	 * @return string
	 */
	private function stripRedactorTags($str)
	{
		// Catch YouTube videos
		$str = preg_replace(
			'/<iframe.+src="https?:\/\/www.youtube.com\/embed\/([a-z0-9_\-]+)".+><\/iframe>/iU',
			'https://www.youtube.com/watch?v=$1',
			$str
		);

		// Strip tags
		$str = strip_tags($str, '<code><span><div><label><a><br><p><b><i><del><strike><u><img><blockquote><mark><cite><small><ul><ol><li><hr><dl><dt><dd><sup><sub><big><pre><code><figure><figcaption><strong><em><table><tr><td><th><tbody><thead><tfoot><h1><h2><h3><h4><h5><h6>');

		// Reload YouTube videos
		$str = preg_replace(
			'/https?:\/\/www.youtube.com\/watch\?v=([a-z0-9_\-]+)/i',
			'<iframe width="560" height="315" src="http://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
			$str
		);

		return $str;
	}
}

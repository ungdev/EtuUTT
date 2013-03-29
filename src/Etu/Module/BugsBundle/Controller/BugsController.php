<?php

namespace Etu\Module\BugsBundle\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\BugsBundle\Entity\Comment;
use Etu\Module\BugsBundle\Entity\Issue;

use Doctrine\ORM\EntityManager;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * Class BugsController
 * @package Etu\Module\BugsBundle\Controller
 *
 * @Route("/bugs")
 */
class BugsController extends Controller
{
	/**
	 * @Route("/", name="bugs_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$bugs = $em->createQueryBuilder()
			->select('i, u, a')
			->from('EtuModuleBugsBundle:Issue', 'i')
			->leftJoin('i.user', 'u')
			->leftJoin('i.assignee', 'a')
			->where('i.isOpened = 1')
			->setMaxResults(20)
			->getQuery()
			->getResult();

		return array('bugs' => $bugs);
	}

	/**
	 * @Route("/closed", name="bugs_closed")
	 * @Template()
	 */
	public function closedAction()
	{
		return array(
			'bugs' => array()
		);
	}

	/**
	 * @Route("/{number}-{slug}", requirements = {"number" = "\d+"}, name="bugs_view")
	 * @Template()
	 */
	public function viewAction($number, $slug)
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
			->where('i.number = :number')
			->setParameter('number', $number)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $bug) {
			throw $this->createNotFoundException('Issue number #'.$number.' not found');
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
		$comment->setCreatedAt(new \DateTime());

		$form = $this->createFormBuilder($comment)
			->add('body')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {

			$comment->setBody($this->stripRedactorTags($comment->getBody()));

			$em->persist($comment);
			$em->flush();

			$message = 'bugs.view.message.creation';
		} else {
			$message = null;
		}

		$updateForm = $this->createFormBuilder($bug)
			->add('criticality', 'choice', array(
				'choices' => array(
					Issue::CRITICALITY_CRITICAL => 'bugs.view.admin.criticality.critical',
					Issue::CRITICALITY_SECURITY => 'bugs.view.admin.criticality.security',
					Issue::CRITICALITY_MAJOR => 'bugs.view.admin.criticality.major',
					Issue::CRITICALITY_MINOR => 'bugs.view.admin.criticality.minor',
					Issue::CRITICALITY_VISUAL => 'bugs.view.admin.criticality.visual',
					Issue::CRITICALITY_TYPO => 'bugs.view.admin.criticality.typo',
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
		return strip_tags($str, '<code><span><div><label><a><br><p><b><i><del><strike><u><img><video><audio><object><embed><param><blockquote><mark><cite><small><ul><ol><li><hr><dl><dt><dd><sup><sub><big><pre><code><figure><figcaption><strong><em><table><tr><td><th><tbody><thead><tfoot><h1><h2><h3><h4><h5><h6>');
	}
}

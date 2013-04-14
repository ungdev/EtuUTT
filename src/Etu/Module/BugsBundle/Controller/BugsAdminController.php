<?php

namespace Etu\Module\BugsBundle\Controller;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\CoreBundle\Util\RedactorJsEscaper;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\BugsBundle\Entity\Comment;
use Etu\Module\BugsBundle\Entity\Issue;

use Doctrine\ORM\EntityManager;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/bugs/admin")
 */
class BugsAdminController extends Controller
{
	/**
	 * @Route("/{id}-{slug}/assign", requirements = {"id" = "\d+"}, name="bugs_admin_assign")
	 * @Template()
	 */
	public function assignAction($id, $slug)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('bugs.admin')) {
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

		/** @var $assignee User */
		$assignee = $em->createQueryBuilder()
			->select('u')
			->from('EtuUserBundle:User', 'u')
			->where('u.fullName = :fullName')
			->setParameter('fullName', $this->getRequest()->get('assignee'))
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $assignee) {
			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'error',
				'message' => 'bugs.admin.assign.assignee_not_found'
			));
		} else {
			$bug->setAssignee($assignee);
			$bug->setUpdatedAt(new \DateTime());

			$em->persist($bug);

			$comment = new Comment();
			$comment->setIsStateUpdate(true);
			$comment->setIssue($bug);
			$comment->setUser($this->getUser());
			$comment->setBody(
				$this->get('translator')->trans('bugs.admin.assign.message', array(
					'%adminName%' => $this->getUser()->getFullName(),
					'%userName%' => $assignee->getFullName()
				))
			);

			$em->persist($comment);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'bugs.admin.assign.success'
			));
		}

		return $this->redirect($this->generateUrl('bugs_view', array(
			'id' => $bug->getId(),
			'slug' => StringManipulationExtension::slugify($bug->getTitle())
		)));
	}

	/**
	 * @Route("/{id}-{slug}/unassign", requirements = {"id" = "\d+"}, name="bugs_admin_unassign")
	 * @Template()
	 */
	public function unassignAction($id, $slug)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('bugs.admin')) {
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

		$assignee = $bug->getAssignee();

		$bug->setAssignee(null);
		$bug->setUpdatedAt(new \DateTime());

		$em->persist($bug);

		$comment = new Comment();
		$comment->setIsStateUpdate(true);
		$comment->setIssue($bug);
		$comment->setUser($this->getUser());
		$comment->setBody(
			$this->get('translator')->trans('bugs.admin.unassign.message', array(
				'%adminName%' => $this->getUser()->getFullName(),
				'%userName%' => $assignee->getFullName()
			))
		);

		$em->persist($comment);

		$em->flush();

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'bugs.admin.unassign.success'
		));

		return $this->redirect($this->generateUrl('bugs_view', array(
			'id' => $bug->getId(),
			'slug' => StringManipulationExtension::slugify($bug->getTitle())
		)));
	}

	/**
	 * @Route("/{id}-{slug}/criticality", requirements = {"id" = "\d+"}, name="bugs_admin_criticality")
	 * @Template()
	 */
	public function criticalityAction($id, $slug)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('bugs.admin')) {
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

		$criticalities = array(
			Issue::CRITICALITY_SECURITY => 'bugs.criticality.security',
			Issue::CRITICALITY_CRITICAL => 'bugs.criticality.critical',
			Issue::CRITICALITY_MAJOR => 'bugs.criticality.major',
			Issue::CRITICALITY_MINOR => 'bugs.criticality.minor',
			Issue::CRITICALITY_VISUAL => 'bugs.criticality.visual',
			Issue::CRITICALITY_TYPO => 'bugs.criticality.typo',
		);

		$updateForm = $this->createFormBuilder($bug)
			->add('criticality', 'choice', array('choices' => $criticalities))
			->getForm();

		$request = $this->getRequest();

		// Comment genration: before update
		$label = 'label';

		if ($bug->getCriticality() == Issue::CRITICALITY_SECURITY) {
			$label .= ' label-important';
		} elseif ($bug->getCriticality() == Issue::CRITICALITY_CRITICAL || $bug->getCriticality() == Issue::CRITICALITY_MAJOR) {
			$label .= ' label-warning';
		} elseif ($bug->getCriticality() == Issue::CRITICALITY_MINOR) {
			$label .= ' label-info';
		}

		$before = sprintf(
			'<span class="%s">%s</span>',
			$label, $this->get('translator')->trans($criticalities[$bug->getCriticality()])
		);

		if ($request->getMethod() == 'POST' && $updateForm->bind($request)->isValid()) {
			$bug->setUpdatedAt(new \DateTime());

			$em->persist($bug);

			// Comment genration: after update
			$label = 'label';

			if ($bug->getCriticality() == Issue::CRITICALITY_SECURITY) {
				$label .= ' label-important';
			} elseif ($bug->getCriticality() == Issue::CRITICALITY_CRITICAL || $bug->getCriticality() == Issue::CRITICALITY_MAJOR) {
				$label .= ' label-warning';
			} elseif ($bug->getCriticality() == Issue::CRITICALITY_MINOR) {
				$label .= ' label-info';
			}

			$after = sprintf(
				'<span class="%s">%s</span>',
				$label, $this->get('translator')->trans($criticalities[$bug->getCriticality()])
			);

			$comment = new Comment();
			$comment->setIsStateUpdate(true);
			$comment->setIssue($bug);
			$comment->setUser($this->getUser());
			$comment->setBody(
				$this->get('translator')->trans('bugs.admin.criticality.message', array(
					'%adminName%' => $this->getUser()->getFullName(),
					'%before%' => $before,
					'%after%' => $after
				))
			);

			$em->persist($comment);

			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'bugs.admin.criticality.success'
			));
		}

		return $this->redirect($this->generateUrl('bugs_view', array(
			'id' => $bug->getId(),
			'slug' => StringManipulationExtension::slugify($bug->getTitle())
		)));
	}

	/**
	 * @Route("/{id}-{slug}/close", requirements = {"id" = "\d+"}, name="bugs_admin_close")
	 * @Template()
	 */
	public function closeAction($id, $slug)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('bugs.admin')) {
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

		$bug->setOpen(false);
		$bug->setUpdatedAt(new \DateTime());

		$em->persist($bug);

		$comment = new Comment();
		$comment->setIsStateUpdate(true);
		$comment->setIssue($bug);
		$comment->setUser($this->getUser());
		$comment->setBody(
			$this->get('translator')->trans('bugs.admin.close.message', array(
				'%adminName%' => $this->getUser()->getFullName()
			))
		);

		$em->persist($comment);

		$em->flush();

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'bugs.admin.close.success'
		));

		return $this->redirect($this->generateUrl('bugs_view', array(
			'id' => $bug->getId(),
			'slug' => StringManipulationExtension::slugify($bug->getTitle())
		)));
	}

	/**
	 * @Route("/{id}-{slug}/open", requirements = {"id" = "\d+"}, name="bugs_admin_open")
	 * @Template()
	 */
	public function openAction($id, $slug)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('bugs.admin')) {
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

		$bug->setOpen(true);
		$bug->setUpdatedAt(new \DateTime());

		$em->persist($bug);

		$comment = new Comment();
		$comment->setIsStateUpdate(true);
		$comment->setIssue($bug);
		$comment->setUser($this->getUser());
		$comment->setBody(
			$this->get('translator')->trans('bugs.admin.open.message', array(
				'%adminName%' => $this->getUser()->getFullName()
			))
		);

		$em->persist($comment);

		$em->flush();

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'bugs.admin.open.success'
		));

		return $this->redirect($this->generateUrl('bugs_view', array(
			'id' => $bug->getId(),
			'slug' => StringManipulationExtension::slugify($bug->getTitle())
		)));
	}
}

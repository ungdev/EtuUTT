<?php

namespace Etu\Module\BugsBundle\Controller;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\BugsBundle\Entity\Comment;
use Etu\Module\BugsBundle\Entity\Issue;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;

/**
 * @Route("/admin/bugs")
 */
class BugsAdminController extends Controller
{
    /**
     * @Route("/{id}-{slug}/assign", requirements = {"id" = "\d+"}, name="bugs_admin_assign")
     * @Template()
     */
    public function assignAction($id, $slug, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS_ADMIN');

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

        if (!$bug) {
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
            ->setParameter('fullName', $request->get('assignee'))
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$assignee) {
            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'error',
                'message' => 'bugs.bugs_admin.assign.assignee_not_found',
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
                $this->get('translator')->trans('bugs.bugs_admin.assign.message', array(
                    '%adminName%' => $this->getUser()->getFullName(),
                    '%userName%' => $assignee->getFullName(),
                ))
            );

            $em->persist($comment);
            $em->flush();

            // Subscribe automatically the user at the issue
            $this->getSubscriptionsManager()->subscribe($assignee, 'issue', $bug->getId());

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'bugs.bugs_admin.assign.success',
            ));
        }

        return $this->redirect($this->generateUrl('bugs_view', array(
            'id' => $bug->getId(),
            'slug' => StringManipulationExtension::slugify($bug->getTitle()),
        )));
    }

    /**
     * @Route("/{id}-{slug}/unassign", requirements = {"id" = "\d+"}, name="bugs_admin_unassign")
     * @Template()
     */
    public function unassignAction($id, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS_ADMIN');

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

        if (!$bug) {
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
            $this->get('translator')->trans('bugs.bugs_admin.unassign.message', array(
                '%adminName%' => $this->getUser()->getFullName(),
                '%userName%' => $assignee->getFullName(),
            ))
        );

        $em->persist($comment);

        $em->flush();

        $this->get('session')->getFlashBag()->set('message', array(
            'type' => 'success',
            'message' => 'bugs.bugs_admin.unassign.success',
        ));

        return $this->redirect($this->generateUrl('bugs_view', array(
            'id' => $bug->getId(),
            'slug' => StringManipulationExtension::slugify($bug->getTitle()),
        )));
    }

    /**
     * @Route("/{id}-{slug}/criticality", requirements = {"id" = "\d+"}, name="bugs_admin_criticality")
     * @Template()
     */
    public function criticalityAction($id, $slug, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS_ADMIN');

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

        if (!$bug) {
            throw $this->createNotFoundException('Issue #'.$id.' not found');
        }

        if (StringManipulationExtension::slugify($bug->getTitle()) != $slug) {
            throw $this->createNotFoundException('Invalid slug');
        }

        $criticalities = array(
            Issue::CRITICALITY_SECURITY => 'bugs.criticality.60',
            Issue::CRITICALITY_CRITICAL => 'bugs.criticality.50',
            Issue::CRITICALITY_MAJOR => 'bugs.criticality.40',
            Issue::CRITICALITY_MINOR => 'bugs.criticality.30',
            Issue::CRITICALITY_VISUAL => 'bugs.criticality.20',
            Issue::CRITICALITY_TYPO => 'bugs.criticality.10',
        );

        $updateForm = $this->createFormBuilder($bug)
            ->add('criticality', ChoiceType::class, array('choices' => $criticalities))
            ->getForm();

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

        if ($request->getMethod() == 'POST' && $updateForm->handleRequest($request)->isValid()) {
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
                $this->get('translator')->trans('bugs.bugs_admin.criticality.message', array(
                    '%adminName%' => $this->getUser()->getFullName(),
                    '%before%' => $before,
                    '%after%' => $after,
                ))
            );

            $em->persist($comment);

            $em->flush();

            $this->get('session')->getFlashBag()->set('message', array(
                'type' => 'success',
                'message' => 'bugs.bugs_admin.criticality.success',
            ));
        }

        return $this->redirect($this->generateUrl('bugs_view', array(
            'id' => $bug->getId(),
            'slug' => StringManipulationExtension::slugify($bug->getTitle()),
        )));
    }

    /**
     * @Route("/{id}-{slug}/close", requirements = {"id" = "\d+"}, name="bugs_admin_close")
     * @Template()
     */
    public function closeAction($id, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS_ADMIN');

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

        if (!$bug) {
            throw $this->createNotFoundException('Issue #'.$id.' not found');
        }

        if (StringManipulationExtension::slugify($bug->getTitle()) != $slug) {
            throw $this->createNotFoundException('Invalid slug');
        }

        $bug->close();
        $em->persist($bug);

        $comment = new Comment();
        $comment->setIsStateUpdate(true);
        $comment->setIssue($bug);
        $comment->setUser($this->getUser());
        $comment->setBody(
            $this->get('translator')->trans('bugs.bugs_admin.close.message', array(
                '%adminName%' => $this->getUser()->getFullName(),
            ))
        );

        $em->persist($comment);
        $em->flush();

        // Send notifications to subscribers
        $notif = new Notification();

        $notif
            ->setModule('bugs')
            ->setHelper('bugs_closed')
            ->setAuthorId($this->getUser()->getId())
            ->setEntityType('issue')
            ->setEntityId($bug->getId())
            ->addEntity($bug);

        $this->getNotificationsSender()->send($notif);

        $this->get('session')->getFlashBag()->set('message', array(
            'type' => 'success',
            'message' => 'bugs.bugs_admin.close.success',
        ));

        return $this->redirect($this->generateUrl('bugs_view', array(
            'id' => $bug->getId(),
            'slug' => StringManipulationExtension::slugify($bug->getTitle()),
        )));
    }

    /**
     * @Route("/{id}-{slug}/open", requirements = {"id" = "\d+"}, name="bugs_admin_open")
     * @Template()
     */
    public function openAction($id, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS_ADMIN');

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

        if (!$bug) {
            throw $this->createNotFoundException('Issue #'.$id.' not found');
        }

        if (StringManipulationExtension::slugify($bug->getTitle()) != $slug) {
            throw $this->createNotFoundException('Invalid slug');
        }

        $bug->open();
        $em->persist($bug);

        $comment = new Comment();
        $comment->setIsStateUpdate(true);
        $comment->setIssue($bug);
        $comment->setUser($this->getUser());
        $comment->setBody(
            $this->get('translator')->trans('bugs.bugs_admin.open.message', array(
                '%adminName%' => $this->getUser()->getFullName(),
            ))
        );

        $em->persist($comment);

        $em->flush();

        $this->get('session')->getFlashBag()->set('message', array(
            'type' => 'success',
            'message' => 'bugs.bugs_admin.open.success',
        ));

        return $this->redirect($this->generateUrl('bugs_view', array(
            'id' => $bug->getId(),
            'slug' => StringManipulationExtension::slugify($bug->getTitle()),
        )));
    }

    /**
     * @Route("/{id}-{slug}/delete", requirements = {"id" = "\d+"}, name="bugs_admin_delete")
     * @Template()
     */
    public function deleteAction($id, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS_ADMIN');

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

        if (!$bug) {
            throw $this->createNotFoundException('Issue #'.$id.' not found');
        }

        if (StringManipulationExtension::slugify($bug->getTitle()) != $slug) {
            throw $this->createNotFoundException('Invalid slug');
        }

        return array(
            'bug' => $bug,
        );
    }

    /**
     * @Route("/{id}-{slug}/delete/confirm", requirements = {"id" = "\d+"}, name="bugs_admin_delete_confirm")
     * @Template()
     */
    public function deleteConfirmAction($id, $slug)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS_ADMIN');

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

        if (!$bug) {
            throw $this->createNotFoundException('Issue #'.$id.' not found');
        }

        if (StringManipulationExtension::slugify($bug->getTitle()) != $slug) {
            throw $this->createNotFoundException('Invalid slug');
        }

        /** @var $comments Comment[] */
        $comments = $em->createQueryBuilder()
            ->select('c, u')
            ->from('EtuModuleBugsBundle:Comment', 'c')
            ->leftJoin('c.user', 'u')
            ->where('c.issue = :issue')
            ->setParameter('issue', $bug->getId())
            ->getQuery()
            ->getResult();

        $em->remove($bug);

        foreach ($comments as $comment) {
            $em->remove($comment);
        }

        $em->flush();

        $this->get('session')->getFlashBag()->set('message', array(
            'type' => 'success',
            'message' => 'bugs.bugs_admin.delete.success',
        ));

        return $this->redirect($this->generateUrl('bugs_index'));
    }
}

<?php

namespace Etu\Module\BugsBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\BugsBundle\Entity\Comment;
use Etu\Module\BugsBundle\Entity\Issue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/admin/bugs")
 */
class BugsAdminController extends Controller
{
    /**
     * @Route("/{id}-{slug}/assign", requirements = {"id" = "\d+"}, name="bugs_admin_assign")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
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
            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'error',
                'message' => 'bugs.bugs_admin.assign.assignee_not_found',
            ]);
        } else {
            $bug->setAssignee($assignee);
            $bug->setUpdatedAt(new \DateTime());

            $em->persist($bug);

            $comment = new Comment();
            $comment->setIsStateUpdate(true);
            $comment->setIssue($bug);
            $comment->setUser($this->getUser());
            $comment->setBody(
                $this->get('translator')->trans('bugs.bugs_admin.assign.message', [
                    '%adminName%' => $this->getUser()->getFullName(),
                    '%userName%' => $assignee->getFullName(),
                ])
            );

            $em->persist($comment);
            $em->flush();

            // Subscribe automatically the user at the issue
            $this->getSubscriptionsManager()->subscribe($assignee, 'issue', $bug->getId());

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'bugs.bugs_admin.assign.success',
            ]);
        }

        return $this->redirect($this->generateUrl('bugs_view', [
            'id' => $bug->getId(),
            'slug' => StringManipulationExtension::slugify($bug->getTitle()),
        ]));
    }

    /**
     * @Route("/{id}-{slug}/unassign", requirements = {"id" = "\d+"}, name="bugs_admin_unassign")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
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
            $this->get('translator')->trans('bugs.bugs_admin.unassign.message', [
                '%adminName%' => $this->getUser()->getFullName(),
                '%userName%' => $assignee->getFullName(),
            ])
        );

        $em->persist($comment);

        $em->flush();

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'bugs.bugs_admin.unassign.success',
        ]);

        return $this->redirect($this->generateUrl('bugs_view', [
            'id' => $bug->getId(),
            'slug' => StringManipulationExtension::slugify($bug->getTitle()),
        ]));
    }

    /**
     * @Route("/{id}-{slug}/criticality", requirements = {"id" = "\d+"}, name="bugs_admin_criticality")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
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

        $criticalities = [
            'bugs.criticality.60' => Issue::CRITICALITY_SECURITY,
            'bugs.criticality.50' => Issue::CRITICALITY_CRITICAL,
            'bugs.criticality.40' => Issue::CRITICALITY_MAJOR,
            'bugs.criticality.30' => Issue::CRITICALITY_MINOR,
            'bugs.criticality.20' => Issue::CRITICALITY_VISUAL,
            'bugs.criticality.10' => Issue::CRITICALITY_TYPO,
            'bugs.criticality.5' => Issue::CRITICALITY_SUGGESTION,
        ];

        $updateForm = $this->createFormBuilder($bug)
            ->add('criticality', ChoiceType::class, ['choices' => $criticalities])
            ->add('submit', SubmitType::class)
            ->getForm();

        // Comment genration: before update
        $label = 'label';

        if (Issue::CRITICALITY_SECURITY == $bug->getCriticality()) {
            $label .= ' label-important';
        } elseif (Issue::CRITICALITY_CRITICAL == $bug->getCriticality() || Issue::CRITICALITY_MAJOR == $bug->getCriticality()) {
            $label .= ' label-warning';
        } elseif (Issue::CRITICALITY_MINOR == $bug->getCriticality()) {
            $label .= ' label-info';
        }

        $before = sprintf(
            '<span class="%s">%s</span>',
            $label, $this->get('translator')->trans(array_flip($criticalities)[$bug->getCriticality()])
        );

        $updateForm->handleRequest($request);
        if ($updateForm->isSubmitted() && $updateForm->isValid()) {
            $bug->setUpdatedAt(new \DateTime());

            $em->persist($bug);

            // Comment genration: after update
            $label = 'label';

            if (Issue::CRITICALITY_SECURITY == $bug->getCriticality()) {
                $label .= ' label-important';
            } elseif (Issue::CRITICALITY_CRITICAL == $bug->getCriticality() || Issue::CRITICALITY_MAJOR == $bug->getCriticality()) {
                $label .= ' label-warning';
            } elseif (Issue::CRITICALITY_MINOR == $bug->getCriticality()) {
                $label .= ' label-info';
            }

            $after = sprintf(
                '<span class="%s">%s</span>',
                $label, $this->get('translator')->trans(array_flip($criticalities)[$bug->getCriticality()])
            );

            $comment = new Comment();
            $comment->setIsStateUpdate(true);
            $comment->setIssue($bug);
            $comment->setUser($this->getUser());
            $comment->setBody(
                $this->get('translator')->trans('bugs.bugs_admin.criticality.message', [
                    '%adminName%' => $this->getUser()->getFullName(),
                    '%before%' => $before,
                    '%after%' => $after,
                ])
            );

            $em->persist($comment);

            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'bugs.bugs_admin.criticality.success',
            ]);
        }

        return $this->redirect($this->generateUrl('bugs_view', [
            'id' => $bug->getId(),
            'slug' => StringManipulationExtension::slugify($bug->getTitle()),
        ]));
    }

    /**
     * @Route("/{id}-{slug}/close", requirements = {"id" = "\d+"}, name="bugs_admin_close")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
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
            $this->get('translator')->trans('bugs.bugs_admin.close.message', [
                '%adminName%' => $this->getUser()->getFullName(),
            ])
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

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'bugs.bugs_admin.close.success',
        ]);

        return $this->redirect($this->generateUrl('bugs_view', [
            'id' => $bug->getId(),
            'slug' => StringManipulationExtension::slugify($bug->getTitle()),
        ]));
    }

    /**
     * @Route("/{id}-{slug}/open", requirements = {"id" = "\d+"}, name="bugs_admin_open")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
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
            $this->get('translator')->trans('bugs.bugs_admin.open.message', [
                '%adminName%' => $this->getUser()->getFullName(),
            ])
        );

        $em->persist($comment);

        $em->flush();

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'bugs.bugs_admin.open.success',
        ]);

        return $this->redirect($this->generateUrl('bugs_view', [
            'id' => $bug->getId(),
            'slug' => StringManipulationExtension::slugify($bug->getTitle()),
        ]));
    }

    /**
     * @Route("/{id}-{slug}/delete", requirements = {"id" = "\d+"}, name="bugs_admin_delete")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
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

        return [
            'bug' => $bug,
        ];
    }

    /**
     * @Route("/{id}-{slug}/delete/confirm", requirements = {"id" = "\d+"}, name="bugs_admin_delete_confirm")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
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

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'bugs.bugs_admin.delete.success',
        ]);

        return $this->redirect($this->generateUrl('bugs_index'));
    }
}

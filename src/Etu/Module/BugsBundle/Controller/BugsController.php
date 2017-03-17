<?php

namespace Etu\Module\BugsBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\CoreBundle\Form\EditorType;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Module\BugsBundle\Entity\Comment;
use Etu\Module\BugsBundle\Entity\Issue;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

/**
 * @Route("/bugs")
 */
class BugsController extends Controller
{
    /**
     * @Route("/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="bugs_index")
     * @Template()
     *
     * @param mixed $page
     */
    public function indexAction($page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('i, u, a')
            ->from('EtuModuleBugsBundle:Issue', 'i')
            ->leftJoin('i.user', 'u')
            ->leftJoin('i.assignee', 'a')
            ->where('i.isOpened = 1')
            ->orderBy('i.criticality', 'DESC')
            ->addOrderBy('i.createdAt', 'DESC');

        $pagination = $this->get('knp_paginator')->paginate($query, $page, 20);

        return ['pagination' => $pagination];
    }

    /**
     * @Route("/closed/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="bugs_closed")
     * @Template()
     *
     * @param mixed $page
     */
    public function closedAction($page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('i, u, a')
            ->from('EtuModuleBugsBundle:Issue', 'i')
            ->leftJoin('i.user', 'u')
            ->leftJoin('i.assignee', 'a')
            ->where('i.isOpened = 0')
            ->orderBy('i.closedAt', 'DESC')
            ->setMaxResults(20);

        $pagination = $this->get('knp_paginator')->paginate($query, $page, 20);

        return ['pagination' => $pagination];
    }

    /**
     * @Route("/{id}-{slug}", requirements = {"id" = "\d+"}, name="bugs_view")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
     */
    public function viewAction($id, $slug, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS');

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
            return $this->redirect($this->generateUrl('bugs_view', [
                'id' => $id, 'slug' => StringManipulationExtension::slugify($bug->getTitle()),
            ]), 301);
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

        $form = $this->createFormBuilder($comment)
            ->add('body', EditorType::class, ['label' => 'bugs.bugs.view.comment_body'])
            ->add('submit', SubmitType::class, ['label' => 'bugs.bugs.view.comment_submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($this->isGranted('ROLE_BUGS_POST') && $form->isSubmitted() && $form->isValid()) {
            $em->persist($comment);
            $em->flush();

            // Send notifications to subscribers
            $notif = new Notification();

            $notif
                ->setModule('bugs')
                ->setHelper('bugs_new_comment')
                ->setAuthorId($this->getUser()->getId())
                ->setEntityType('issue')
                ->setEntityId($bug->getId())
                ->addEntity($comment);

            $this->getNotificationsSender()->send($notif);

            // Subscribe automatically the user
            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'issue', $bug->getId());

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'bugs.bugs.view.comment_confirm',
            ]);

            return $this->redirect($this->generateUrl('bugs_view', [
                'id' => $bug->getId(),
                'slug' => StringManipulationExtension::slugify($bug->getTitle()),
            ]));
        }

        $updateForm = $this->createFormBuilder($bug)
            ->setAction($this->generateUrl('bugs_admin_criticality', ['id' => $bug->getId(), 'slug' => StringManipulationExtension::slugify($bug->getTitle())]))
            ->add('criticality', ChoiceType::class, [
                'choices' => [
                    'bugs.criticality.60' => Issue::CRITICALITY_SECURITY,
                    'bugs.criticality.50' => Issue::CRITICALITY_CRITICAL,
                    'bugs.criticality.40' => Issue::CRITICALITY_MAJOR,
                    'bugs.criticality.30' => Issue::CRITICALITY_MINOR,
                    'bugs.criticality.20' => Issue::CRITICALITY_VISUAL,
                    'bugs.criticality.10' => Issue::CRITICALITY_TYPO,
                ],
            ])
            ->add('submit', SubmitType::class, ['label' => 'bugs.bugs.view.admin_edit'])
            ->getForm();

        return [
            'bug' => $bug,
            'comments' => $comments,
            'form' => $form->createView(),
            'updateForm' => $updateForm->createView(),
        ];
    }

    /**
     * @Route("/create", name="bugs_create")
     * @Template()
     */
    public function createAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS_POST');

        $bug = new Issue();
        $bug->setUser($this->getUser());

        $form = $this->createFormBuilder($bug)
            ->add('title', TextType::class, ['label' => 'bugs.bugs.create.name'])
            ->add('criticality', ChoiceType::class, [
                'choices' => [
                    'bugs.criticality.60' => Issue::CRITICALITY_SECURITY,
                    'bugs.criticality.50' => Issue::CRITICALITY_CRITICAL,
                    'bugs.criticality.40' => Issue::CRITICALITY_MAJOR,
                    'bugs.criticality.30' => Issue::CRITICALITY_MINOR,
                    'bugs.criticality.20' => Issue::CRITICALITY_VISUAL,
                    'bugs.criticality.10' => Issue::CRITICALITY_TYPO,
                ],
                'label' => 'bugs.bugs.create.criticality',
            ])
            ->add('body', EditorType::class, ['label' => 'bugs.bugs.create.body'])
            ->add('submit', SubmitType::class, ['label' => 'bugs.bugs.create.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            /** @var $em EntityManager */
            $em = $this->getDoctrine()->getManager();

            $em->persist($bug);
            $em->flush();

            // Send notifications to subscribers ("entityId = 0" mean all opened bugs)
            $notif = new Notification();

            $notif
                ->setModule('bugs')
                ->setHelper('bugs_new_opened')
                ->setAuthorId($this->getUser()->getId())
                ->setEntityType('issue')
                ->setEntityId(0)
                ->addEntity($bug);

            $this->getNotificationsSender()->send($notif);

            // Subscribe automatically the user at the issue
            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'issue', $bug->getId());

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'bugs.bugs.create.confirm',
            ]);

            return $this->redirect($this->generateUrl('bugs_view', [
                'id' => $bug->getId(),
                'slug' => StringManipulationExtension::slugify($bug->getTitle()),
            ]));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/{id}-{slug}/edit", requirements = {"id" = "\d+"}, name="bugs_edit")
     * @Template()
     *
     * @param mixed $id
     * @param mixed $slug
     */
    public function editAction($id, $slug, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS_POST');

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

        if ($bug->getUser()->getId() != $this->getUser()->getId() && !isGranted('ROLE_BUGS_ADMIN')) {
            throw new AccessDeniedHttpException('Vous n\'avez pas le droit de modifier ce signalement.');
        }

        $form = $this->createFormBuilder($bug)
            ->add('title', TextType::class, ['label' => 'bugs.bugs.edit.title'])
            ->add('criticality', ChoiceType::class, [
                'choices' => [
                    'bugs.criticality.60' => Issue::CRITICALITY_SECURITY,
                    'bugs.criticality.50' => Issue::CRITICALITY_CRITICAL,
                    'bugs.criticality.40' => Issue::CRITICALITY_MAJOR,
                    'bugs.criticality.30' => Issue::CRITICALITY_MINOR,
                    'bugs.criticality.20' => Issue::CRITICALITY_VISUAL,
                    'bugs.criticality.10' => Issue::CRITICALITY_TYPO,
                ],
                'label' => 'bugs.bugs.edit.criticality',
            ])
            ->add('body', EditorType::class, ['label' => 'bugs.bugs.edit.body'])
            ->add('submit', SubmitType::class, ['label' => 'bugs.bugs.edit.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($bug);
            $em->flush();

            // Subscribe automatically the user at the issue
            $subscription = new Subscription();
            $subscription->setUser($this->getUser());
            $subscription->setEntityType('issue');
            $subscription->setEntityId($bug->getId());

            $em->persist($subscription);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'bugs.bugs.edit.confirm',
            ]);

            return $this->redirect($this->generateUrl('bugs_view', [
                'id' => $bug->getId(),
                'slug' => StringManipulationExtension::slugify($bug->getTitle()),
            ]));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route(
     *      "/{issueId}-{slug}/edit/comment/{id}",
     *      requirements = {"issueId" = "\d+", "id" = "\d+"},
     *      name="bugs_edit_comment"
     * )
     * @Template()
     *
     * @param mixed $slug
     * @param mixed $id
     */
    public function editCommentAction($slug, $id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_BUGS_POST');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var $comment Comment */
        $comment = $em->createQueryBuilder()
            ->select('c, i, u')
            ->from('EtuModuleBugsBundle:Comment', 'c')
            ->leftJoin('c.issue', 'i')
            ->leftJoin('c.user', 'u')
            ->where('c.id = :id')
            ->setParameter('id', $id)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();

        if (!$comment) {
            throw $this->createNotFoundException('Comment #'.$id.' not found');
        }

        if (StringManipulationExtension::slugify($comment->getIssue()->getTitle()) != $slug) {
            return $this->redirect($this->generateUrl('bugs_edit_comment', [
                'id' => $id, 'slug' => StringManipulationExtension::slugify($comment->getIssue()->getTitle()),
            ]), 301);
        }

        if ($comment->getUser()->getId() != $this->getUser()->getId() && !$this->isGranted('ROLE_BUGS_ADMIN')) {
            throw new AccessDeniedHttpException('Vous n\'avez pas le droit de modifier ce commentaire.');
        }

        $form = $this->createFormBuilder($comment)
            ->add('body', EditorType::class, ['label' => 'bugs.bugs.view.comment_body'])
            ->add('submit', SubmitType::class, ['label' => 'bugs.bugs.edit.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();

            $em->persist($comment);
            $em->flush();

            $em->persist($comment);
            $em->flush();

            return $this->redirect($this->generateUrl('bugs_view', [
                'id' => $comment->getIssue()->getId(),
                'slug' => StringManipulationExtension::slugify($comment->getIssue()->getTitle()),
            ]));
        }

        return [
            'form' => $form->createView(),
        ];
    }
}

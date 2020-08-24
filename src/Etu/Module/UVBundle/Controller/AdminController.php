<?php

namespace Etu\Module\UVBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\UVBundle\Entity\Comment;
use Etu\Module\UVBundle\Entity\Review;
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
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW_ADMIN');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $comments = $em->createQueryBuilder()
            ->select('c, u, a')
            ->from('EtuModuleUVBundle:Comment', 'c')
            ->leftJoin('c.uv', 'u')
            ->leftJoin('c.user', 'a')
            ->where('c.isValide = :valide')
            ->setParameter('valide', false)
            ->orderBy('c.createdAt', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult();

        $reviews = $em->createQueryBuilder()
            ->select('r, u, s')
            ->from('EtuModuleUVBundle:Review', 'r')
            ->leftJoin('r.uv', 'u')
            ->leftJoin('r.sender', 's')
            ->where('r.validated = :valide')
            ->setParameter('valide', false)
            ->orderBy('r.createdAt', 'DESC')
            ->setMaxResults(20)
            ->getQuery()
            ->getResult();

        return [
            'comments' => $comments,
            'reviews' => $reviews,
        ];
    }

    /**
     * @Route("/reviews/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="admin_uvs_reviews")
     * @Template()
     *
     * @param mixed $page
     */
    public function reviewsAction($page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW_ADMIN');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('r, u, s')
            ->from('EtuModuleUVBundle:Review', 'r')
            ->leftJoin('r.uv', 'u')
            ->leftJoin('r.sender', 's')
            ->addOrderBy('r.validated', 'ASC')
            ->addOrderBy('r.createdAt', 'DESC')
            ->getQuery();

        $pagination = $this->get('knp_paginator')->paginate($query, $page, 40);

        return [
            'pagination' => $pagination,
        ];
    }

    /**
     * @Route("/review/{id}/validate", name="admin_uvs_review_validate")
     * @Template()
     */
    public function validateReviewAction(Review $review)
    {
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW_ADMIN');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $review->setValidated(true);

        $em->persist($review);
        $em->flush();

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'uvs.admin.validateReview.confirm',
        ]);

        return $this->redirect($this->generateUrl('admin_uvs_reviews'));
    }

    /**
     * @Route("/review/{id}/unvalidate", name="admin_uvs_review_unvalidate")
     * @Template()
     */
    public function unvalidateReviewAction(Review $review)
    {
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW_ADMIN');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $review->setValidated(false);

        $em->persist($review);
        $em->flush();

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'uvs.admin.unvalidateReview.confirm',
        ]);

        return $this->redirect($this->generateUrl('admin_uvs_reviews'));
    }

    /**
     * @Route("/comment/{id}/validate", name="admin_uvs_comment_validate")
     * @Template()
     *
     * @param Comment $comment
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function validateCommentAction(Comment $comment)
    {
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW_ADMIN');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $comment->setIsValide(true);

        $em->persist($comment);
        $em->flush();

        // Notify subscribers
        $notif = new Notification();

        $notif
            ->setModule('uv')
            ->setHelper('uv_new_comment')
            ->setAuthorId($comment->getUser()->getId())
            ->setEntityType('uv')
            ->setEntityId($comment->getId())
            ->addEntity($comment);

        $this->getNotificationsSender()->send($notif);

        $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'uvs.admin.validateComment.confirm',
            ]);

        return $this->redirect($this->generateUrl('admin_uvs_comments'));
    }

    /**
     * @Route("/comment/{id}/unvalidate", name="admin_uvs_comment_unvalidate")
     * @Template()
     */
    public function unvalidateCommentAction(Comment $comment)
    {
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW_ADMIN');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $comment->setIsValide(false);

        $em->persist($comment);
        $em->flush();

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'uvs.admin.unvalidateComment.confirm',
        ]);

        return $this->redirect($this->generateUrl('admin_uvs_comments'));
    }

    /**
     * @Route("/review/{id}/delete", name="admin_uvs_review_delete")
     * @Template()
     */
    public function deleteReviewAction(Review $review)
    {
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW_ADMIN');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $path = __DIR__.'/../../../../../web/uploads/uvs/'.$review->getFilename();
        if (file_exists($path)) {
            unlink($path);
        }

        $em->remove($review);
        $em->flush();

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'uvs.admin.deleteReview.confirm',
        ]);

        return $this->redirect($this->generateUrl('admin_uvs_reviews'));
    }

    /**
     * @Route("/comments/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="admin_uvs_comments")
     * @Template()
     *
     * @param mixed $page
     */
    public function commentsAction($page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW_ADMIN');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $query = $em->createQueryBuilder()
            ->select('c, u, a')
            ->from('EtuModuleUVBundle:Comment', 'c')
            ->leftJoin('c.uv', 'u')
            ->leftJoin('c.user', 'a')
            ->addOrderBy('c.isValide', 'ASC')
            ->addOrderBy('c.createdAt', 'DESC')
            ->getQuery();

        $pagination = $this->get('knp_paginator')->paginate($query, $page, 20);

        return [
            'pagination' => $pagination,
        ];
    }

    /**
     * @Route("/comment/{id}/delete", name="admin_uvs_comment_delete")
     * @Template()
     */
    public function deleteCommentAction(Comment $comment)
    {
        if ($comment->getUser() != $this->getUser()) {
            $this->denyAccessUnlessGranted('ROLE_UV_REVIEW_ADMIN');
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $em->remove($comment);
        $em->flush();

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'uvs.admin.deleteComment.confirm',
        ]);
        if (!$this->isGranted('ROLE_UV_REVIEW_ADMIN')) {
            return $this->redirectToRoute('uvs_goto', ['code' => $comment->getUv()->getCode()]);
        }

        return $this->redirectToRoute('admin_uvs_comments');
    }
}

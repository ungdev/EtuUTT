<?php

namespace Etu\Module\UVBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Form\EditorType;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\CoreBundle\Util\SendSlack;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Model\BadgesManager;
use Etu\Module\UVBundle\Entity\Comment;
use Etu\Module\UVBundle\Entity\Review;
use Etu\Module\UVBundle\Entity\UV;
use League\HTMLToMarkdown\HtmlConverter;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Finder\Exception\AccessDeniedException;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
// Import annotations
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/uvs")
 */
class ViewController extends Controller
{
    /**
     * @Route("/{slug}-{name}/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="uvs_view")
     * @Template()
     *
     * @param mixed $slug
     * @param mixed $name
     * @param mixed $page
     */
    public function viewAction(Request $request, $slug, $name, $page = 1)
    {
        $this->denyAccessUnlessGranted('ROLE_UV');

        $rtn = [];

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var UV $uv */
        $uv = $em->getRepository('EtuModuleUVBundle:UV')
            ->findOneBy(['slug' => $slug]);
        $rtn['uv'] = $uv;

        if (!$uv) {
            throw $this->createNotFoundException(sprintf('UV for slug %s not found', $slug));
        }

        if (StringManipulationExtension::slugify($uv->getName()) != $name) {
            return $this->redirect($this->generateUrl('uvs_view', [
                'slug' => $uv->getSlug(), 'name' => StringManipulationExtension::slugify($uv->getName()),
            ]), 301);
        }

        // UV review post submit
        if ($this->isGranted('ROLE_UV_REVIEW_POST')) {
            $comment = new Comment();
            $comment->setUv($uv)
                ->setUser($this->getUser())
                ->setValide(false);

            $commentForm = $this->createFormBuilder($comment)
                ->add('body', EditorType::class, ['label' => 'uvs.main.view.body'])
                ->add('anonyme', CheckboxType::class, ['label' => 'uvs.main.view.anon', 'required' => false])
                ->add('submit', SubmitType::class, ['label' => 'uvs.main.view.submit'])
                ->getForm();

            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $comment->setValide(false);
                $em->persist($comment);
                $em->flush();

                $converter = new HtmlConverter();

                $jsonData = json_encode(['blocks' => [
                    [
                        'type' => 'header',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'Nouveau commentaire',
                        ],
                    ],
                    [
                        'type' => 'context',
                        'elements' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => 'Soumis par *'.($comment->getAnonyme() ? 'anonyme' : $comment->getUser()->getFullName().' ('.$comment->getUser()->getLogin().')').'* pour *'.mb_strtoupper($comment->getUv()->getSlug()).'*',
                            ],
                        ],
                    ],
                    [
                        'type' => 'divider',
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $converter->convert($comment->getBody()),
                        ],
                    ],
                    [
                        'type' => 'actions',
                        'block_id' => 'comment_'.$comment->getId(),
                        'elements' => [
                            [
                                'type' => 'button',
                                'action_id' => 'ok',
                                'text' => [
                                    'type' => 'plain_text',
                                    'text' => 'Approuver',
                                ],
                                'style' => 'primary',
                            ],
                            [
                                'type' => 'button',
                                'text' => [
                                    'type' => 'plain_text',
                                    'text' => 'Supprimer',
                                ],
                                'action_id' => 'delete',
                                'style' => 'danger',
                            ],
                        ],
                    ],
                ],
                ]);
                SendSlack::curl_send($this->container->getParameter('slack_webhook_moderation'), $jsonData);

                $this->get('session')->getFlashBag()->set('message', [
                    'type' => 'success',
                    'message' => 'uvs.main.comment.confirm',
                ]);

                return $this->redirect($this->generateUrl('uvs_view', [
                    'slug' => $slug,
                    'name' => $name,
                ]));
            }
            $rtn['commentForm'] = $commentForm->createView();
        }

        if ($this->isGranted('ROLE_UV_REVIEW')) {
            // Get UV annals

            /** @var Review[] $results */
            $results = $em->createQueryBuilder()
                ->select('r, s')
                ->from('EtuModuleUVBundle:Review', 'r')
                ->leftJoin('r.sender', 's')
                ->where('r.uv = :uv')
                ->setParameter('uv', $uv->getId())
                ->getQuery()
                ->getResult();

            $order = [];

            // Order by semester: A13, P12, A12, P11, ...
            foreach ($results as $review) {
                $semester = (int) mb_substr($review->getSemester(), 1);
                $season = 'A' == (mb_substr($review->getSemester(), 0, 1)) ? 1 : 0;
                $order[] = $semester * 2 + $season;
            }

            array_multisort(
                $order, SORT_DESC, SORT_NUMERIC,
                $results
            );

            $reviews = [];
            $reviewsCount = 0;

            foreach ($results as $result) {
                if (!isset($reviews[$result->getSemester()]['count'])) {
                    $reviews[$result->getSemester()]['count'] = 0;
                }

                if (!isset($reviews[$result->getSemester()]['validated'])) {
                    $reviews[$result->getSemester()]['validated'] = [];
                }

                if (!isset($reviews[$result->getSemester()]['pending'])) {
                    $reviews[$result->getSemester()]['pending'] = [];
                }

                $key = ($result->getValidated()) ? 'validated' : 'pending';
                $reviews[$result->getSemester()][$key][] = $result;
                ++$reviews[$result->getSemester()]['count'];
                ++$reviewsCount;
            }

            // Get UV comments
            $query = $em->createQueryBuilder()
                ->select('c, u')
                ->from('EtuModuleUVBundle:Comment', 'c')
                ->leftJoin('c.user', 'u')
                ->where('c.uv = :uv')
                ->setParameter('uv', $uv->getId())
                ->addOrderBy('c.valide', 'ASC')
                ->addOrderBy('c.createdAt', 'DESC')
                ->getQuery();

            $pagination = $this->get('knp_paginator')->paginate($query, $page, 10);

            $rtn['semesters'] = $reviews;
            $rtn['reviewsCount'] = $reviewsCount;
            $rtn['pagination'] = $pagination;
            $rtn['user'] = $this->getUser();
        }

        return $rtn;
    }

    /**
     * @Route("/editUEComment/{id}", name="uvs_edit_comment")
     * @Template()
     *
     * @param Request       $request
     * @param EntityManager $em
     * @param Comment       $comment
     *
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function editUEComment(Request $request, Comment $comment)
    {
        $em = $this->getDoctrine()->getManager();
        if (($this->getUser() === $comment->getUser() && $this->isGranted('ROLE_UV_REVIEW_POST')) || $this->isGranted('ROLE_UV_REVIEW_ADMIN')) {
            $commentForm = $this->createFormBuilder($comment)
                ->add('body', EditorType::class, ['label' => 'uvs.main.view.body'])
                ->add('anonyme', CheckboxType::class, ['label' => 'uvs.main.view.anon', 'required' => false])
                ->add('submit', SubmitType::class, ['label' => 'uvs.main.view.submit'])
                ->getForm();
            $commentForm->handleRequest($request);
            if ($commentForm->isSubmitted() && $commentForm->isValid()) {
                $comment->setValide(false);
                $em->persist($comment);
                $em->flush();
                $converter = new HtmlConverter();

                $jsonData = json_encode(['blocks' => [
                    [
                        'type' => 'header',
                        'text' => [
                            'type' => 'plain_text',
                            'text' => 'Nouveau commentaire',
                        ],
                    ],
                    [
                        'type' => 'context',
                        'elements' => [
                            [
                                'type' => 'mrkdwn',
                                'text' => 'Soumis par *'.($comment->getAnonyme() ? 'anonyme' : $comment->getUser()->getFullName().' ('.$comment->getUser()->getLogin().')').'* pour *'.mb_strtoupper($comment->getUv()->getSlug()).'*',
                            ],
                        ],
                    ],
                    [
                        'type' => 'divider',
                    ],
                    [
                        'type' => 'section',
                        'text' => [
                            'type' => 'mrkdwn',
                            'text' => $converter->convert($comment->getBody()),
                        ],
                    ],
                    [
                        'type' => 'actions',
                        'block_id' => 'comment_'.$comment->getId(),
                        'elements' => [
                            [
                                'type' => 'button',
                                'action_id' => 'ok',
                                'text' => [
                                    'type' => 'plain_text',
                                    'text' => 'Approuver',
                                ],
                                'style' => 'primary',
                            ],
                            [
                                'type' => 'button',
                                'text' => [
                                    'type' => 'plain_text',
                                    'text' => 'Supprimer',
                                ],
                                'action_id' => 'delete',
                                'style' => 'danger',
                            ],
                        ],
                    ],
                ],
                ]);
                SendSlack::curl_send($this->container->getParameter('slack_webhook_moderation'), $jsonData);

                return $this->redirectToRoute('uvs_view', ['slug' => $comment->getUv()->getSlug(), 'name' => $comment->getUv()->getName()]);
            }

            return $this->render('@EtuModuleUV/View/editComment.html.twig', ['commentForm' => $commentForm->createView()]);
        }

        throw new AccessDeniedException("Vous n'avez pas l'autorisation de modifier ce commentaire");
    }

    /**
     * @Route("/{slug}-{name}/courses", name="uvs_view_courses")
     * @Template()
     *
     * @param mixed $slug
     * @param mixed $name
     */
    public function coursesAction($slug, $name)
    {
        $this->denyAccessUnlessGranted('ROLE_UV');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var UV $uv */
        $uv = $em->getRepository('EtuModuleUVBundle:UV')
            ->findOneBy(['slug' => $slug]);

        if (!$uv) {
            throw $this->createNotFoundException(sprintf('UV for slug %s not found', $slug));
        }

        if (StringManipulationExtension::slugify($uv->getName()) != $name) {
            return $this->redirect($this->generateUrl('uvs_view', [
                'slug' => $uv->getSlug(), 'name' => StringManipulationExtension::slugify($uv->getName()),
            ]), 301);
        }

        /** @var $results Course[] */
        $results = $em->createQueryBuilder()
            ->select('c')
            ->from('EtuUserBundle:Course', 'c')
            ->where('c.uv = :uv')
            ->setParameter('uv', mb_strtoupper($slug))
            ->orderBy('c.start')
            ->groupBy('c.day, c.room, c.start')
            ->getQuery()
            ->getResult();

        /** @var $courses Course[] */
        $courses = [];

        $days = [
            Course::DAY_MONDAY => 1, Course::DAY_TUESDAY => 2, Course::DAY_WENESDAY => 3,
            Course::DAY_THURSDAY => 4, Course::DAY_FRIDAY => 5, Course::DAY_SATHURDAY => 6,
        ];

        $orderDay = [];
        $orderHour = [];

        foreach ($results as $course) {
            $courses[] = $course;
            $orderDay[] = $days[$course->getDay()];
            $orderHour[] = $course->getStart();
        }

        array_multisort(
            $orderDay, SORT_ASC, SORT_NUMERIC,
            $orderHour, SORT_ASC, SORT_NUMERIC,
            $courses
        );

        /** @var $results Course[] */
        $results = [];

        foreach ($courses as $course) {
            $results[$course->getDay()][] = $course;
        }

        return [
            'uv' => $uv,
            'courses' => $results,
        ];
    }

    /**
     * @Route("/{slug}-{name}/send-review", name="uvs_view_send_review")
     * @Template()
     *
     * @param mixed $slug
     * @param mixed $name
     */
    public function sendReviewAction(Request $request, $slug, $name)
    {
        $this->denyAccessUnlessGranted('ROLE_UV_REVIEW_POST');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        /** @var UV $uv */
        $uv = $em->getRepository('EtuModuleUVBundle:UV')
            ->findOneBy(['slug' => $slug]);

        if (!$uv) {
            throw $this->createNotFoundException(sprintf('UV for slug %s not found', $slug));
        }

        if (StringManipulationExtension::slugify($uv->getName()) != $name) {
            return $this->redirect($this->generateUrl('uvs_view_send_review', [
                'slug' => $uv->getSlug(), 'name' => StringManipulationExtension::slugify($uv->getName()),
            ]), 301);
        }

        $review = new Review();
        $review->setUv($uv)
            ->setSender($this->getUser())
            ->setSemester(User::currentSemester());

        $form = $this->createFormBuilder($review)
            ->add('type', ChoiceType::class, ['choices' => array_flip(Review::$types), 'required' => true, 'label' => 'uvs.main.sendReview.type'])
            ->add('semester', ChoiceType::class, ['choices' => array_flip(Review::availableSemesters()), 'required' => true, 'label' => 'uvs.main.sendReview.semester'])
            ->add('file', null, ['required' => true, 'label' => 'uvs.main.sendReview.file'])
            ->add('submit', SubmitType::class, ['label' => 'uvs.main.sendReview.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $review->upload();

            $em->persist($review);
            $em->flush();

            // Notify subscribers
            $notif = new Notification();

            $review->file = null;

            $notif
                ->setModule('uv')
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

            $user = $this->getUser();

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

            $em->persist($user);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'uvs.main.sendReview.confirm',
            ]);

            return $this->redirect($this->generateUrl('uvs_view', [
                'slug' => $slug,
                'name' => $name,
            ]));
        }

        return [
            'uv' => $uv,
            'form' => $form->createView(),
        ];
    }
}

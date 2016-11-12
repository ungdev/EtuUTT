<?php

namespace Etu\Module\ForumBundle\Controller;

use Doctrine\ORM\EntityRepository;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\UserBundle\Model\BadgesManager;
use Etu\Module\ForumBundle\Entity\Category;
use Etu\Module\ForumBundle\Entity\Thread;
use Etu\Module\ForumBundle\Entity\Message;
use Etu\Module\ForumBundle\Entity\View;
use Etu\Module\ForumBundle\Entity\CategoryView;
use Etu\Module\ForumBundle\Form\ThreadType;
use Etu\Module\ForumBundle\Form\ThreadTypeNoSticky;
use Etu\Module\ForumBundle\Form\MessageEditType;
use Etu\Module\ForumBundle\Form\MessageType;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Etu\Module\ForumBundle\Model\PermissionsChecker;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
    /**
     * @Route("/forum", name="forum_index")
     * @Template()
     */
    public function indexAction()
    {
        $this->denyAccessUnlessGranted('ROLE_FORUM');

        $em = $this->getDoctrine()->getManager();
        $categories = $em->createQueryBuilder()
            ->select('c, v')
            ->from('EtuModuleForumBundle:Category', 'c')
            ->leftJoin('c.categoryViewed', 'v', 'WITH', 'v.user = :user')
            ->setParameter('user', $this->getUser())
            ->where('c.depth <= 1')
            ->orderBy('c.left')
            ->getQuery()
            ->getResult();

        return array('categories' => $categories);
    }

    /**
     * @Route("/forum/{id}-{slug}/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="forum_category")
     * @Template()
     */
    public function categoryAction($id, $slug, $page)
    {
        $this->denyAccessUnlessGranted('ROLE_FORUM');

        $em = $this->getDoctrine()->getManager();

        /** @var Category $category */
        $category = $em->getRepository('EtuModuleForumBundle:Category')
            ->find($id);

        $checker = new PermissionsChecker($this->getUser());

        if (!$checker->canRead($category) && !$this->isGranted('ROLE_FORUM_ADMIN')) {
            return $this->createAccessDeniedResponse();
        }

        $parents = $em->createQueryBuilder()
            ->select('c')
            ->from('EtuModuleForumBundle:Category', 'c')
            ->where('c.left <= :left')
            ->andWhere('c.right >= :right')
            ->andWhere('c.id != :id')
            ->setParameter('left', $category->getLeft())
            ->setParameter('right', $category->getRight())
            ->setParameter('id', $category->getId())
            ->orderBy('c.depth')
            ->getQuery()
            ->getResult();

        $depth = count($parents) + 1;

        $subCategories = $em->createQueryBuilder()
            ->select('c, v')
            ->from('EtuModuleForumBundle:Category', 'c')
            ->leftJoin('c.categoryViewed', 'v', 'WITH', 'v.user = :user')
            ->where('c.left > :left')
            ->andWhere('c.right < :right')
            ->andWhere('c.depth = :depth')
            ->setParameter('left', $category->getLeft())
            ->setParameter('right', $category->getRight())
            ->setParameter('depth', $depth)
            ->setParameter('user', $this->getUser())
            ->orderBy('c.left')
            ->getQuery()
            ->getResult();

        $threads = $em->createQueryBuilder()
            ->select('t, m, v')
            ->from('EtuModuleForumBundle:Thread', 't')
            ->leftJoin('t.lastMessage', 'm')
            ->leftJoin('t.viewed', 'v', 'WITH', 'v.user = :user')
            ->where('t.category = :category')
            ->andWhere('t.state != 300')
            ->setParameter('category', $category)
            ->setParameter('user', $this->getUser())
            ->orderBy('t.weight', 'DESC')
            ->addOrderBy('m.createdAt', 'DESC')
            ->getQuery()
            ->getResult();

        $threads = $this->get('knp_paginator')->paginate($threads, $page, 15);

        $noThreads = true;
        if (count($threads) > 0) {
            $noThreads = false;
        }

        $isSubCategories = false;
        if (count($subCategories) > 0) {
            $isSubCategories = true;
        }

        $views = $em->createQueryBuilder()
            ->select('v')
            ->from('EtuModuleForumBundle:CategoryView', 'v')
            ->where('v.category = :category')
            ->setParameter('category', $category)
            ->andWhere('v.user = :user')
            ->setParameter('user', $this->getUser())
            ->getQuery()
            ->getResult();

        if ($this->getUser() && count($views) == 0) {
            $viewed = new CategoryView();
            $viewed->setUser($this->getUser())
                ->setCategory($category);
            $em->persist($viewed);
            $em->flush();
        }

        return array('category' => $category, 'subCategories' => $subCategories, 'parents' => $parents, 'threads' => $threads, 'noThreads' => $noThreads, 'isSubCategories' => $isSubCategories);
    }

    /**
     * @Route("/forum/thread/{id}-{slug}/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="forum_thread")
     * @Template()
     */
    public function threadAction($id, $slug, $page)
    {
        $this->denyAccessUnlessGranted('ROLE_FORUM');

        $em = $this->getDoctrine()->getManager();
        $thread = $em->createQueryBuilder()
            ->select('t, c')
            ->from('EtuModuleForumBundle:Thread', 't')
            ->leftJoin('t.category', 'c')
            ->where('t.id = :id')
            ->andWhere('t.state != 300')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();

        $category = $thread->getCategory();

        $checker = new PermissionsChecker($this->getUser());
        if (!$checker->canRead($category) && !$this->isGranted('ROLE_FORUM_ADMIN')) {
            return $this->createAccessDeniedResponse();
        }

        $parents = $em->createQueryBuilder()
            ->select('c')
            ->from('EtuModuleForumBundle:Category', 'c')
            ->where('c.left <= :left')
            ->andWhere('c.right >= :right')
            ->setParameter('left', $category->getLeft())
            ->setParameter('right', $category->getRight())
            ->orderBy('c.depth')
            ->getQuery()
            ->getResult();

        $messages = $em->createQueryBuilder()
            ->select('m, u')
            ->from('EtuModuleForumBundle:Message', 'm')
            ->leftJoin('m.author', 'u')
            ->where('m.thread = :thread')
            ->setParameter('thread', $thread)
            ->orderBy('m.createdAt')
            ->getQuery()
            ->getResult();

        $messages = $this->get('knp_paginator')->paginate($messages, $page, 10);

        $cantAnswer = (bool) ($thread->getState() == 200 && !$checker->canLock($category) && !$this->isGranted('ROLE_FORUM_ADMIN'));

        $views = $em->createQueryBuilder()
            ->select('v')
            ->from('EtuModuleForumBundle:View', 'v')
            ->where('v.thread = :thread')
            ->setParameter('thread', $thread)
            ->andWhere('v.user = :user')
            ->setParameter('user', $this->getUser())
            ->getQuery()
            ->getResult();

        if ($this->getUser() && count($views) == 0) {
            $viewed = new View();
            $viewed->setUser($this->getUser())
                ->setThread($thread);
            $em->persist($viewed);
            $em->flush();
        }

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message, ['action' => $this->generateUrl('forum_answer', ['id' => $id, 'slug' => $slug])]);

        return array(
            'category' => $category,
            'thread' => $thread,
            'parents' => $parents,
            'messages' => $messages,
            'cantAnswer' => $cantAnswer,
            'form' => $form->createView(),
        );
    }

    /**
     * @Route("/forum/post/{id}-{slug}", name="forum_post")
     * @Template()
     */
    public function postAction($id, $slug, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_FORUM_POST');

        $em = $this->getDoctrine()->getManager();
        $category = $em->getRepository('EtuModuleForumBundle:Category')
            ->find($id);

        $checker = new PermissionsChecker($this->getUser());
        if (!$checker->canPost($category) && !$this->isGranted('ROLE_FORUM_ADMIN')) {
            return $this->createAccessDeniedResponse();
        }

        $parents = $em->createQueryBuilder()
            ->select('c')
            ->from('EtuModuleForumBundle:Category', 'c')
            ->where('c.left <= :left')
            ->andWhere('c.right >= :right')
            ->setParameter('left', $category->getLeft())
            ->setParameter('right', $category->getRight())
            ->orderBy('c.depth')
            ->getQuery()
            ->getResult();

        $thread = new Thread();
        if ($checker->canSticky($category) || $this->isGranted('ROLE_FORUM_ADMIN')) {
            $form = $this->createForm(ThreadType::class, $thread);
        } else {
            $form = $this->createForm(ThreadTypeNoSticky::class, $thread);
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if ($thread->getWeight() != 100 && !$checker->canSticky($category) && !$this->isGranted('ROLE_FORUM_ADMIN')) {
                $thread->setWeight(100);
            }
            $thread->setAuthor($this->getUser())
                ->setCategory($category)
                ->setCountMessages(1)
                ->setSlug(StringManipulationExtension::slugify($thread->getTitle()));
            $message = $thread->getLastMessage();
            $message->setAuthor($this->getUser())
                ->setCategory($category)
                ->setThread($thread)
                ->setState(100)
                ->setCreatedAt($thread->getCreatedAt());
            $thread->setLastMessage($message);
            foreach ($parents as $parent) {
                $parent->setLastMessage($message)
                    ->setCountMessages($parent->getCountMessages() + 1)
                    ->setCountThreads($parent->getCountThreads() + 1);
                $em->persist($parent);
            }

            $em->persist($thread);

            $cviews = $em->getRepository('EtuModuleForumBundle:CategoryView')
                ->findByCategory($category);
            foreach ($cviews as $cview) {
                $em->remove($cview);
            }

            $em->flush();

            $this->giveBadges();

            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'message', $thread->getId());

            return $this->redirect($this->generateUrl('forum_thread', array('id' => $thread->getId(), 'slug' => $thread->getSlug())));
        }

        return array('category' => $category, 'parents' => $parents, 'form' => $form->createView());
    }

    /**
     * @Route("/forum/answer/{id}-{slug}", name="forum_answer")
     * @Template()
     */
    public function answerAction($id, $slug, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_FORUM_POST');

        $em = $this->getDoctrine()->getManager();
        $thread = $em->createQueryBuilder()
            ->select('t, c')
            ->from('EtuModuleForumBundle:Thread', 't')
            ->leftJoin('t.category', 'c')
            ->where('t.id = :id')
            ->andWhere('t.state != 300')
            ->setParameter('id', $id)
            ->getQuery()
            ->getSingleResult();

        $category = $thread->getCategory();

        $checker = new PermissionsChecker($this->getUser());
        if ((!$checker->canAnswer($category) && !$this->isGranted('ROLE_FORUM_ADMIN')) || ($thread->getState() == 200 && !$checker->canLock($category) && !$this->isGranted('ROLE_FORUM_ADMIN'))) {
            return $this->createAccessDeniedResponse();
        }

        $parents = $em->createQueryBuilder()
            ->select('c')
            ->from('EtuModuleForumBundle:Category', 'c')
            ->where('c.left <= :left')
            ->andWhere('c.right >= :right')
            ->setParameter('left', $category->getLeft())
            ->setParameter('right', $category->getRight())
            ->orderBy('c.depth')
            ->getQuery()
            ->getResult();

        $message = new Message();
        $form = $this->createForm(MessageType::class, $message);

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $message->setAuthor($this->getUser())
                ->setCategory($category)
                ->setThread($thread)
                ->setState(100);
            $thread->setCountMessages($thread->getCountMessages() + 1)
                ->setLastMessage($message);
            foreach ($parents as $parent) {
                $parent->setLastMessage($message)
                    ->setCountMessages($parent->getCountMessages() + 1);
                $em->persist($parent);
            }
            $em->persist($thread);

            $views = $em->getRepository('EtuModuleForumBundle:View')
                ->findByThread($thread);
            foreach ($views as $view) {
                $em->remove($view);
            }
            $cviews = $em->getRepository('EtuModuleForumBundle:CategoryView')
                ->findByCategory($category);
            foreach ($cviews as $cview) {
                $em->remove($cview);
            }
            $em->flush();

            $this->giveBadges();

            $page = ceil($thread->getCountMessages() / 10);

            $notif = new Notification();
            $notif
                ->setModule('forum')
                ->setHelper('thread_answered')
                ->setAuthorId($this->getUser()->getId())
                ->setEntityType('message')
                ->setEntityId($thread->getId())
                ->addEntity($message);

            $this->getNotificationsSender()->send($notif);

            $this->getSubscriptionsManager()->subscribe($this->getUser(), 'message', $thread->getId());

            return $this->redirect($this->generateUrl('forum_thread', array('id' => $thread->getId(), 'slug' => $thread->getSlug(), 'page' => $page)).'#'.$message->getId());
        }

        return array('thread' => $thread, 'parents' => $parents, 'form' => $form->createView());
    }

    /**
     * @Route("/forum/edit/{threadId}-{slug}/{messageId}", name="forum_edit")
     * @Template()
     */
    public function editAction($messageId, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_FORUM_POST');

        $em = $this->getDoctrine()->getManager();
        $message = $em->createQueryBuilder()
            ->select('m, t')
            ->from('EtuModuleForumBundle:Message', 'm')
            ->leftJoin('m.thread', 't')
            ->where('m.id = :id')
            ->andWhere('t.state != 300')
            ->setParameter('id', $messageId)
            ->getQuery()
            ->getSingleResult();

        $thread = $message->getThread();
        $category = $message->getCategory();

        $checker = new PermissionsChecker($this->getUser());
        if ((!$checker->canEdit($category) && !$this->isGranted('ROLE_FORUM_ADMIN')) || ($thread->getState() == 200 && !$checker->canLock($category) && !$this->isGranted('ROLE_FORUM_ADMIN'))) {
            return $this->createAccessDeniedResponse();
        }

        $parents = $em->createQueryBuilder()
            ->select('c')
            ->from('EtuModuleForumBundle:Category', 'c')
            ->where('c.left <= :left')
            ->andWhere('c.right >= :right')
            ->setParameter('left', $category->getLeft())
            ->setParameter('right', $category->getRight())
            ->orderBy('c.depth')
            ->getQuery()
            ->getResult();

        if ($message->getCreatedAt() == $thread->getCreatedAt()) {
            $form = $this->createForm(MessageEditType::class, $message,  ['action' => $this->generateUrl('forum_edit', ['messageId' => $message->getId(), 'threadId' => $thread->getId(), 'slug' => $thread->getSlug()])]);
            $typeForm = 'thread';
        } else {
            $form = $this->createForm(MessageType::class, $message,  ['action' => $this->generateUrl('forum_edit', ['messageId' => $message->getId(), 'threadId' => $thread->getId(), 'slug' => $thread->getSlug()])]);
            $typeForm = 'message';
        }

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($message);
            $em->flush();

            $nbMessages = $em->createQueryBuilder()
                ->select('count(m.id)')
                ->from('EtuModuleForumBundle:Message', 'm')
                ->where('m.thread = :thread')
                ->andWhere('m.id <= :mid')
                ->setParameter('thread', $thread->getId())
                ->setParameter('mid', $message->getId())
                ->getQuery()
                ->getSingleScalarResult();

            $page = ceil($nbMessages / 10);

            return $this->redirect($this->generateUrl('forum_thread', array('id' => $thread->getId(), 'slug' => $thread->getSlug(), 'page' => $page)).'#'.$message->getId());
        }

        return array('messageContent' => $message, 'thread' => $thread, 'parents' => $parents, 'form' => $form->createView(), 'category' => $category, 'typeForm' => $typeForm);
    }

    /**
     * @Route("/forum/mod/{action}/{threadId}-{slug}/{messageId}", defaults={"messageId" = null}, requirements={"messageId" = "\d+"}, name="forum_mod")
     * @Template()
     */
    public function modAction($action, $threadId, $messageId, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_FORUM_POST');

        $em = $this->getDoctrine()->getManager();
        $thread = $em->createQueryBuilder()
            ->select('t, c')
            ->from('EtuModuleForumBundle:Thread', 't')
            ->leftJoin('t.category', 'c')
            ->where('t.id = :id')
            ->andWhere('t.state != 300')
            ->setParameter('id', $threadId)
            ->getQuery()
            ->getSingleResult();

        $category = $thread->getCategory();
        $categoryId = $category->getId();

        $parents = $em->createQueryBuilder()
            ->select('c')
            ->from('EtuModuleForumBundle:Category', 'c')
            ->where('c.left <= :left')
            ->andWhere('c.right >= :right')
            ->setParameter('left', $category->getLeft())
            ->setParameter('right', $category->getRight())
            ->orderBy('c.depth')
            ->getQuery()
            ->getResult();

        $return = array('thread' => $thread, 'parents' => $parents, 'action' => $action);

        switch ($action) {
            case 'remove':
                $checker = new PermissionsChecker($this->getUser());
                if (!$checker->canDelete($category) && !$this->isGranted('ROLE_FORUM_ADMIN')) {
                    $return = $this->createAccessDeniedResponse();
                }
                if ($messageId == null) {
                    $messages = $em->createQueryBuilder()
                        ->select('m')
                        ->from('EtuModuleForumBundle:Message', 'm')
                        ->where('m.thread = :thread')
                        ->setParameter('thread', $thread)
                        ->orderBy('m.createdAt')
                        ->getQuery()
                        ->getResult();
                    foreach ($messages as $message) {
                        $category->setCountMessages($category->getCountMessages() - 1);
                        $thread->setCountMessages($thread->getCountMessages() - 1);
                        $em->remove($message);
                    }
                    $category->setCountThreads($category->getCountThreads() - 1);
                    $em->remove($thread);
                }

                $message = $em->getRepository('EtuModuleForumBundle:Message')
                    ->find($messageId);
                if ($message->getCreatedAt() == $thread->getCreatedAt()) {
                    $messages = $em->createQueryBuilder()
                        ->select('m')
                        ->from('EtuModuleForumBundle:Message', 'm')
                        ->where('m.thread = :thread')
                        ->setParameter('thread', $thread)
                        ->orderBy('m.createdAt')
                        ->getQuery()
                        ->getResult();
                    foreach ($messages as $message) {
                        $category->setCountMessages($category->getCountMessages() - 1);
                        $thread->setCountMessages($thread->getCountMessages() - 1);
                        $em->remove($message);
                    }
                    $category->setCountThreads($category->getCountThreads() - 1);
                    $em->remove($thread);
                } else {
                    $thread->setCountMessages($thread->getCountMessages() - 1);
                    $category->setCountMessages($thread->getCountMessages() - 1);
                    $em->remove($message);
                }
                $em->flush();

                $category = $em->getRepository('EtuModuleForumBundle:Category')
                    ->find($categoryId);
                $thread = $em->getRepository('EtuModuleForumBundle:Category')
                    ->find($threadId);

                $getLastMessage = $em->createQueryBuilder()
                    ->select('m')
                    ->from('EtuModuleForumBundle:Message', 'm')
                    ->where('m.category = :category')
                    ->setParameter('category', $category)
                    ->orderBy('m.createdAt', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery();

                try {
                    $getLastMessage = $getLastMessage->getSingleResult();
                    $category->setLastMessage($getLastMessage);
                } catch (\Doctrine\Orm\NoResultException $e) {
                    $category->setLastMessage(null);
                }

                $getLastMessage = $em->createQueryBuilder()
                    ->select('m')
                    ->from('EtuModuleForumBundle:Message', 'm')
                    ->where('m.thread = :thread')
                    ->setParameter('thread', $thread)
                    ->orderBy('m.createdAt', 'DESC')
                    ->setMaxResults(1)
                    ->getQuery();
                try {
                    $getLastMessage = $getLastMessage->getSingleResult();
                    $thread->setLastMessage($getLastMessage);
                    $em->persist($thread);
                    $return = $this->redirect($this->generateUrl('forum_thread', array('id' => $thread->getId(), 'slug' => $thread->getSlug())));
                } catch (\Doctrine\Orm\NoResultException $e) {
                    $return = $this->redirect($this->generateUrl('forum_category', array('id' => $category->getId(), 'slug' => $category->getSlug())));
                }

                $em->persist($category);
                $em->flush();
                break;
            case 'lock':
                $thread = $em->getRepository('EtuModuleForumBundle:Thread')
                    ->find($threadId);
                $category = $thread->getCategory();

                $checker = new PermissionsChecker($this->getUser());
                if (!$checker->canLock($category) && !$this->isGranted('ROLE_FORUM_ADMIN')) {
                    return $this->createAccessDeniedResponse();
                }

                if ($thread->getState() == 200) {
                    $thread->setState(100);
                } else {
                    $thread->setState(200);
                }

                $return = $this->redirect($this->generateUrl('forum_thread', array(
                    'id' => $thread->getId(),
                    'slug' => $thread->getSlug(),
                )));
                break;
            case 'move':
                $thread = $em->getRepository('EtuModuleForumBundle:Thread')
                    ->find($threadId);
                $category = $thread->getCategory();

                $checker = new PermissionsChecker($this->getUser());
                if (!$checker->canMove($category) && !$this->isGranted('ROLE_FORUM_ADMIN')) {
                    $return = $this->createAccessDeniedResponse();
                }

                $c = $em->getRepository('EtuModuleForumBundle:Category');

                $form = $this->createFormBuilder($thread)
                    ->add('category', EntityType::class, array(
                        'class' => 'EtuModuleForumBundle:Category',
                        'query_builder' => function (EntityRepository $er) {
                            $categoriesList = array();
                            $categories = $er->createQueryBuilder('c')
                            ->orderBy('c.left')
                            ->getQuery()
                            ->getResult();

                            foreach ($categories as $category) {
                                $checker = new PermissionsChecker($this->getUser());
                                if ($checker->canRead($category) || $this->isGranted('ROLE_FORUM_ADMIN')) {
                                    array_push($categoriesList, $category);
                                }
                            }

                            $categories = $er->createQueryBuilder('c');
                            $categories->where('c.id = 0');
                            $i = 0;
                            foreach ($categoriesList as $category) {
                                $categories->orWhere('c.id = :cat'.$i);
                                $categories->setParameter('cat'.$i, $category->getId());
                                ++$i;
                            }
                            $categories->orderBy('c.left');

                            return $categories;
                        }, )
                    )
                    ->getForm();

                $return['form'] = $form->createView();

                $form->handleRequest($request);
                if ($form->isSubmitted() && $form->isValid()) {
                    $category->setCountThreads($category->getCountThreads() - 1)
                        ->setCountMessages($category->getCountMessages() - $thread->getCountMessages());

                    $newCat = $thread->getCategory();
                    $newCat->setCountThreads($newCat->getCountThreads() + 1)
                        ->setCountMessages($newCat->getCountMessages() + $thread->getCountMessages());

                    $thread->setCategory($newCat);

                    $modMessages = $em->createQueryBuilder()->update('EtuModuleForumBundle:Message', 'm')
                        ->set('m.category', ':newCat')
                        ->setParameter('newCat', $newCat)
                        ->where('m.thread = :thread')
                        ->setParameter('thread', $thread)
                        ->getQuery()
                        ->execute();
                    $em->persist($thread);

                    $getLastMessage = $em->createQueryBuilder()
                        ->select('m')
                        ->from('EtuModuleForumBundle:Message', 'm')
                        ->where('m.category = :category')
                        ->setParameter('category', $category)
                        ->orderBy('m.createdAt', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery();

                    try {
                        $getLastMessage = $getLastMessage->getSingleResult();
                        $category->setLastMessage($getLastMessage);
                    } catch (\Doctrine\Orm\NoResultException $e) {
                        $category->setLastMessage(null);
                    }
                    $em->persist($category);

                    $getLastMessage = $em->createQueryBuilder()
                        ->select('m')
                        ->from('EtuModuleForumBundle:Message', 'm')
                        ->where('m.category = :category')
                        ->setParameter('category', $newCat)
                        ->orderBy('m.createdAt', 'DESC')
                        ->setMaxResults(1)
                        ->getQuery();

                    try {
                        $getLastMessage = $getLastMessage->getSingleResult();
                        $newCat->setLastMessage($getLastMessage);
                    } catch (\Doctrine\Orm\NoResultException $e) {
                        $newCat->setLastMessage(null);
                    }
                    $em->persist($newCat);

                    $em->flush();
                    $return = $this->redirect($this->generateUrl('forum_thread', array('id' => $thread->getId(), 'slug' => $thread->getSlug())));
                }
        }

        return $return;
    }

    private function giveBadges()
    {
        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $threads = $em->createQueryBuilder()
            ->select('t')
            ->from('EtuModuleForumBundle:Thread', 't')
            ->where('t.author = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $nbThreads = count($threads);

        if ($nbThreads >= 1) {
            BadgesManager::userAddBadge($user, 'mysterion', 1);
            BadgesManager::userPersistBadges($user);
        }
        if ($nbThreads >= 10) {
            BadgesManager::userAddBadge($user, 'mysterion', 2);
            BadgesManager::userPersistBadges($user);
        }
        if ($nbThreads >= 20) {
            BadgesManager::userAddBadge($user, 'mysterion', 3);
            BadgesManager::userPersistBadges($user);
        }
        if ($nbThreads >= 40) {
            BadgesManager::userAddBadge($user, 'mysterion', 4);
            BadgesManager::userPersistBadges($user);
        }

        $messages = $em->createQueryBuilder()
            ->select('m')
            ->from('EtuModuleForumBundle:Message', 'm')
            ->where('m.author = :user')
            ->setParameter('user', $user)
            ->getQuery()
            ->getResult();

        $nbMessages = count($messages);

        if ($nbMessages >= 1) {
            BadgesManager::userAddBadge($user, 'monkey', 1);
            BadgesManager::userPersistBadges($user);
        }
        if ($nbMessages >= 20) {
            BadgesManager::userAddBadge($user, 'monkey', 2);
            BadgesManager::userPersistBadges($user);
        }
        if ($nbMessages >= 50) {
            BadgesManager::userAddBadge($user, 'monkey', 3);
            BadgesManager::userPersistBadges($user);
        }
        if ($nbMessages >= 100) {
            BadgesManager::userAddBadge($user, 'monkey', 4);
            BadgesManager::userPersistBadges($user);
        }
        if ($nbMessages >= 500) {
            BadgesManager::userAddBadge($user, 'monkey', 5);
            BadgesManager::userPersistBadges($user);
        }
    }
}

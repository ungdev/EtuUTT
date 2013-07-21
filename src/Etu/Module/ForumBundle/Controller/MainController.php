<?php

namespace Etu\Module\ForumBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;

use Etu\Module\ForumBundle\Entity\Category;
use Etu\Module\ForumBundle\Entity\Thread;

use Etu\Module\ForumBundle\Form\ThreadType;
use Etu\Module\ForumBundle\Form\MessageType;

use Etu\Module\ForumBundle\Model\PermissionsChecker;

// Import annotations
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
		$em = $this->getDoctrine()->getManager();
		$categories = $em->createQueryBuilder()
			->select('c')
			->from('EtuModuleForumBundle:Category', 'c')
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
		$em = $this->getDoctrine()->getManager();
		$category = $em->getRepository('EtuModuleForumBundle:Category')
			->find($id);

		$checker = new PermissionsChecker($this->getUser());
		if (!$checker->canRead($category)) {
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

		$subCategories = $em->createQueryBuilder()
			->select('c')
			->from('EtuModuleForumBundle:Category', 'c')
			->where('c.left > :left')
			->andWhere('c.right < :right')
			->setParameter('left', $category->getLeft())
			->setParameter('right', $category->getRight())
			->orderBy('c.depth')
			->getQuery()
			->getResult();

		$threads = $em->createQueryBuilder()
			->select('t, m')
			->from('EtuModuleForumBundle:Thread', 't')
			->leftJoin('t.lastMessage', 'm')
			->where('t.category = :category')
			->andWhere('t.state != 300')
			->setParameter('category', $category)
			->orderBy('t.weight', 'DESC')
			->addOrderBy('m.createdAt', 'DESC')
			->getQuery()
			->getResult();

		$threads = $this->get('knp_paginator')->paginate($threads, $page, 15);
		
		return array('category' => $category, 'subCategories' => $subCategories, 'parents' => $parents, 'threads' => $threads);
	}

	/**
	 * @Route("/forum/thread/{id}-{slug}/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="forum_thread")
	 * @Template()
	 */
	public function threadAction($id, $slug, $page)
	{
		return array();
	}

	/**
	 * @Route("/forum/post/{id}-{slug}", name="forum_post")
	 * @Template()
	 */
	public function postAction($id, $slug)
	{
		$em = $this->getDoctrine()->getManager();
		$category = $em->getRepository('EtuModuleForumBundle:Category')
			->find($id);

		$checker = new PermissionsChecker($this->getUser());
		if (!$checker->canPost($category)) {
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
		$form = $this->createForm(new ThreadType, $thread);

		$request = $this->get('request');
		if ($request->getMethod() == 'POST') {
			$form->bind($request);
			if ($form->isValid()) {
				if($thread->getWeight() != 100 && !$checker->canSticky($category)) $thread->setWeight(100);
				$thread->setAuthor($this->getUser())
					->setCategory($category)
					->setCountMessages(1)
					->setSlug(StringManipulationExtension::slugify($thread->getTitle()));
				$message = $thread->getLastMessage();
				$message->setAuthor($this->getUser())
					->setCategory($category)
					->setThread($thread)
					->setState(100);
				$thread->setLastMessage($message);
				$category->setCountMessages($category->getCountMessages()+1)
					->setCountThreads($category->getCountThreads()+1);
				foreach($parents as $parent)
				{
					$parent->setLastMessage($message);
					$em->persist($parent);
				}
				$em->persist($thread);
				$em->persist($category);
				$em->flush();

				return $this->redirect($this->generateUrl('forum_thread', array('id' => $thread->getId(), 'slug' => $thread->getSlug())));
			}
			else return array('errors' => $form->getErrors(), 'category' => $category, 'parents' => $parents, 'form' => $form->createView());
		}

		return array('category' => $category, 'parents' => $parents, 'form' => $form->createView());
	}
}

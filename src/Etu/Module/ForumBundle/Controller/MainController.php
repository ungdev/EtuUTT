<?php

namespace Etu\Module\ForumBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Module\ForumBundle\Entity\Category;
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
		
		return array('category' => $category, 'parents' => $parents, 'threads' => $threads);
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
		return array();
	}
}

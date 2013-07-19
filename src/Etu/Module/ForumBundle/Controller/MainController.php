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
	 * @Route("/forum/{id}-{slug}", name="forum_category")
	 * @Template()
	 */
	public function categoryAction($id, $slug)
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
			->where('c.left <= :left AND c.right >= :right')
			->setParameter('left', $category->getLeft())
			->setParameter('right', $category->getRight())
			->orderBy('c.depth')
			->getQuery()
			->getResult();
		
		return array('category' => $category, 'parents' => $parents);
	}
}

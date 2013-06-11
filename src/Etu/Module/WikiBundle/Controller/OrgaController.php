<?php

namespace Etu\Module\WikiBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Util\RedactorJsEscaper;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Module\WikiBundle\Entity\Category;
use Etu\Module\WikiBundle\Entity\Page;
use Etu\Module\WikiBundle\Entity\PageRevision;
use Etu\Module\WikiBundle\Model\NestedPagesTree;
use Etu\Module\WikiBundle\Model\PermissionsChecker;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;


/**
 * Organization wiki controller
 */
class OrgaController extends Controller
{
	protected $removedCategories = array();

	/**
	 * @Route("/wiki/{login}", name="wiki_orga_index")
	 * @Template()
	 */
	public function indexAction($login)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.isHome = 1')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			/** @var $orga Organization */
			$orga = $em->getRepository('EtuUserBundle:Organization')->findOneByLogin($login);

			$home = new Page();
			$home->setTitle('Accueil')
				->setOrga($orga)
				->setIsHome(true)
				->setLevelToView(Page::LEVEL_CONNECTED)
				->setLevelToEdit(Page::LEVEL_ASSO_ADMIN)
				->setLevelToEditPermissions(Page::LEVEL_ASSO_ADMIN);

			$em->persist($home);
			$em->flush();

			$revision = new PageRevision();
			$revision->setBody('Bienvenue sur le wiki associatif de '.$orga->getName())
				->setComment('Création automatique')
				->setPageId($home->getId());

			$home->setRevision($revision);

			$em->persist($revision);
			$em->persist($home);
			$em->flush();
		}

		$tree = $this->createNestedTreeFor($home->getOrga());

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route("/wiki/{login}/edit", name="wiki_orga_edit")
	 * @Template()
	 */
	public function editAction($login)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.isHome = 1')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			throw $this->createNotFoundException('Page not found');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canEdit($home)) {
			return $this->createAccessDeniedResponse();
		}

		$revisions = $em->createQueryBuilder()
			->select('r, u')
			->from('EtuModuleWikiBundle:PageRevision', 'r')
			->leftJoin('r.user', 'u')
			->where('r.page = :page')
			->setParameter('page', $home->getId())
			->orderBy('r.date', 'DESC')
			->setMaxResults(30)
			->getQuery()
			->getResult();

		$page = new \stdClass();
		$page->body = $home->getRevision()->getBody();
		$page->comment = '';

		$form = $this->createFormBuilder($page)
			->add('body', 'redactor')
			->add('comment', 'text')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			if (RedactorJsEscaper::escape($page->body) != $home->getRevision()->getBody()) {
				$revision = $home->createRevision();
				$revision->setBody(RedactorJsEscaper::escape($page->body));
				$revision->setComment($page->comment);

				$home->setRevision($revision);

				$em->persist($revision);
				$em->persist($home);
				$em->flush();
			}

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.orga.edit.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_orga_index', array('login' => $login)));
		}

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'form' => $form->createView(),
			'revisions' => $revisions
		);
	}

	/**
	 * @Route(
	 *      "/wiki/{login}/revision/{id}/{confirm}",
	 *      defaults={"confirm"=false},
	 *      name="wiki_orga_revision"
	 * )
	 * @Template()
	 */
	public function revisionAction($login, $id, $confirm = false)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.isHome = 1')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			throw $this->createNotFoundException('Page not found');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canEdit($home)) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $revisions PageRevision[] */
		$revisions = $em->createQueryBuilder()
			->select('r, u')
			->from('EtuModuleWikiBundle:PageRevision', 'r')
			->leftJoin('r.user', 'u')
			->where('r.page = :page')
			->setParameter('page', $home->getId())
			->orderBy('r.date', 'DESC')
			->setMaxResults(30)
			->getQuery()
			->getResult();

		$currentRevision = null;

		foreach ($revisions as $revision) {
			if ($revision->getId() == $id) {
				$currentRevision = $revision;
			}
		}

		if (! $currentRevision instanceof PageRevision) {
			throw $this->createNotFoundException('Revision not found');
		}

		if ($confirm !== false) {
			$home->setRevision($currentRevision);
			$em->persist($home);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.orga.revision.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_orga_index', array('login' => $login)));
		}

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'currentRevision' => $currentRevision,
			'revisions' => $revisions
		);
	}

	/**
	 * @Route("/wiki/{login}/permissions", name="wiki_orga_permissions")
	 * @Template()
	 */
	public function permissionsAction($login)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.isHome = 1')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			throw $this->createNotFoundException('Page not found');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canEditPermissions($home)) {
			return $this->createAccessDeniedResponse();
		}

		$levels = array(
			Page::LEVEL_CONNECTED => 'wiki.levels.connected',
			Page::LEVEL_ASSO_MEMBER => 'wiki.levels.asso_member',
			Page::LEVEL_ASSO_ADMIN => 'wiki.levels.asso_admin',
			Page::LEVEL_ADMIN => 'wiki.levels.admin'
		);

		$form = $this->createFormBuilder($home)
			->add('levelToEdit', 'choice', array('choices' => $levels))
			->add('levelToEditPermissions', 'choice', array('choices' => $levels))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em->persist($home);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.orga.permissions.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_orga_index', array('login' => $login)));
		}

		$tree = $this->createNestedTreeFor($home->getOrga());

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'form' => $form->createView(),
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route("/wiki/{login}/tree", name="wiki_orga_tree")
	 * @Template()
	 */
	public function treeAction($login)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.isHome = 1')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			throw $this->createNotFoundException('Page not found');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canDelete($home)) {
			return $this->createAccessDeniedResponse();
		}

		$tree = $this->createNestedTreeFor($home->getOrga());

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route("/wiki/{login}/category/{id}/edit", name="wiki_orga_edit_category")
	 * @Template()
	 *
	 * @todo Test
	 */
	public function editCategoryAction($login, $id)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.isHome = 1')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			throw $this->createNotFoundException('Home not found');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canCreate($home)) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $categories Category[] */
		$categories = $em->createQueryBuilder()
			->select('c, cp')
			->from('EtuModuleWikiBundle:Category', 'c')
			->leftJoin('c.parent', 'cp')
			->where('c.orga = :orga')
			->andWhere('c.depth <= 3')
			->setParameter('orga', $home->getOrga()->getId())
			->orderBy('c.depth', 'ASC')
			->addOrderBy('c.title', 'ASC')
			->getQuery()
			->getResult();

		$choices = array(0 => 'A la racine');

		foreach ($categories as $key => $category) {
			$choices[$category->getId()] = $category->getTitle();
			unset($categories[$key]);
			$categories[$category->getId()] = $category;
		}

		if (! isset($categories[$id])) {
			throw $this->createNotFoundException('Category not found');
		}

		$levels = array(
			Page::LEVEL_CONNECTED => 'wiki.levels.connected',
			Page::LEVEL_ASSO_MEMBER => 'wiki.levels.asso_member',
			Page::LEVEL_ASSO_ADMIN => 'wiki.levels.asso_admin',
			Page::LEVEL_ADMIN => 'wiki.levels.admin'
		);

		$category = $categories[$id];
		$category->parentId = $category->getParent()->getId();

		$form = $this->createFormBuilder($category)
			->add('title')
			->add('parentId', 'choice', array('choices' => $choices))
			->add('levelToView', 'choice', array('choices' => $levels))
			->add('levelToEdit', 'choice', array('choices' => $levels))
			->add('levelToEditPermissions', 'choice', array('choices' => $levels))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			if ($category->parentId > 0 && isset($categories[$category->parentId])) {
				$category->setParent($categories[$category->parentId]);
				$category->setDepth($categories[$category->parentId]->getDepth() + 1);
			} elseif ($category->parentId == 0) {
				$category->removeParent();
				$category->setDepth(0);
			}

			$em->persist($category);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.orga.edit_category.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_orga_tree', array('login' => $login)));
		}

		$tree = $this->createNestedTreeFor($home->getOrga());

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'form' => $form->createView(),
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route("/wiki/{login}/category/{id}/remove/{confirm}", name="wiki_orga_remove_category")
	 * @Template()
	 *
	 * @todo Test
	 */
	public function removeCategoryAction($login, $id, $confirm = false)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.isHome = 1')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			throw $this->createNotFoundException('Home not found');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canCreate($home)) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $categories Category[] */
		$category = $em->createQueryBuilder()
			->select('c, cp')
			->from('EtuModuleWikiBundle:Category', 'c')
			->leftJoin('c.parent', 'cp')
			->where('c.id = :id')
			->setParameter('id', $id)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $category) {
			throw $this->createNotFoundException('Category not found');
		}

		if ($confirm !== false) {
			/** @var $categories Category[] */
			$categories = $em->createQueryBuilder()
				->select('c, cp')
				->from('EtuModuleWikiBundle:Category', 'c')
				->leftJoin('c.parent', 'cp')
				->where('c.orga = :orga')
				->andWhere('c.depth > :depth')
				->setParameter('orga', $home->getOrga()->getId())
				->setParameter('depth', $category->getDepth())
				->orderBy('c.depth', 'DESC')
				->getQuery()
				->getResult();

			$this->removeTreePart($category->getId(), $categories);
			$this->removedCategories[] = $category->getId();

			// Delete pages first
			$query = $em->createQueryBuilder()
				->delete()
				->from('EtuModuleWikiBundle:Page', 'p');

			foreach ($this->removedCategories as $key => $categoryId) {
				$query->orWhere('p.category = :categoryId'.$key)
					->setParameter(':categoryId'.$key, $categoryId);
			}

			$query->getQuery()->execute();

			// Delete categories then
			foreach ($this->removedCategories as $categoryId) {
				$em->createQueryBuilder()
					->delete()
					->from('EtuModuleWikiBundle:Category', 'c')
					->where('c.id = :id')
					->setParameter(':id', $categoryId)
					->getQuery()
					->execute();
			}

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.orga.delete_category.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_orga_tree', array('login' => $login)));
		}

		$tree = $this->createNestedTreeFor($home->getOrga());

		return array(
			'page' => $home,
			'category' => $category,
			'orga' => $home->getOrga(),
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * Create the pages and categories tree for a given organization
	 *
	 * @param Organization $orga
	 * @return NestedPagesTree
	 */
	protected function createNestedTreeFor(Organization $orga)
	{
		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $pages Page[] */
		$pages = $em->createQueryBuilder()
			->select('p, c, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.category', 'c')
			->leftJoin('p.revision', 'r')
			->where('p.orga = :orga')
			->andWhere('p.isHome = 0')
			->andWhere('p.isDeleted = 0')
			->setParameter('orga', $orga->getId())
			->orderBy('c.depth', 'ASC')
			->addOrderBy('c.title', 'ASC')
			->orderBy('p.title', 'ASC')
			->getQuery()
			->getResult();

		/** @var $categories Category[] */
		$categories = $em->createQueryBuilder()
			->select('c, cp')
			->from('EtuModuleWikiBundle:Category', 'c')
			->leftJoin('c.parent', 'cp')
			->where('c.orga = :orga')
			->setParameter('orga', $orga->getId())
			->orderBy('c.depth', 'ASC')
			->addOrderBy('c.title', 'ASC')
			->getQuery()
			->getResult();

		return new NestedPagesTree($pages, $categories);
	}

	/**
	 * @param integer $parentId
	 * @param Category[] $categories
	 */
	protected function removeTreePart($parentId, $categories)
	{
		foreach ($categories as $category) {
			if ($category->getParent()->getId() == $parentId) {
				$this->removeTreePart($category->getId(), $categories);
				array_unshift($this->removedCategories, $category->getId());
			}
		}
	}
}

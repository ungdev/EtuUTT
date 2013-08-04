<?php

namespace Etu\Module\WikiBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
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
 * Pages wiki controller
 */
class PageController extends Controller
{
	/**
	 * @Route("/wiki/{login}/create", name="wiki_page_create")
	 * @Template()
	 */
	public function createAction($login)
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
			->andWhere('c.depth <= 4')
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

		$levels = array(
			Page::LEVEL_CONNECTED => 'wiki.levels.connected',
			Page::LEVEL_ASSO_MEMBER => 'wiki.levels.asso_member',
			Page::LEVEL_ASSO_ADMIN => 'wiki.levels.asso_admin',
			Page::LEVEL_ADMIN => 'wiki.levels.admin'
		);

		$page = new Page();
		$page->setOrga($home->getOrga());

		$form = $this->createFormBuilder($page)
			->add('title')
			->add('body', 'redactor')
			->add('parent', 'choice', array('choices' => $choices))
			->add('levelToView', 'choice', array('choices' => $levels))
			->add('levelToEdit', 'choice', array('choices' => $levels))
			->add('levelToEditPermissions', 'choice', array('choices' => $levels))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			if ($page->parent > 0 && isset($categories[$page->parent])) {
				$page->setCategory($categories[$page->parent]);
			}

			$revision = $page->createRevision();
			$revision->setBody($page->body);
			$revision->setComment('Création');
			$revision->setUser($this->getUser());

			$page->setRevision($revision);

			$em->persist($revision);
			$em->persist($page);
			$em->flush();

			$revision->setPageId($page->getId());

			$em->persist($revision);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.create.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_page_view', array(
				'login' => $login,
				'id' => $page->getId(),
				'slug' => StringManipulationExtension::slugify($page->getTitle())
			)));
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
	 * @Route("/wiki/{login}/create-category", name="wiki_page_create_category")
	 * @Template()
	 */
	public function createCategoryAction($login)
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

		$levels = array(
			Page::LEVEL_CONNECTED => 'wiki.levels.connected',
			Page::LEVEL_ASSO_MEMBER => 'wiki.levels.asso_member',
			Page::LEVEL_ASSO_ADMIN => 'wiki.levels.asso_admin',
			Page::LEVEL_ADMIN => 'wiki.levels.admin'
		);

		$category = new Category();
		$category->setOrga($home->getOrga());
		$category->setDepth(0);

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
			}

			$em->persist($category);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.create_category.confirm'
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
	 * @Route("/wiki/{login}/{id}-{slug}/edit", name="wiki_page_edit")
	 * @Template()
	 */
	public function editAction($login, $id, $slug)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $page Page */
		$page = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.id = :id')
			->andWhere('o.login = :login')
			->setParameter('id', $id)
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $page) {
			throw $this->createNotFoundException('Page not found');
		}

		if (! $page->getRevision()) {
			throw $this->createNotFoundException('Revision not found');
		}

		if (StringManipulationExtension::slugify($page->getTitle()) != $slug) {
			throw $this->createNotFoundException('Invalid slug');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canEdit($page)) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $categories Category[] */
		$categories = $em->createQueryBuilder()
			->select('c, cp')
			->from('EtuModuleWikiBundle:Category', 'c')
			->leftJoin('c.parent', 'cp')
			->where('c.orga = :orga')
			->setParameter('orga', $page->getOrga()->getId())
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

		$page->body = $page->getRevision()->getBody();
		$page->parent = $page->getCategory() ? $page->getCategory()->getId() : 0;
		$page->comment = null;

		$form = $this->createFormBuilder($page)
			->add('title')
			->add('body', 'redactor')
			->add('comment', 'text', array('required' => false))
			->add('parent', 'choice', array('choices' => $choices))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			if ($page->parent > 0 && isset($categories[$page->parent])) {
				$page->setCategory($categories[$page->parent]);
			} elseif($page->parent == 0) {
				$page->putToRoot();
			}

			if ($page->body != $page->getRevision()->getBody()) {
				$revision = $page->createRevision();
				$revision->setBody($page->body);
				$revision->setComment($page->comment);
				$revision->setUser($this->getUser());

				$page->setRevision($revision);

				$em->persist($revision);
			}

			$em->persist($page);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.edit.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_page_view', array(
				'login' => $login,
				'id' => $page->getId(),
				'slug' => StringManipulationExtension::slugify($page->getTitle())
			)));
		}

		$revisions = $em->createQueryBuilder()
			->select('r, u')
			->from('EtuModuleWikiBundle:PageRevision', 'r')
			->leftJoin('r.user', 'u')
			->where('r.page = :page')
			->setParameter('page', $page->getId())
			->orderBy('r.createdAt', 'DESC')
			->setMaxResults(30)
			->getQuery()
			->getResult();

		return array(
			'page' => $page,
			'orga' => $page->getOrga(),
			'form' => $form->createView(),
			'revisions' => $revisions
		);
	}

	/**
	 * @Route(
	 *      "/wiki/{login}/{id}-{slug}/revision/{revisionId}/{confirm}",
	 *      defaults={"confirm"=false},
	 *      name="wiki_page_revision"
	 * )
	 * @Template()
	 */
	public function revisionAction($login, $id, $slug, $revisionId, $confirm = false)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $page Page */
		$page = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.id = :id')
			->andWhere('o.login = :login')
			->setParameter('id', $id)
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $page) {
			throw $this->createNotFoundException('Page not found');
		}

		if (StringManipulationExtension::slugify($page->getTitle()) != $slug) {
			throw $this->createNotFoundException('Invalid slug');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canEdit($page)) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $revisions PageRevision[] */
		$revisions = $em->createQueryBuilder()
			->select('r, u')
			->from('EtuModuleWikiBundle:PageRevision', 'r')
			->leftJoin('r.user', 'u')
			->where('r.page = :page')
			->setParameter('page', $page->getId())
			->orderBy('r.createdAt', 'DESC')
			->setMaxResults(30)
			->getQuery()
			->getResult();

		$revision = null;

		foreach ($revisions as $r) {
			if ($r->getId() == $revisionId) {
				$revision = $r;
			}
		}

		if (! $revision) {
			throw $this->createNotFoundException('Revision not found');
		}

		if ($confirm !== false) {
			$page->setRevision($revision);
			$em->persist($page);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.revision.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_page_view', array(
				'login' => $login,
				'id' => $page->getId(),
				'slug' => StringManipulationExtension::slugify($page->getTitle())
			)));
		}

		$tree = $this->createNestedTreeFor($page->getOrga());

		return array(
			'page' => $page,
			'orga' => $page->getOrga(),
			'revisions' => $revisions,
			'currentRevision' => $revision,
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route("/wiki/{login}/{id}-{slug}/permissions", name="wiki_page_permissions")
	 * @Template()
	 */
	public function permissionsAction($login, $id, $slug)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $page Page */
		$page = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.id = :id')
			->andWhere('o.login = :login')
			->setParameter('id', $id)
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $page) {
			throw $this->createNotFoundException('Page not found');
		}

		if (! $page->getRevision()) {
			throw $this->createNotFoundException('Revision not found');
		}

		if (StringManipulationExtension::slugify($page->getTitle()) != $slug) {
			throw $this->createNotFoundException('Invalid slug');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canEdit($page)) {
			return $this->createAccessDeniedResponse();
		}

		$levels = array(
			Page::LEVEL_CONNECTED => 'wiki.levels.connected',
			Page::LEVEL_ASSO_MEMBER => 'wiki.levels.asso_member',
			Page::LEVEL_ASSO_ADMIN => 'wiki.levels.asso_admin',
			Page::LEVEL_ADMIN => 'wiki.levels.admin'
		);

		$form = $this->createFormBuilder($page)
			->add('levelToView', 'choice', array('choices' => $levels))
			->add('levelToEdit', 'choice', array('choices' => $levels))
			->add('levelToEditPermissions', 'choice', array('choices' => $levels))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em->persist($page);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.permissions.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_page_view', array(
				'login' => $login,
				'id' => $page->getId(),
				'slug' => StringManipulationExtension::slugify($page->getTitle())
			)));
		}

		$tree = $this->createNestedTreeFor($page->getOrga());

		return array(
			'page' => $page,
			'orga' => $page->getOrga(),
			'form' => $form->createView(),
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route(
	 *      "/wiki/{login}/{id}-{slug}/delete/{confirm}",
	 *      defaults={"confirm"=false},
	 *      name="wiki_page_delete"
	 * )
	 * @Template()
	 */
	public function deleteAction($login, $id, $slug, $confirm = false)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $page Page */
		$page = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.id = :id')
			->andWhere('o.login = :login')
			->setParameter('id', $id)
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $page) {
			throw $this->createNotFoundException('Page not found');
		}

		if (! $page->getRevision()) {
			throw $this->createNotFoundException('Revision not found');
		}

		if (StringManipulationExtension::slugify($page->getTitle()) != $slug) {
			throw $this->createNotFoundException('Invalid slug');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canDelete($page)) {
			return $this->createAccessDeniedResponse();
		}

		if ($confirm !== false) {
			$em->remove($page);
			$em->flush();

			$em->createQueryBuilder()
				->delete()
				->from('EtuModuleWikiBundle:PageRevision', 'pr')
				->where('pr.page = :pageId')
				->setParameter('pageId', $page->getId())
				->getQuery()
				->execute();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.delete.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_orga_index', array('login' => $login)));
		}

		$tree = $this->createNestedTreeFor($page->getOrga());

		return array(
			'page' => $page,
			'orga' => $page->getOrga(),
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route("/wiki/{login}/{id}-{slug}", name="wiki_page_view")
	 * @Template()
	 */
	public function viewAction($login, $id, $slug)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $page Page */
		$page = $em->createQueryBuilder()
			->select('p, r, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->leftJoin('p.orga', 'o')
			->where('p.id = :id')
			->andWhere('o.login = :login')
			->setParameter('id', $id)
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $page) {
			throw $this->createNotFoundException('Page not found');
		}

		if (! $page->getRevision()) {
			throw $this->createNotFoundException('Revision not found');
		}

		if (StringManipulationExtension::slugify($page->getTitle()) != $slug) {
			throw $this->createNotFoundException('Invalid slug');
		}

		$checker = new PermissionsChecker($this->getUser());

		if (! $checker->canView($page)) {
			return $this->createAccessDeniedResponse();
		}

		$tree = $this->createNestedTreeFor($page->getOrga());

		return array(
			'page' => $page,
			'orga' => $page->getOrga(),
			'tree' => $tree->getNestedTree(),
			'breadcrumb' => $tree->getBreadcrumbFor($page),
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
			->select('p, c, cp')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.category', 'c')
			->leftJoin('c.parent', 'cp')
			->where('p.orga = :orga')
			->andWhere('p.isHome = 0')
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
}

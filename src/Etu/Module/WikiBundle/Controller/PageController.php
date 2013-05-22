<?php

namespace Etu\Module\WikiBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;

use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\CoreBundle\Util\RedactorJsEscaper;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Module\WikiBundle\Entity\Page;

// Import annotations
use Etu\Module\WikiBundle\Entity\PageRevision;
use Etu\Module\WikiBundle\Model\NestedPagesTree;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class PageController extends Controller
{
	/**
	 * @Route("/wiki/{login}/{id}-{slug}", name="wiki_page")
	 * @Template()
	 */
	public function pageAction($login, $id, $slug)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $page Page */
		$page = $em->createQueryBuilder()
			->select('p, o, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->leftJoin('p.revision', 'r')
			->where('o.login = :login')
			->andWhere('p.id = :id')
			->setParameter('login', $login)
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, o, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->leftJoin('p.revision', 'r')
			->where('o.login = :login')
			->andWhere('p.isHome = 1')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $page) {
			throw $this->createNotFoundException(sprintf('Page not found'));
		}

		if (StringManipulationExtension::slugify($page->getTitle()) != $slug) {
			throw $this->createNotFoundException(sprintf('Invalid slug'));
		}

		/** @var $pages Page[] */
		$pages = $em->createQueryBuilder()
			->select('p, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->orderBy('p.left', 'ASC')
			->getQuery()
			->getResult();

		$tree = new NestedPagesTree($pages);

		return array(
			'page' => $page,
			'home' => $home,
			'orga' => $page->getOrga(),
			'tree' => $tree->getNestedTree(),
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
			->select('p, o, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->leftJoin('p.revision', 'r')
			->where('o.login = :login')
			->andWhere('p.id = :id')
			->setParameter('login', $login)
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();

		if (! $page) {
			throw $this->createNotFoundException(sprintf('Page not found'));
		}

		if (StringManipulationExtension::slugify($page->getTitle()) != $slug) {
			throw $this->createNotFoundException(sprintf('Invalid slug'));
		}

		$revisions = $em->createQueryBuilder()
			->select('r, u')
			->from('EtuModuleWikiBundle:PageRevision', 'r')
			->leftJoin('r.user', 'u')
			->where('r.page = :page')
			->setParameter('page', $page->getId())
			->orderBy('r.date', 'DESC')
			->setMaxResults(30)
			->getQuery()
			->getResult();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, o, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->leftJoin('p.revision', 'r')
			->where('o.login = :login')
			->andWhere('p.isHome = 1')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		/** @var $pages Page[] */
		$pages = $em->createQueryBuilder()
			->select('p, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->orderBy('p.left', 'ASC')
			->getQuery()
			->getResult();

		$categories = array(0 => 'A la racine');
		$parents = array();
		$maxRight = 0;

		foreach ($pages as $p) {
			if ($p->getIsCategory()) {
				$categories[$p->getId()] = $p->getTitle();
				$parents[$p->getId()] = $p;
			}

			if ($p->getRight() > $maxRight) {
				$maxRight = $p->getRight();
			}
		}

		$revision = $page->createRevision();
		$revision->setBody($page->getRevision()->getBody())
			->setUser($this->getUser())
			->title = $page->getTitle();
		$revision->category = null;

		$form = $this->createFormBuilder($revision)
			->add('title', 'text')
			->add('body', 'redactor')
			->add('comment')
			->add('category', 'choice', array('choices' => $categories))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {

			$parent = $revision->category;

			$em->createQueryBuilder()
				->update('EtuModuleWikiBundle:Page', 'p')
				->set('p.left', 'p.left - 2')
				->where('p.left > :left')
				->setParameter('left', $page->getLeft())
				->getQuery()
				->execute();

			$em->createQueryBuilder()
				->update('EtuModuleWikiBundle:Page', 'p')
				->set('p.right', 'p.right - 2')
				->where('p.right > :right')
				->setParameter('right', $page->getRight())
				->getQuery()
				->execute();

			if ($parent == 0) {
				$page->setDepth(0)
					->setLeft($maxRight - 1)
					->setRight($maxRight);
			} else {
				/** @var $parent Page */
				$parent = $em->getRepository('EtuModuleWikiBundle:Page')->find($parents[$parent]->getId());

				$em->createQueryBuilder()
					->update('EtuModuleWikiBundle:Page', 'p')
					->set('p.right', 'p.right + 2')
					->where('p.right > :right')
					->setParameter('right', $parent->getLeft())
					->getQuery()
					->execute();

				$em->createQueryBuilder()
					->update('EtuModuleWikiBundle:Page', 'p')
					->set('p.left', 'p.left + 2')
					->where('p.left > :left')
					->setParameter('left', $parent->getLeft())
					->getQuery()
					->execute();

				$page->setDepth($parent->getDepth() + 1)
					->setLeft($parent->getLeft() + 1)
					->setRight($parent->getLeft() + 2);
			}

			if (RedactorJsEscaper::escape($revision->getBody()) != $page->getRevision()->getBody()) {
				$page->setRevision($revision);
				$revision->setBody(RedactorJsEscaper::escape($revision->getBody()));

				$em->persist($revision);
			}

			$em->persist($page);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.edit.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_page', array(
				'login' => $login,
				'id' => $id,
				'slug' => $slug
			)));
		}

		return array(
			'page' => $page,
			'home' => $home,
			'orga' => $page->getOrga(),
			'form' => $form->createView(),
			'revisions' => $revisions,
		);
	}

	/**
	 * @Route("/wiki/orga/{login}/revision/{id}/{ready}", defaults={"ready"=false}, name="wiki_page_revision")
	 * @Template()
	 */
	public function revisionAction($login, $id, $ready = false)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $revision PageRevision */
		$revision = $em->createQueryBuilder()
			->select('r')
			->from('EtuModuleWikiBundle:PageRevision', 'r')
			->where('r.id = :id')
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();

		if (! $revision) {
			throw $this->createNotFoundException(sprintf('Revision not found'));
		}

		/** @var $page Page */
		$page = $em->createQueryBuilder()
			->select('p, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->where('p.id = :id')
			->andWhere('o.login = :login')
			->setParameter('id', $revision->getPageId())
			->setParameter('login', $login)
			->getQuery()
			->getOneOrNullResult();

		if (! $page) {
			throw $this->createNotFoundException(sprintf('Page not found'));
		}

		if ($ready !== false) {
			$page->setRevision($revision);

			$em->persist($page);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.revision.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_page', array(
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
			->orderBy('r.date', 'DESC')
			->setMaxResults(30)
			->getQuery()
			->getResult();

		/** @var $pages Page[] */
		$pages = $em->createQueryBuilder()
			->select('p, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->orderBy('p.left', 'ASC')
			->getQuery()
			->getResult();

		$tree = new NestedPagesTree($pages);

		return array(
			'page' => $page,
			'currentRevision' => $revision,
			'revisions' => $revisions,
			'orga' => $page->getOrga(),
			'tree' => $tree->getNestedTree(),
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
			->select('p, o, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->leftJoin('p.revision', 'r')
			->where('o.login = :login')
			->andWhere('p.id = :id')
			->setParameter('login', $login)
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, o, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->leftJoin('p.revision', 'r')
			->where('o.login = :login')
			->andWhere('p.isHome = 1')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $page) {
			throw $this->createNotFoundException(sprintf('Page not found'));
		}

		if (StringManipulationExtension::slugify($page->getTitle()) != $slug) {
			throw $this->createNotFoundException(sprintf('Invalid slug'));
		}

		if ($confirm !== false) {
			$em->remove($page);

			$em->createQueryBuilder()
				->update('EtuModuleWikiBundle:Page', 'p')
				->set('p.left', 'p.left - 2')
				->where('p.left > :left')
				->setParameter('left', $page->getLeft())
				->getQuery()
				->execute();

			$em->createQueryBuilder()
				->update('EtuModuleWikiBundle:Page', 'p')
				->set('p.right', 'p.right - 2')
				->where('p.right >= :right')
				->setParameter('right', $page->getRight())
				->getQuery()
				->execute();

			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.delete.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_index_orga', array('login' => $login)));
		}

		/** @var $pages Page[] */
		$pages = $em->createQueryBuilder()
			->select('p, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->orderBy('p.left', 'ASC')
			->getQuery()
			->getResult();

		$tree = new NestedPagesTree($pages);

		return array(
			'page' => $page,
			'home' => $home,
			'orga' => $page->getOrga(),
			'tree' => $tree->getNestedTree(),
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
			->select('p, o, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->leftJoin('p.revision', 'r')
			->where('o.login = :login')
			->andWhere('p.id = :id')
			->setParameter('login', $login)
			->setParameter('id', $id)
			->getQuery()
			->getOneOrNullResult();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, o, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->leftJoin('p.revision', 'r')
			->where('o.login = :login')
			->andWhere('p.isHome = 1')
			->setParameter('login', $login)
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		/** @var $pages Page[] */
		$pages = $em->createQueryBuilder()
			->select('p, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->orderBy('p.left', 'ASC')
			->getQuery()
			->getResult();

		$tree = new NestedPagesTree($pages);

		if (! $page) {
			throw $this->createNotFoundException(sprintf('Page not found'));
		}

		if (StringManipulationExtension::slugify($page->getTitle()) != $slug) {
			throw $this->createNotFoundException(sprintf('Invalid slug'));
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

			return $this->redirect($this->generateUrl('wiki_page', array(
				'login' => $login,
				'id' => $id,
				'slug' => $slug
			)));
		}

		return array(
			'page' => $page,
			'home' => $home,
			'orga' => $page->getOrga(),
			'form' => $form->createView(),
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route("/wiki/orga/{login}/create/category", name="wiki_create_category")
	 * @Template()
	 */
	public function createCategoryAction($login)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $orga Organization */
		$orga = $em->getRepository('EtuUserBundle:Organization')->findOneByLogin($login);

		if (! $orga) {
			throw $this->createNotFoundException(sprintf('Orga %s not found', $login));
		}

		/** @var $pages Page[] */
		$pages = $em->createQueryBuilder()
			->select('p, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->orderBy('p.left', 'ASC')
			->getQuery()
			->getResult();

		$tree = new NestedPagesTree($pages);

		$categories = array(0 => 'A la racine');
		$parents = array();
		$maxRight = 0;

		foreach ($pages as $page) {
			if ($page->getIsCategory()) {
				$categories[$page->getId()] = $page->getTitle();
				$parents[$page->getId()] = $page;
			}

			if ($page->getRight() > $maxRight) {
				$maxRight = $page->getRight();
			}
		}

		$levels = array(
			Page::LEVEL_CONNECTED => 'wiki.levels.connected',
			Page::LEVEL_ASSO_MEMBER => 'wiki.levels.asso_member',
			Page::LEVEL_ASSO_ADMIN => 'wiki.levels.asso_admin',
			Page::LEVEL_ADMIN => 'wiki.levels.admin'
		);

		$form = $this->createFormBuilder()
			->add('title', 'text')
			->add('category', 'choice', array('choices' => $categories))
			->add('levelToView', 'choice', array('choices' => $levels))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$data = $form->getData();

			$title = $data['title'];
			$parent = $data['category'];

			if (isset($levels[$data['levelToView']])) {
				$levelToView = $data['levelToView'];
			} else {
				$levelToView = Page::LEVEL_CONNECTED;
			}

			$page = new Page();

			$page->setTitle($title)
				->setUser($this->getUser())
				->setOrga($orga)
				->setIsCategory(true)
				->setLevelToView($levelToView);

			if ($parent == 0) {
				$page->setDepth(0)
					->setLeft($maxRight + 1)
					->setRight($maxRight + 2);
			} else {
				/** @var $parent Page */
				$parent = $parents[$parent];

				$em->createQueryBuilder()
					->update('EtuModuleWikiBundle:Page', 'p')
					->set('p.right', 'p.right + 2')
					->where('p.right >= :right')
					->setParameter('right', $parent->getLeft())
					->getQuery()
					->execute();

				$em->createQueryBuilder()
					->update('EtuModuleWikiBundle:Page', 'p')
					->set('p.left', 'p.left + 2')
					->where('p.left > :left')
					->setParameter('left', $parent->getLeft())
					->getQuery()
					->execute();

				$page->setDepth($parent->getDepth() + 1)
					->setLeft($parent->getLeft() + 1)
					->setRight($parent->getLeft() + 2);
			}

			$em->persist($page);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.createCategory.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_index_orga', array('login' => $login)));
		}

		return array(
			'orga' => $orga,
			'form' => $form->createView(),
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route("/wiki/orga/{login}/create/page", name="wiki_create_page")
	 * @Template()
	 */
	public function createPageAction($login)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $orga Organization */
		$orga = $em->getRepository('EtuUserBundle:Organization')->findOneByLogin($login);

		if (! $orga) {
			throw $this->createNotFoundException(sprintf('Orga %s not found', $login));
		}

		/** @var $pages Page[] */
		$pages = $em->createQueryBuilder()
			->select('p, o')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.orga', 'o')
			->andWhere('o.login = :login')
			->setParameter('login', $login)
			->orderBy('p.left', 'ASC')
			->getQuery()
			->getResult();

		$tree = new NestedPagesTree($pages);

		$categories = array(0 => 'A la racine');
		$parents = array();
		$maxRight = 0;

		foreach ($pages as $page) {
			if ($page->getIsCategory()) {
				$categories[$page->getId()] = $page->getTitle();
				$parents[$page->getId()] = $page;
			}

			if ($page->getRight() > $maxRight) {
				$maxRight = $page->getRight();
			}
		}

		$levels = array(
			Page::LEVEL_CONNECTED => 'wiki.levels.connected',
			Page::LEVEL_ASSO_MEMBER => 'wiki.levels.asso_member',
			Page::LEVEL_ASSO_ADMIN => 'wiki.levels.asso_admin',
			Page::LEVEL_ADMIN => 'wiki.levels.admin'
		);

		$form = $this->createFormBuilder()
			->add('title', 'text')
			->add('body', 'redactor')
			->add('category', 'choice', array('choices' => $categories))
			->add('levelToView', 'choice', array('choices' => $levels))
			->add('levelToEdit', 'choice', array('choices' => $levels))
			->add('levelToEditPermissions', 'choice', array('choices' => $levels))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$data = $form->getData();

			$page = new Page();

			$page->setTitle($data['title'])
				->setUser($this->getUser())
				->setOrga($orga)
				->setIsCategory(false);

			if (isset($levels[$data['levelToView']])) {
				$page->setLevelToView($data['levelToView']);
			}

			if (isset($levels[$data['levelToEdit']])) {
				$page->setLevelToEdit($data['levelToEdit']);
			}

			if (isset($levels[$data['levelToEditPermissions']])) {
				$page->setLevelToEditPermissions($data['levelToEditPermissions']);
			}

			$parent = $data['category'];

			if ($parent == 0) {
				$page->setDepth(0)
					->setLeft($maxRight + 1)
					->setRight($maxRight + 2);
			} else {
				/** @var $parent Page */
				$parent = $parents[$parent];

				$em->createQueryBuilder()
					->update('EtuModuleWikiBundle:Page', 'p')
					->set('p.right', 'p.right + 2')
					->where('p.right >= :right')
					->setParameter('right', $parent->getLeft())
					->getQuery()
					->execute();

				$em->createQueryBuilder()
					->update('EtuModuleWikiBundle:Page', 'p')
					->set('p.left', 'p.left + 2')
					->where('p.left > :left')
					->setParameter('left', $parent->getLeft())
					->getQuery()
					->execute();

				$page->setDepth($parent->getDepth() + 1)
					->setLeft($parent->getLeft() + 1)
					->setRight($parent->getLeft() + 2);
			}

			$revision = new PageRevision();
			$revision->setBody(RedactorJsEscaper::escape($data['body']))
				->setUser($this->getUser())
				->setComment('Création');

			$page->setRevision($revision);

			$em->persist($page);
			$em->persist($revision);
			$em->flush();

			$revision->setPageId($page->getId());

			$em->persist($revision);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.page.createPage.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_index_orga', array('login' => $login)));
		}

		return array(
			'orga' => $orga,
			'form' => $form->createView(),
			'tree' => $tree->getNestedTree()
		);
	}
}

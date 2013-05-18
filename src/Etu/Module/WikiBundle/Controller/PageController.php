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
			'orga' => $page->getOrga(),
			'tree' => $tree->getNestedTree(),
		);
	}

	/**
	 * @Route("/wiki/orga/{login}/create/category", name="wiki_create_category")
	 * @Template()
	 */
	public function createCategoryAction($login)
	{
		if (! $this->getUserLayer()->isConnected() || ! $this->getUser()->getIsAdmin()) {
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
				'message' => 'wiki.main.createCategory.confirm'
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
		if (! $this->getUserLayer()->isConnected() || ! $this->getUser()->getIsAdmin()) {
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
				'message' => 'wiki.main.createPage.confirm'
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

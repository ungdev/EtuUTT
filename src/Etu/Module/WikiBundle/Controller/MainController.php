<?php

namespace Etu\Module\WikiBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Util\RedactorJsEscaper;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Module\WikiBundle\Entity\Page;
use Etu\Module\WikiBundle\Entity\PageRevision;
use Etu\Module\WikiBundle\Model\NestedPagesTree;

// Import annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	static public $homeIdentifier = 1;

	/**
	 * @Route("/wiki", name="wiki_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->where('p.id = :home')
			->setParameter('home', self::$homeIdentifier)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			throw new \RuntimeException(
				'Home page of the wiki can not be found. Use php app/console etu:wiki:init if you destroyed it manually.'
			);
		}

		$orgas = $em->getRepository('EtuUserBundle:Organization')->findBy(array(), array('name' => 'ASC'));

		return array(
			'page' => $home,
			'orgas' => $orgas
		);
	}

	/**
	 * @Route("/wiki/home/edit", name="wiki_index_edit")
	 * @Template()
	 */
	public function indexEditAction()
	{
		if (! $this->getUserLayer()->isConnected() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $home Page */
		$home = $em->createQueryBuilder()
			->select('p, r')
			->from('EtuModuleWikiBundle:Page', 'p')
			->leftJoin('p.revision', 'r')
			->where('p.id = :home')
			->setParameter('home', self::$homeIdentifier)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			throw new \RuntimeException(
				'Home page of the wiki can not be found. Use php app/console etu:wiki:init if you destroyed it manually.'
			);
		}

		$revision = $home->createRevision();
		$revision->setBody($home->getRevision()->getBody())
			->setUser($this->getUser());

		$form = $this->createFormBuilder($revision)
			->add('body', 'redactor')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			if (RedactorJsEscaper::escape($revision->getBody()) != $home->getRevision()->getBody()) {
				$home->setRevision($revision);
				$revision->setBody(RedactorJsEscaper::escape($revision->getBody()));

				$em->persist($revision);
				$em->persist($home);
				$em->flush();
			}

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.main.indexEdit.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_index'));
		}

		return array(
			'page' => $home,
			'form' => $form->createView()
		);
	}

	/**
	 * @Route("/wiki/orga/{login}", name="wiki_index_orga")
	 * @Template()
	 */
	public function indexOrgaAction($login)
	{
		if (! $this->getUserLayer()->isConnected() || ! $this->getUser()->getIsAdmin()) {
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

		if (! $home) {
			/** @var $orga Organization */
			$orga = $em->getRepository('EtuUserBundle:Organization')->findOneByLogin($login);

			if (! $orga) {
				throw $this->createNotFoundException(sprintf('Orga %s not found', $login));
			}

			$home = new Page();

			$home
				->setTitle('Accueil')
				->setIsHome(true)
				->setOrga($orga)
				->setLeft(1)
				->setRight(2)
				->setDepth(0)
				->setLevelToDelete(Page::LEVEL_UNREACHABLE)
				->setLevelToCreate(Page::LEVEL_ASSO_ADMIN)
				->setLevelToEdit(Page::LEVEL_ASSO_MEMBER)
				->setLevelToView(Page::LEVEL_CONNECTED);

			$em->persist($home);
			$em->flush();

			$revision = new PageRevision();
			$revision->setPageId($home->getId())
				->setBody('Cette page n\'a pas été modifiée par son association.')
				->setComment('Création automatique');

			$home->setRevision($revision);

			$em->persist($revision);
			$em->persist($home);
			$em->flush();
		}

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route("/wiki/orga/{login}/edit", name="wiki_index_orga_edit")
	 * @Template()
	 */
	public function indexOrgaEditAction($login)
	{
		if (! $this->getUserLayer()->isConnected() || ! $this->getUser()->getIsAdmin()) {
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

		if (! $home) {
			throw $this->createNotFoundException(sprintf('Home page for organization %s not found', $login));
		}

		$revision = $home->createRevision();
		$revision->setBody($home->getRevision()->getBody())
			->setUser($this->getUser())
			->title = $home->getTitle();

		$form = $this->createFormBuilder($revision)
			->add('title', 'text')
			->add('body', 'redactor')
			->add('comment')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			if (RedactorJsEscaper::escape($revision->getBody()) != $home->getRevision()->getBody()) {
				$home->setRevision($revision);
				$revision->setBody(RedactorJsEscaper::escape($revision->getBody()));

				$em->persist($revision);
				$em->persist($home);
				$em->flush();
			}

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.main.indexOrga.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_index_orga', array('login' => $login)));
		}

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'form' => $form->createView(),
			'revisions' => $revisions,
			'tree' => $tree->getNestedTree()
		);
	}

	/**
	 * @Route("/wiki/orga/{login}/revision/{id}/{ready}", defaults={"ready"=false}, name="wiki_index_orga_revision")
	 * @Template()
	 */
	public function indexOrgaRevisionAction($login, $id, $ready)
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
	 * @Route("/wiki/orga/{login}/permissions", name="wiki_index_orga_permissions")
	 * @Template()
	 */
	public function indexOrgaPermissionsAction($login)
	{
		if (! $this->getUserLayer()->isConnected() || ! $this->getUser()->getIsAdmin()) {
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

		if (! $home) {
			throw $this->createNotFoundException(sprintf('Home page for organization %s not found', $login));
		}

		$levels = array(
			Page::LEVEL_CONNECTED => 'wiki.levels.connected',
			Page::LEVEL_ASSO_MEMBER => 'wiki.levels.asso_member',
			Page::LEVEL_ASSO_ADMIN => 'wiki.levels.asso_admin',
			Page::LEVEL_ADMIN => 'wiki.levels.admin'
		);

		$form = $this->createFormBuilder($home)
			->add('levelToCreate', 'choice', array('choices' => $levels))
			->add('levelToDelete', 'choice', array('choices' => $levels))
			->add('levelToEdit', 'choice', array('choices' => $levels))
			->add('levelToEditPermissions', 'choice', array('choices' => $levels))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em->persist($home);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.main.indexOrgaPermissions.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_index_orga', array('login' => $login)));
		}

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'form' => $form->createView(),
			'tree' => $tree->getNestedTree()
		);
	}
}

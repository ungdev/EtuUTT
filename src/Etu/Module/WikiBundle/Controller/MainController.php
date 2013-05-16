<?php

namespace Etu\Module\WikiBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Util\RedactorJsEscaper;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Module\WikiBundle\Entity\Page;

// Import annotations
use Etu\Module\WikiBundle\Entity\PageRevision;
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

		$revision = $home->getRevision();

		$form = $this->createFormBuilder($revision)
			->add('body', 'redactor')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$revision->setBody(RedactorJsEscaper::escape($revision->getBody()));

			$em->persist($revision);
			$em->flush();

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
				->setLevelToDelete(Page::LEVEL_UNREACHABLE)
				->setLevelToCreate(Page::LEVEL_ASSO)
				->setLevelToEdit(Page::LEVEL_ASSO)
				->setLevelToView(Page::LEVEL_CONNECTED);

			$em->persist($home);
			$em->flush();

			$revision = new PageRevision();
			$revision->setPageId($home->getId())
				->setBody('Cette page n\'a pas été modifiée par son association.');

			$home->setRevision($revision);

			$em->persist($revision);
			$em->persist($home);
			$em->flush();
		}

		return array(
			'page' => $home,
			'orga' => $home->getOrga()
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

		if (! $home) {
			throw $this->createNotFoundException(sprintf('Home page for organization %s not found', $login));
		}

		$revision = $home->getRevision();

		$form = $this->createFormBuilder($revision)
			->add('body', 'redactor')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$revision->setBody(RedactorJsEscaper::escape($revision->getBody()));

			$em->persist($revision);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.main.indexOrga.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_index_orga', array('login' => $login)));
		}

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'form' => $form->createView()
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

		if (! $home) {
			/** @var $orga Organization */
			$orga = $em->getRepository('EtuUserBundle:Organization')->findOneByLogin($login);

			if (! $orga) {
				throw $this->createNotFoundException(sprintf('Orga %s not found', $orga));
			}

			$home = new Page();

			$home
				->setTitle('Wiki de '.$orga->getName())
				->setOrga($orga)
				->setLevelToDelete(Page::LEVEL_ADMIN)
				->setLevelToCreate(Page::LEVEL_ASSO)
				->setLevelToEdit(Page::LEVEL_ASSO)
				->setLevelToView(Page::LEVEL_CONNECTED);

			$em->persist($home);
			$em->flush();

			$revision = new PageRevision();
			$revision->setPageId($home->getId())
				->setBody('Cette page n\'a pas été modifiée par son association.');

			$home->setRevision($revision);

			$em->persist($revision);
			$em->persist($home);
			$em->flush();
		}

		$levels = array(
			Page::LEVEL_CONNECTED => 'connected',
			Page::LEVEL_ASSO => 'asso',
			Page::LEVEL_ADMIN => 'admin'
		);

		$form = $this->createFormBuilder($home)
			->add('levelToCreate', 'choice', array('choices' => $levels))
			->add('levelToEdit', 'choice', array('choices' => $levels))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$revision->setBody(RedactorJsEscaper::escape($revision->getBody()));

			$em->persist($revision);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'wiki.main.indexOrga.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_index_orga', array('login' => $login)));
		}

		return array(
			'page' => $home,
			'orga' => $home->getOrga(),
			'form' => $form->createView()
		);
	}
}

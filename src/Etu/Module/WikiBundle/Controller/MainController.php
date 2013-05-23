<?php

namespace Etu\Module\WikiBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Util\RedactorJsEscaper;
use Etu\Module\WikiBundle\Entity\Page;

// Import annotations
use Etu\Module\WikiBundle\Entity\PageRevision;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
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
			->where('p.isHome = 1')
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			$home = new Page();
			$home->setTitle('Accueil')
				->setIsHome(true)
				->setLevelToView(Page::LEVEL_CONNECTED)
				->setLevelToEdit(Page::LEVEL_ADMIN)
				->setLevelToEditPermissions(Page::LEVEL_ADMIN);

			$em->persist($home);
			$em->flush();

			$revision = new PageRevision();
			$revision->setBody('Bienvenue sur le wiki associatif de l\'UTT')
				->setComment('Création automatique')
				->setPageId($home->getId());

			$home->setRevision($revision);

			$em->persist($revision);
			$em->persist($home);
			$em->flush();
		}

		$orgas = $em->getRepository('EtuUserBundle:Organization')->findBy(array(), array('name' => 'ASC'));
		$userOrgas = array();

		foreach ($orgas as $key => $orga) {
			foreach ($this->getUser()->getMemberships() as $membership) {
				if ($membership->getOrganization()->getId() == $orga->getId()) {
					unset($orgas[$key]);
					$userOrgas[] = $orga;
				}
			}
		}

		return array(
			'page' => $home,
			'orgas' => $orgas,
			'userOrgas' => $userOrgas
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
			->where('p.isHome = 1')
			->setMaxResults(1)
			->getQuery()
			->getOneOrNullResult();

		if (! $home) {
			throw $this->createNotFoundException('Page not found');
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
				'message' => 'wiki.main.indexEdit.confirm'
			));

			return $this->redirect($this->generateUrl('wiki_index'));
		}

		return array(
			'page' => $home,
			'form' => $form->createView(),
			'revisions' => $revisions
		);
	}
}

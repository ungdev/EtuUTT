<?php

namespace Etu\Core\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Page;
use Etu\Core\CoreBundle\Framework\Definition\Controller;

use Etu\Core\CoreBundle\Framework\Module\ModulesManager;
use Etu\Core\CoreBundle\Stats\TgaAudienceDriver;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Symfony\Component\HttpFoundation\Response;

// Import @Route() and @Template() annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
	/**
	 * @Route("", name="admin_index")
	 * @Template()
	 */
	public function indexAction()
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		// Modules
		/** @var $modulesManager ModulesManager */
		$modulesManager = $this->get('etu.core.modules_manager');

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $request->get('modules')) {
			$enabledModules = array_keys((array) $request->get('modules'));

			foreach ($enabledModules as $key => $module) {
				if ($module = $modulesManager->getModuleByIdentifier($module)) {
					$enabledModules[$key] = get_class($module);
				} else {
					unset($enabledModules[$key]);
				}
			}

			$yaml = \Symfony\Component\Yaml\Yaml::dump(array('modules' => $enabledModules));
			$configFile = $this->getKernel()->getRootDir().'/config/modules.yml';

			file_put_contents($configFile, $yaml);

			// Clear routes cache
			$iterator = new \RecursiveIteratorIterator(
				new \RecursiveDirectoryIterator($this->getKernel()->getRootDir().'/cache'),
				\RecursiveIteratorIterator::CHILD_FIRST
			);

			foreach ($iterator as $file) {
				if ($file->isFile() &&
					(strpos($file->getBasename(), 'UrlGenerator') !== false)
					|| (strpos($file->getBasename(), 'UrlMatcher') !== false)
				) {
					unlink($file->getPathname());
				}
			}

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'admin.index.confirm'
			));

			return $this->redirect($this->generateUrl('admin_index'));
		}

		// Pages
		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$pages = $em->getRepository('EtuCoreBundle:Page')->findBy(array(), array('title' => 'ASC'));

		return array(
			'modules' => $modulesManager->getModules(),
			'pages' => $pages,
		);
	}

	/**
	 * @Route("/stats", name="admin_stats")
	 * @Template()
	 */
	public function statsAction()
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		// Stats
		$statsDriver = new TgaAudienceDriver($this->getDoctrine());

		return array_merge(
			$statsDriver->getGlobalStats(),
			$statsDriver->getVisitorsStats(),
			$statsDriver->getTrafficStats($this->container->getParameter('etu.domain'))
		);
	}

	/**
	 * @Route("/page/create", name="admin_page_create")
	 * @Template()
	 */
	public function pageCreateAction()
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$page = new Page();

		$form = $this->createFormBuilder($page)
			->add('title')
			->add('content', 'redactor')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$page->setSlug(StringManipulationExtension::slugify($page->getTitle()));

			$em->persist($page);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'admin.page.create.confirm'
			));

			return $this->redirect($this->generateUrl('admin_index'));
		}

		return array(
			'form' => $form->createView()
		);
	}

	/**
	 * @Route("/page/edit/{id}", name="admin_page_edit")
	 * @Template()
	 */
	public function pageEditAction($id)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$page = $em->getRepository('EtuCoreBundle:Page')->find($id);

		$form = $this->createFormBuilder($page)
			->add('title')
			->add('content', 'redactor')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em->persist($page);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'admin.page.edit.confirm'
			));

			return $this->redirect($this->generateUrl('admin_index'));
		}

		return array(
			'form' => $form->createView()
		);
	}

	/**
	 * @Route("/page/delete/{id}", name="admin_page_delete")
	 * @Template()
	 */
	public function pageDeleteAction($id)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$page = $em->getRepository('EtuCoreBundle:Page')->find($id);

		return array(
			'page' => $page
		);
	}

	/**
	 * @Route("/page/delete/{id}/confirm", name="admin_page_delete_confirm")
	 * @Template()
	 */
	public function pageDeleteConfirmAction($id)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$page = $em->getRepository('EtuCoreBundle:Page')->find($id);

		$em->remove($page);

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'admin.page.delete.confirm'
		));

		return $this->redirect($this->generateUrl('admin_index'));
	}
}

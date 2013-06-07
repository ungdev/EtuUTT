<?php

namespace Etu\Core\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Page;
use Etu\Core\CoreBundle\Framework\Definition\Controller;

use Etu\Core\CoreBundle\Framework\Module\ModulesManager;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Symfony\Component\HttpFoundation\Response;

// Import @Route() and @Template() annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Tga\AudienceBundle\Stats\Processor;

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

		$top = explode('cached', shell_exec('top -b -n 1'));
		$top = $top[0].'cached';

		return array(
			'top' => $top
		);
	}

	/**
	 * @Route("/modules", name="admin_modules")
	 * @Template()
	 */
	public function modulesAction()
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		// Modules
		/** @var $modulesManager ModulesManager */
		$modulesManager = $this->get('etu.core.modules_manager');

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST') {
			if ($request->get('modules')) {
				$enabledModules = array_keys((array) $request->get('modules'));
			} else {
				$enabledModules = array();
			}

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

			// Clear routes cache (production)
			if (file_exists($this->getKernel()->getRootDir().'/cache/prod')) {
				$iterator = new \DirectoryIterator($this->getKernel()->getRootDir().'/cache/prod');

				foreach ($iterator as $file) {
					if ($file->isFile() &&
						(strpos($file->getBasename(), 'UrlGenerator') !== false)
						|| (strpos($file->getBasename(), 'UrlMatcher') !== false)
					) {
						unlink($file->getPathname());
					}
				}
			}

			// Clear routes cache (development)
			if (file_exists($this->getKernel()->getRootDir().'/cache/dev')) {
				$iterator = new \DirectoryIterator($this->getKernel()->getRootDir().'/cache/dev');

				foreach ($iterator as $file) {
					if ($file->isFile() &&
						(strpos($file->getBasename(), 'UrlGenerator') !== false)
						|| (strpos($file->getBasename(), 'UrlMatcher') !== false)
					) {
						unlink($file->getPathname());
					}
				}
			}


			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'core.admin.modules.confirm'
			));

			return $this->redirect($this->generateUrl('admin_modules'));
		}

		return array(
			'modules' => $modulesManager->getModules(),
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

		/** @var $processor Processor */
		$processor = $this->get('tga_audience.stats')->getProcessor();

		$uniqueVisitors = array_merge(array(array('Date', 'Visitors')), $processor->getUniqueVisitors());
		$pagesCalls = array_merge(array(array('Date', 'Calls')), $processor->getPageCalls());

		$platforms = array_merge(array(array('Platform', 'Count')), $processor->getPlatforms());
		$browsers = array_merge(array(array('Browser', 'Count')), $processor->getBrowsers());

		$externalSources = array_merge(array(array('Source', 'Count')), $processor->getExternalSources());

		$allExternalSources = $processor->getMostUsedExternalSources();

		foreach ($allExternalSources as $key => $value) {
			if (! is_object($value)) {
				unset($allExternalSources[$key]);
			}
		}

		return array(
			'uniqueVisitors' => json_encode($uniqueVisitors),
			'uniqueVisitorsCount' => $processor->getUniqueVisitorsCount(),
			'pagesCalls' => json_encode($pagesCalls),
			'pagesCallsCount' => $processor->getPageCallsCount(),
			'averageVisitedPages' => $processor->getAverageVisitedPages(),
			'averageDuration' => $processor->getAverageDuration(),
			'averageTimeToLoad' => $processor->getAverageTimeToLoad(),
			'platforms' => json_encode($platforms),
			'browsers' => json_encode($browsers),
			'mostUsedRoutes' => $processor->getMostUsedRoutes(),
			'browsersVersions' => $processor->getMostUsedBrowsers(),
			'externalSources' => json_encode($externalSources),
			'allExternalSources' => $allExternalSources
		);
	}

	/**
	 * @Route("/pages", name="admin_pages")
	 * @Template()
	 */
	public function pagesAction()
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('pages.admin')) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$pages = $em->getRepository('EtuCoreBundle:Page')->findBy(array(), array('title' => 'ASC'));

		return array(
			'pages' => $pages,
		);
	}

	/**
	 * @Route("/page/create", name="admin_page_create")
	 * @Template()
	 */
	public function pageCreateAction()
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('pages.admin')) {
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
				'message' => 'core.admin.pageCreate.confirm'
			));

			return $this->redirect($this->generateUrl('admin_pages'));
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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('pages.admin')) {
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
				'message' => 'core.admin.pageEdit.confirm'
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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('pages.admin')) {
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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->hasPermission('pages.admin')) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$page = $em->getRepository('EtuCoreBundle:Page')->find($id);

		$em->remove($page);

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'core.admin.pageDelete.confirm'
		));

		return $this->redirect($this->generateUrl('admin_index'));
	}
}

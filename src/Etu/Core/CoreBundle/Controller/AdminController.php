<?php

namespace Etu\Core\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Page;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Framework\Definition\Module;
use Etu\Core\CoreBundle\Framework\Module\ModulesManager;
use Etu\Core\CoreBundle\Twig\Extension\StringManipulationExtension;
use Etu\Core\CoreBundle\Util\Server;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Form\Extension\Core\Type\SubmitType;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\HttpFoundation\Request;

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
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_HOME');

        return [];
    }

    /**
     * @Route("/server", name="admin_server")
     * @Template()
     */
    public function serverAction()
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_SERVER');

        return [
            'status' => new Server\Status(),
        ];
    }

    /**
     * @Route("/modules", name="admin_modules")
     * @Template()
     */
    public function modulesAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_MODULES');

        // Modules
        /** @var $modulesManager ModulesManager */
        $modulesManager = $this->get('etu.core.modules_manager');
        $modules = $modulesManager->getModules();

        /** @var $module Module */
        foreach ($modules as $module) {
            $modules->get($module->getIdentifier())->needed = false;
            $modules->get($module->getIdentifier())->neededBy = [];
            $modules->get($module->getIdentifier())->canBeEnabled = true;
            $modules->get($module->getIdentifier())->need = [];
        }

        /** @var $module Module */
        foreach ($modules as $module) {
            if ($module->isEnabled()) {
                foreach ($module->getRequirements() as $requirement) {
                    $modules->get($requirement)->needed = true;
                    $modules->get($requirement)->neededBy[] = $module->getTitle();
                }
            }

            foreach ($module->getRequirements() as $requirement) {
                if (!$modules->get($requirement)->isEnabled()) {
                    $modules->get($module->getIdentifier())->canBeEnabled = false;
                    $modules->get($module->getIdentifier())->need[] = $modules->get($requirement)->getTitle();
                }
            }
        }

        if ('POST' == $request->getMethod()) {
            if ($request->get('modules')) {
                $enabledModules = array_keys((array) $request->get('modules'));
            } else {
                $enabledModules = [];
            }

            foreach ($enabledModules as $key => $module) {
                if ($module = $modulesManager->getModuleByIdentifier($module)) {
                    $enabledModules[$key] = get_class($module);
                } else {
                    unset($enabledModules[$key]);
                }
            }

            foreach ($modules as $module) {
                if ($module->needed) {
                    $enabledModules[] = get_class($module);
                }
            }

            $yaml = \Symfony\Component\Yaml\Yaml::dump(['modules' => $enabledModules]);
            $configFile = $this->getKernel()->getRootDir().'/config/modules.yml';

            file_put_contents($configFile, $yaml);

            // Clear routes cache (production)
            if (file_exists($this->getKernel()->getRootDir().'/cache/prod')) {
                $iterator = new \DirectoryIterator($this->getKernel()->getRootDir().'/cache/prod');

                foreach ($iterator as $file) {
                    if ($file->isFile() &&
                        (false !== mb_strpos($file->getBasename(), 'UrlGenerator'))
                        || (false !== mb_strpos($file->getBasename(), 'UrlMatcher'))
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
                        (false !== mb_strpos($file->getBasename(), 'UrlGenerator'))
                        || (false !== mb_strpos($file->getBasename(), 'UrlMatcher'))
                    ) {
                        unlink($file->getPathname());
                    }
                }
            }

            $logger = $this->get('monolog.logger.admin');
            $logger->warn('`'.$this->getUser()->getLogin().'` edit enabled modules');

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'core.admin.modules.confirm',
            ]);

            return $this->redirect($this->generateUrl('admin_modules'));
        }

        /** @var $module Module */
        foreach ($modules as $module) {
            $modules->get($module->getIdentifier())->neededBy =
                implode(', ', $modules->get($module->getIdentifier())->neededBy);

            $modules->get($module->getIdentifier())->need =
                implode(', ', $modules->get($module->getIdentifier())->need);
        }

        return [
            'modules' => $modules,
        ];
    }

    /**
     * @Route("/pages", name="admin_pages")
     * @Template()
     */
    public function pagesAction()
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PAGES');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $pages = $em->getRepository('EtuCoreBundle:Page')->findBy([], ['title' => 'ASC']);

        return [
            'pages' => $pages,
        ];
    }

    /**
     * @Route("/page/create", name="admin_page_create")
     * @Template()
     */
    public function pageCreateAction(Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PAGES');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $page = new Page();

        $page->setContent("<p>\n\tHello paragraph !\n</p>");
        $form = $this->createFormBuilder($page)
            ->add('title', TextType::class, ['label' => 'core.admin.pageCreate.name'])
            ->add('content', TextareaType::class, ['label' => 'core.admin.pageCreate.content'])
            ->add('submit', SubmitType::class, ['label' => 'core.admin.pageCreate.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $page->setSlug(StringManipulationExtension::slugify($page->getTitle()));

            $em->persist($page);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'core.admin.pageCreate.confirm',
            ]);

            return $this->redirect($this->generateUrl('admin_pages'));
        }

        return [
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/page/edit/{id}", name="admin_page_edit")
     * @Template()
     *
     * @param mixed $id
     */
    public function pageEditAction($id, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PAGES');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $page = $em->getRepository('EtuCoreBundle:Page')->find($id);

        $form = $this->createFormBuilder($page)
            ->add('title', TextType::class, ['label' => 'core.admin.pageEdit.name'])
            ->add('content', TextareaType::class, ['label' => 'core.admin.pageEdit.content'])
            ->add('submit', SubmitType::class, ['label' => 'core.admin.pageEdit.submit'])
            ->getForm();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            // $cacheDriver = $em->getConfiguration()->getResultCacheImpl();
            // $cacheDriver->delete('EtuCoreBundle/page:'.$page->getSlug());

            $em->persist($page);
            $em->flush();

            $this->get('session')->getFlashBag()->set('message', [
                'type' => 'success',
                'message' => 'core.admin.pageEdit.confirm',
            ]);

            return $this->redirect($this->generateUrl('admin_pages'));
        }

        return [
            'page' => $page,
            'form' => $form->createView(),
        ];
    }

    /**
     * @Route("/page/delete/{id}", name="admin_page_delete")
     * @Template()
     *
     * @param mixed $id
     */
    public function pageDeleteAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PAGES');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $page = $em->getRepository('EtuCoreBundle:Page')->find($id);

        return [
            'page' => $page,
        ];
    }

    /**
     * @Route("/page/delete/{id}/confirm", name="admin_page_delete_confirm")
     * @Template()
     *
     * @param mixed $id
     */
    public function pageDeleteConfirmAction($id)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PAGES');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $page = $em->getRepository('EtuCoreBundle:Page')->find($id);

        $em->remove($page);
        $em->flush();

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'core.admin.pageDelete.confirm',
        ]);

        return $this->redirect($this->generateUrl('admin_pages'));
    }
}

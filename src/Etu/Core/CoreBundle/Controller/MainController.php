<?php

namespace Etu\Core\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Entity\Page;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Home\HomeRenderer;
use Etu\Core\UserBundle\Entity\User;
use Etu\Module\EventsBundle\Entity\Event;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
    /**
     * @Route("/", name="homepage")
     * @Template()
     */
    public function indexAction()
    {
        if ($this->isGranted('ROLE_CORE_HOMEPAGE')) {
            return $this->indexUserAction();
        }

        return $this->indexAnonymousAction();
    }

    /**
     * @Route("/more/{page}", name="flux_more", options={"expose"=true})
     * @Template()
     *
     * @param mixed $page
     */
    public function moreAction($page)
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_SUBSCRIBE');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        // Load only notifications we should display, ie. notifications sent from
        // currently enabled modules

        $query = $em
            ->createQueryBuilder()
            ->select('n')
            ->from('EtuCoreBundle:Notification', 'n')
            ->orderBy('n.createdAt', 'DESC')
            ->setFirstResult(($page - 1) * 25)
            ->setMaxResults(25);

        /*
         * Subscriptions
         */
        /** @var $subscriptions Subscription[] */
        $subscriptions = $this->get('etu.twig.global_accessor')->get('notifs')->get('subscriptions');
        $subscriptionsWhere = [];
        $notifications = [];

        if (!empty($subscriptions)) {
            foreach ($subscriptions as $key => $subscription) {
                $subscriptionsWhere[] = '(n.entityType = :type_'.$key.' AND n.entityId = :id_'.$key.')';

                $query->setParameter('type_'.$key, $subscription->getEntityType());
                $query->setParameter('id_'.$key, $subscription->getEntityId());
            }

            if (!empty($subscriptionsWhere)) {
                $query = $query->andWhere(implode(' OR ', $subscriptionsWhere));
            }

            /*
             * Modules
             */
            $modulesWhere = ['n.module = \'core\'', 'n.module = \'user\''];

            foreach ($this->getKernel()->getModulesDefinitions() as $module) {
                $identifier = $module->getIdentifier();
                $modulesWhere[] = 'n.module = :module_'.$identifier;

                $query->setParameter('module_'.$identifier, $identifier);
            }

            if (!empty($modulesWhere)) {
                $query = $query->andWhere(implode(' OR ', $modulesWhere));
            }

            // Query
            /** @var $notifications Notification[] */
            $notifications = $query->getQuery()->getResult();
        }

        $user = $this->getUser();
        $user->setLastVisitHome(new \DateTime());

        $em->persist($user);

        if (!$user->testingContext) {
            $em->flush();
        }

        if (empty($notifications)) {
            return new Response('no_more');
        }

        return $this->render('EtuCoreBundle:Main:more.html.twig', [
            'notifs' => $notifications,
        ]);
    }

    /**
     * @Route("/change-locale/{lang}", name="change_locale")
     * @Template()
     *
     * @param mixed $lang
     */
    public function changeLocaleAction($lang, Request $request)
    {
        $this->denyAccessUnlessGranted('ROLE_USER');

        // Change locale if the given locale is available
        if (in_array($lang, $this->container->getParameter('etu.translation.languages'))) {
            $this->get('session')->set('_locale', $lang);

            // Change user language
            if ($this->isGranted('ROLE_USER')) {
                /** @var $em EntityManager */
                $em = $this->getDoctrine()->getManager();

                $user = $this->getUser();
                $user->setLanguage($lang);

                $em->persist($user);
                $em->flush();
            }
        }

        $url = $request->server->get('HTTP_REFERER');

        $this->get('session')->getFlashBag()->set('message', [
            'type' => 'success',
            'message' => 'core.main.changeLocale.confirm',
        ]);

        // Redirect wisely
        if ($this->container->getParameter('etu.domain') == parse_url($url, PHP_URL_HOST)) {
            return $this->redirect($url);
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @Route("/desktop-version", name="desktop_version")
     * @Template()
     */
    public function desktopAction(Request $request)
    {
        setcookie('disable_responsive', true, time() + 3600 * 24 * 365);

        $url = $request->server->get('HTTP_REFERER');

        // Redirect wisely
        if ($this->container->getParameter('etu.domain') == parse_url($url, PHP_URL_HOST)) {
            return $this->redirect($url);
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @Route("/mobile-version", name="mobile_version")
     * @Template()
     */
    public function mobileAction(Request $request)
    {
        setcookie('disable_responsive', false, time() - 10);

        $url = $request->server->get('HTTP_REFERER');

        // Redirect wisely
        if ($this->container->getParameter('etu.domain') == parse_url($url, PHP_URL_HOST)) {
            return $this->redirect($url);
        }

        return $this->redirect($this->generateUrl('homepage'));
    }

    /**
     * @Route("/contributors", name="contributors")
     * @Template()
     */
    public function contributorsAction()
    {
        return [];
    }

    /**
     * @Route("/page/{slug}", name="page_view")
     * @Template()
     *
     * @param mixed $slug
     */
    public function pageAction($slug)
    {
        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        $query = $em->getRepository('EtuCoreBundle:Page')
            ->createQueryBuilder('p')
            ->select('p')
            ->where('p.slug = :slug')
            ->setParameter('slug', $slug)
            ->setMaxResults(1)
            ->getQuery();

        // $query->setResultCacheId('EtuCoreBundle/page:'.$slug);
        // $query->useResultCache(true, 3600 * 24);

        /** @var $page Page */
        $page = $query->getOneOrNullResult();

        if (!$page) {
            throw $this->createNotFoundException('Invalid slug');
        }

        return ['page' => $page];
    }

    /**
     * @Route("/redirect", name="redirect_view")
     * @Template()
     */
    public function redirectAction()
    {
        return [];
    }

    /**
     * @Route("/app-download", name="app_download_view")
     * @Template()
     */
    public function appDownloadAction()
    {
        return [];
    }

    /**
     * @return Response
     */
    protected function indexAnonymousAction()
    {
        /** @var \Etu\Core\CoreBundle\Framework\Module\ModulesManager $modulesManager */
        $modulesManager = $this->get('etu.core.modules_manager');

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $events = [];

        if ($modulesManager->getModuleByIdentifier('events')->isEnabled()) {
            $query = $em->createQueryBuilder()
                ->select('e, o')
                ->from('EtuModuleEventsBundle:Event', 'e')
                ->leftJoin('e.orga', 'o')
                ->where('e.begin >= :begin')
                ->setParameter('begin', new \DateTime())
                ->andWhere('e.privacy <= :public')
                ->setParameter('public', Event::PRIVACY_PUBLIC)
                ->orderBy('e.begin', 'ASC')
                ->addOrderBy('e.end', 'ASC')
                ->setMaxResults(4)
                ->getQuery();

            $query->useResultCache(true, 3600);

            $events = $query->getResult();
        }

        return $this->render('EtuCoreBundle:Main:indexAnonymous.html.twig', [
            'events' => $events,
        ]);
    }

    /**
     * @return Response
     */
    protected function indexUserAction()
    {
        $this->denyAccessUnlessGranted('ROLE_CORE_HOMEPAGE');

        /** @var $em EntityManager */
        $em = $this->getDoctrine()->getManager();

        /** @var HomeRenderer $homeRenderer */
        $homeRenderer = $this->get('etu.core.home_renderer');

        /** @var User $user */
        $user = $this->getUser();

        $view = $this->render('EtuCoreBundle:Main:index.html.twig', [
            'columns' => $homeRenderer->renderBlocks(),
            'firstLogin' => $user->getFirstLogin(),
        ]);

        $user->setLastVisitHome(new \DateTime());

        if (!$user->getFirstLogin()) {
            $user->setFirstLogin(true);
        }

        $em->persist($user);

        if (!$user->testingContext) {
            $em->flush();
        }

        return $view;
    }
}

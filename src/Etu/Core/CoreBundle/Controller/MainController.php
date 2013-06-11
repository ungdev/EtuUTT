<?php

namespace Etu\Core\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Entity\Page;
use Etu\Core\CoreBundle\Entity\Subscription;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;

use Symfony\Component\HttpFoundation\Response;

// Import @Route() and @Template() annotations
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/", name="homepage")
	 * @Template()
	 */
	public function indexAction()
	{
		if ($this->getUserLayer()->isUser()) {
			return $this->indexUserAction();
		}

		if ($this->getUserLayer()->isOrga()) {
			return $this->redirect($this->generateUrl('orga_admin'));
		}

		return $this->indexAnonymousAction();
	}

	/**
	 * @Route("/change-locale/{lang}", name="change_locale")
	 * @Template()
	 */
	public function changeLocaleAction($lang)
	{
		// Change locale if the given locale is available
		if (in_array($lang, $this->container->getParameter('etu.translation.languages'))) {
			$this->get('session')->set('_locale', $lang);

			// Change user language
			if ($this->getUserLayer()->isUser()) {
				/** @var $em EntityManager */
				$em = $this->getDoctrine()->getManager();

				$user = $this->getUser();
				$user->setLanguage($lang);

				$em->persist($user);
				$em->flush();
			}
		}

		$url = $this->getRequest()->server->get('HTTP_REFERER');

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'core.main.changeLocale.confirm'
		));

		// Redirect wisely
		if ($this->container->getParameter('etu.domain') == parse_url($url, PHP_URL_HOST)) {
			return $this->redirect($url);
		}

		return $this->redirect($this->generateUrl('homepage'));
	}

	/**
	 * @Route("/page/{slug}", name="page_view")
	 * @Template()
	 */
	public function pageAction($slug)
	{
		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $page Page */
		$page = $em->getRepository('EtuCoreBundle:Page')->findOneBySlug($slug);

		if (! $page) {
			throw $this->createNotFoundException('Invalid slug');
		}

		return array('page' => $page);
	}

	/**
	 * @return Response
	 */
	protected function indexAnonymousAction()
	{
		return $this->render('EtuCoreBundle:Main:indexAnonymous.html.twig');
	}

	/**
	 * @return Response
	 */
	protected function indexUserAction()
	{
		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		// $imap = new ImapManager($this->get('session')->get('ticket'));

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		// Load only notifications we should display, ie. notifications sent from
		// currently enabled modules

		$query = $em
			->createQueryBuilder()
			->select('n')
			->from('EtuCoreBundle:Notification', 'n')
			->where('n.authorId != :userId')
			->setParameter('userId', $this->getUser()->getId())
			->setMaxResults(50);

		/*
		 * Subscriptions
		 */
		/** @var $subscriptions Subscription[] */
		$subscriptions = $this->get('etu.twig.global_accessor')->get('notifs')->get('subscriptions');
		$subscriptionsWhere = array();
		$notifications = array();

		if (! empty($subscriptions)) {

			foreach ($subscriptions as $key => $subscription) {
				$subscriptionsWhere[] = '(n.entityType = :type_'.$key.' AND n.entityId = :id_'.$key.')';

				$query->setParameter('type_'.$key, $subscription->getEntityType());
				$query->setParameter('id_'.$key, $subscription->getEntityId());
			}

			if (! empty($subscriptionsWhere)) {
				$query = $query->andWhere(implode(' OR ', $subscriptionsWhere));
			}

			/*
			 * Modules
			 */
			$modulesWhere = array('n.module = \'core\'', 'n.module = \'user\'');

			foreach ($this->getKernel()->getModulesDefinitions() as $module) {
				$identifier = $module->getIdentifier();
				$modulesWhere[] = 'n.module = :module_'.$identifier;

				$query->setParameter('module_'.$identifier, $identifier);
			}

			if (! empty($modulesWhere)) {
				$query = $query->andWhere(implode(' OR ', $modulesWhere));
			}

			// Query
			/** @var $notifications Notification[] */
			$notifications = $query->getQuery()->getResult();
		}

		$this->get('twig')->addGlobal('etu_count_new_notifs', 0);

		$view = $this->render('EtuCoreBundle:Main:index.html.twig', array(
			'notifs' => $notifications
		));

		$user = $this->getUser();
		$user->setLastVisitHome(new \DateTime());

		$em->persist($user);

		if (! $user->testingContext) {
			$em->flush();
		}

		return $view;
	}
}

<?php

namespace Etu\Core\CoreBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Entity\Notification;
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
	 * @Route("/change-locale/redirect/{url}", name="change_locale_redirect")
	 * @Template()
	 */
	public function changeLocaleRedirectAction($url)
	{
		$url = urldecode($url);

		// Redirect wisely
		if ($this->container->getParameter('etu.domain') == parse_url($url, PHP_URL_HOST)) {
			return $this->redirect($url);
		}

		return $this->redirect($this->generateUrl('homepage'));
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

		return $this->redirect($this->generateUrl('change_locale_redirect', array(
			'url' => urlencode($this->getRequest()->server->get('HTTP_REFERER'))
		)));
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

		// Load only notifications we should display, ie. notifications sent from
		// currently enabled modules
		$where = array();

		$query = $em
			->createQueryBuilder()
			->select('n')
			->from('EtuCoreBundle:Notification', 'n')
			->where('n.user = :user')
			->orderBy('n.isSuper', 'DESC')
			->addOrderBy('n.date', 'DESC')
			->setParameter('user', $this->getUser()->getId())
			->setMaxResults(50);

		foreach ($this->getKernel()->getModulesDefinitions() as $module) {
			$identifier = $module->getIdentifier();

			$where[] = 'n.module = :'.$identifier;
			$query->setParameter($identifier, $identifier);
		}

		/** @var $notifications Notification[] */
		$notifications = $query->andWhere(implode(' OR ', $where))->getQuery()->getResult();

		$this->get('twig')->addGlobal('etu_count_new_notifs', 0);

		$view = $this->render('EtuCoreBundle:Main:index.html.twig', array(
			'notifs' => $notifications
		));

		// Set notifications as viewed
		foreach ($notifications as $notif) {
			if ($notif->getIsNew()) {
				$notif->setIsNew(false);
				$em->persist($notif);
			}
		}

		$em->flush();

		return $view;
	}
}

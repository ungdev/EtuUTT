<?php

namespace Etu\Core\UserBundle\Controller;

use Etu\Core\UserBundle\Ldap\LdapManager;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MainController extends Controller
{
	/**
	 * @Route("/user", name="user_connect")
	 * @Template()
	 */
	public function connectAction()
	{
		/*
		 * Redirect to CAS server if not logged in
		 * Get the username, find the infos in the database
		 * If not present, use LDAP
		 */
		require __DIR__.'/../Resources/lib/phpCAS/CAS.php';

		\phpCAS::client('1.0', 'cas.utt.fr', 443, '/cas/', false);
		\phpCAS::setNoCasServerValidation();
		\phpCAS::forceAuthentication();

		// Get username
		$username = \phpCAS::getUser();

		$ldap = new LdapManager(
			$this->container->getParameter('etu.ldap.host'),
			$this->container->getParameter('etu.ldap.port')
		);

		$this->get('session')->set('user', $ldap->getUser($username));
		$this->get('session')->getFlashBag()->set('success', 'Vous êtes bien connecté');

		return $this->redirect($this->generateUrl('homepage'));
	}

	/**
	 * @Route("/user/disconnect", name="user_disconnect")
	 * @Template()
	 */
	public function disconnectAction()
	{
		$this->get('session')->set('user', null);
		$this->get('session')->getFlashBag()->set('success', 'Vous êtes bien déconnecté');

		return $this->redirect($this->generateUrl('homepage'));
	}
}

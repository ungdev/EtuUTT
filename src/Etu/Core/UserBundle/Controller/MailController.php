<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class MailController extends Controller
{
	/**
	 * @Route("/user/mail", name="user_mails")
	 * @Template()
	 */
	public function mailAction()
	{
		/*
		 * Redirect to CAS server if not logged in
		 * Get the username, find the infos in the database
		 * If not present, use LDAP
		 */
		require __DIR__.'/../Resources/lib/phpCAS/CAS.php';

		\phpCAS::proxy(
			$this->container->getParameter('etu.cas.version'),
			$this->container->getParameter('etu.cas.host'),
			$this->container->getParameter('etu.cas.port'),
			$this->container->getParameter('etu.cas.path'),
			$this->container->getParameter('etu.cas.change_session_id')
		);

		\phpCAS::setDebug(__DIR__.'/../Resources/temp/logs.txt');
		\phpCAS::setNoCasServerValidation();
		\phpCAS::forceAuthentication();

		\phpCAS::retrievePT('http://openutt.utt.fr/user/mail', $errorCode, $errorMessage);

		var_dump($errorCode, $errorMessage);
		exit;
	}
}

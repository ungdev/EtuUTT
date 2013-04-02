<?php

namespace Etu\Core\UserBundle\Controller;

use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Ldap\LdapManager;
use Symfony\Component\HttpFoundation\Response;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;

use Imagine\Exception\InvalidArgumentException;
use Imagine\Gd\Image;
use Imagine\Gd\Imagine;
use Imagine\Image\Box;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class AuthController extends Controller
{
	/**
	 * @Route("/user", name="user_connect")
	 * @Template()
	 */
	public function connectAction()
	{
		if ($this->getUser() instanceof User || $this->getUser() instanceof Organization) {
			return $this->redirect($this->generateUrl('homepage'));
		}

		/*
		 * Redirect to CAS server if not logged in
		 * Get the username, find the infos in the database
		 * If not present, use LDAP
		 */
		require __DIR__.'/../Resources/lib/phpCAS/CAS.php';

		\phpCAS::client('1.0', 'cas.utt.fr', 443, '/cas/', false);
		\phpCAS::setNoCasServerValidation();

		if (\phpCAS::isAuthenticated()) {
			// Try to connect user
			$user = $this->connectUser(\phpCAS::getUser());

			// Redirection
			if ($user instanceof User) {
				$this->get('session')->set('user', $user->getId());
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'success',
					'message' => 'user.auth.confirm'
				));

				return $this->redirect($this->generateUrl('homepage'));
			}
		}

		// If we can't, we ask for the method
		return array();
	}

	/**
	 * @Route("/user/cas", name="user_connect_cas")
	 * @Template()
	 */
	public function connectCasAction()
	{
		if ($this->getUser() instanceof User || $this->getUser() instanceof Organization) {
			return $this->redirect($this->generateUrl('homepage'));
		}

		/*
		 * Redirect to CAS server if not logged in
		 * Get the username, find the infos in the database
		 * If not present, use LDAP
		 */
		require __DIR__.'/../Resources/lib/phpCAS/CAS.php';

		\phpCAS::client('1.0', 'cas.utt.fr', 443, '/cas/', false);
		\phpCAS::setNoCasServerValidation();
		\phpCAS::forceAuthentication();

		// Try to connect user
		$user = $this->connectUser(\phpCAS::getUser());

		$this->get('session')->set('user', $user->getId());
		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'user.auth.confirm'
		));

		return $this->redirect($this->generateUrl('homepage'));
	}

	/**
	 * @Route("/user/external", name="user_connect_external")
	 * @Template()
	 */
	public function connectExternalAction()
	{
		if ($this->getUser() instanceof User || $this->getUser() instanceof Organization) {
			return $this->redirect($this->generateUrl('homepage'));
		}

		$em = $this->getDoctrine()->getManager();

		$user = new User();

		$form = $this->createFormBuilder($user)
			->add('login')
			->add('password', 'password')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$result = $em->getRepository('EtuUserBundle:User')->findOneBy(array(
				'login' => $user->getLogin(),
				'password' => $this->get('etu.user.crypting')->encrypt($user->getPassword())
			));

			if ($result) {
				$this->get('session')->set('user', $result->getId());
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'success',
					'message' => 'user.auth.confirm'
				));

				return $this->redirect($this->generateUrl('homepage'));
			} else {
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'error',
					'message' => 'user.auth.error'
				));

				return $this->redirect($this->generateUrl('user_connect_external'));
			}
		}

		return array(
			'form' => $form->createView()
		);
	}

	/**
	 * @Route("/user/organization", name="user_connect_orga")
	 * @Template()
	 */
	public function connectOrganizationAction()
	{
		if ($this->getUser() instanceof User || $this->getUser() instanceof Organization) {
			return $this->createAccessDeniedResponse();
		}

		$em = $this->getDoctrine()->getManager();

		$orga = new Organization();

		$form = $this->createFormBuilder($orga)
			->add('login')
			->add('password', 'password')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$result = $em->getRepository('EtuUserBundle:Organization')->findOneBy(array(
				'login' => $orga->getLogin(),
				'password' => $this->get('etu.user.crypting')->encrypt($orga->getPassword())
			));

			if ($result) {
				$this->get('session')->set('user', null);
				$this->get('session')->set('orga', $result->getId());
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'success',
					'message' => 'user.auth.confirm'
				));

				return $this->redirect($this->generateUrl('homepage'));
			} else {
				$this->get('session')->getFlashBag()->set('message', array(
					'type' => 'error',
					'message' => 'user.auth.error'
				));

				return $this->redirect($this->generateUrl('user_connect_orga'));
			}
		}

		return array(
			'form' => $form->createView()
		);
	}

	/**
	 * @Route("/user/disconnect", name="user_disconnect")
	 * @Template()
	 */
	public function disconnectAction()
	{
		if (! $this->getUser() instanceof User && ! $this->getUser() instanceof Organization) {
			return $this->redirect($this->generateUrl('homepage'));
		}

		$this->get('session')->set('orga', null);
		$this->get('session')->set('user', null);
		$this->get('session')->clear();
		$this->get('session')->getFlashBag()->set('success', 'Vous êtes bien déconnecté');

		require __DIR__.'/../Resources/lib/phpCAS/CAS.php';

		\phpCAS::client('1.0', 'cas.utt.fr', 443, '/cas/', false);
		\phpCAS::logoutWithRedirectService($this->generateUrl('homepage'));

		return new Response();
	}


	/**
	 * Connect a user using his login
	 *
	 * @param string $login
	 * @param bool $useLdap
	 * @return bool|User|object
	 */
	protected function connectUser($login, $useLdap = true)
	{
		if (empty($login) || ! is_string($login)) {
			return false;
		}

		$em = $this->getDoctrine()->getManager();

		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		// If the user can't be loaded from database, we use LDAP and update database
		if (! $user && $useLdap) {
			$imagine = new Imagine();

			/** @var $ldap LdapManager */
			$ldap = $this->get('etu.user.ldap');

			$ldapUser = $ldap->getUser($login);

			// Resize photo
			try {
				$image = $imagine->open('http://local-sig.utt.fr/Pub/trombi/individu/'.$ldapUser->getStudentId().'.jpg');

				$image->copy()
					->thumbnail(new Box(200, 200), Image::THUMBNAIL_OUTBOUND)
					->save(__DIR__.'/../../../../../web/photos/'.$ldapUser->getLogin().'.jpg');

				$avatar = $ldapUser->getLogin().'.jpg';
			} catch (InvalidArgumentException $e) {
				$avatar = 'default-avatar.png';
			}

			$user = new User();
			$user->setAvatar($avatar);
			$user->setLogin($ldapUser->getLogin());
			$user->setFullName($ldapUser->getFullName());
			$user->setFirstName($ldapUser->getFirstName());
			$user->setLastName($ldapUser->getLastName());
			$user->setFiliere($ldapUser->getFiliere());
			$user->setFormation(ucfirst(strtolower($ldapUser->getFormation())));
			$user->setNiveau($ldapUser->getNiveau());
			$user->setMail($ldapUser->getMail());
			$user->setPhoneNumber($ldapUser->getPhoneNumber());
			$user->setRoom($ldapUser->getRoom());
			$user->setStudentId($ldapUser->getStudentId());
			$user->setTitle($ldapUser->getTitle());
			$user->setLdapInformations($ldapUser);
			$user->setIsStudent(true);
			$user->setKeepActive(false);

			$em->persist($user);
			$em->flush();
		}

		return $user;
	}
}

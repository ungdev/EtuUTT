<?php

namespace Etu\Core\UserBundle\Controller;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\Session;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Ldap\LdapManager;

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
		$login = \phpCAS::getUser();

		$em = $this->getDoctrine()->getManager();

		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		// If the user can't be loaded from database, we use LDAP and update database
		if (! $user) {
			$imagine = new Imagine();

			$ldap = new LdapManager(
				$this->container->getParameter('etu.ldap.host'),
				$this->container->getParameter('etu.ldap.port')
			);

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

		$this->get('session')->set('user', $user->getId());
		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'user.auth.confirm'
		));

		return $this->redirect($this->generateUrl('homepage'));
	}

	/**
	 * @Route("/user/disconnect", name="user_disconnect")
	 * @Template()
	 */
	public function disconnectAction()
	{
		/** @var Session $session */
		$session = $this->get('session');

		$this->get('session')->set('user', null);
		$this->get('session')->getFlashBag()->set('success', 'Vous êtes bien déconnecté');

		require __DIR__.'/../Resources/lib/phpCAS/CAS.php';

		\phpCAS::client('1.0', 'cas.utt.fr', 443, '/cas/', false);
		\phpCAS::logoutWithRedirectService($this->generateUrl('homepage'));

		return new Response();
	}
}

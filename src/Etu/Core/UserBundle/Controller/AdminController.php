<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\CoreBundle\Framework\Definition\Permission;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Controller\AuthController;
use Etu\Core\UserBundle\Entity\Session;

use Etu\Core\UserBundle\Model\Badge;
use Etu\Core\UserBundle\Model\BadgesManager;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

/**
 * @Route("/admin")
 */
class AdminController extends Controller
{
	/**
	 * @Route("/users/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="admin_users_index")
	 * @Template()
	 */
	public function usersIndexAction($page = 1)
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

		$user = new User();
		$search = false;
		$users = array();

		$form = $this->createFormBuilder($user)
			->add('fullName', null, array('required' => false))
			->add('studentId', null, array('required' => false))
			->add('phoneNumber', null, array('required' => false))
			->add('uvs', null, array('required' => false))
			->add('filiere', 'choice', array('choices' => User::$branches, 'required' => false))
			->add('niveau', 'choice', array('choices' => User::$levels, 'required' => false))
			->add('personnalMail', null, array('required' => false))
			->getForm();

		if ($form->bind($this->getRequest())->isValid()) {
			$search = true;

			/** @var $em EntityManager */
			$em = $this->getDoctrine()->getManager();

			/** @var $users QueryBuilder */
			$users = $em->createQueryBuilder()
				->select('u')
				->from('EtuUserBundle:User', 'u')
				->where('u.isStudent = 1')
				->orderBy('u.lastName');

			if (! $user->getFullName() && ! $user->getStudentId() && ! $user->getPhoneNumber() && ! $user->getUvs() &&
				! $user->getFiliere() && ! $user->getNiveau() && ! $user->getPersonnalMail())
			{
				return $this->redirect($this->generateUrl('trombi_index'));
			}

			if ($user->getFullName()) {
				$where = 'u.login = :login ';
				$users->setParameter('login', $user->getFullName());

				$where .= 'OR u.surnom LIKE :surnom OR (';
				$users->setParameter('surnom', '%'.$user->getFullName().'%');

				$terms = explode(' ', $user->getFullName());

				foreach ($terms as $key => $term) {
					$where .= 'u.fullName LIKE :name_'.$key.' AND ';
					$users->setParameter('name_'.$key, '%'.$term.'%');
				}

				$where = substr($where, 0, -5).')';

				$users->andWhere($where);
			}

			if ($user->getStudentId()) {
				$users->andWhere('u.studentId = :id')
					->setParameter('id', $user->getStudentId());
			}

			if ($user->getPhoneNumber()) {
				$users->andWhere('u.phoneNumber = :phone')
					->setParameter('phone', $user->getPhoneNumber());
			}

			if ($user->getUvs()) {
				$uvs = array_map('trim', explode(',', $user->getUvs()));

				foreach ($uvs as $key => $uv) {
					$users->andWhere('u.uvs LIKE :uv'.$key)
						->setParameter('uv'.$key, '%'.$uv.'%');
				}
			}

			if ($user->getFiliere() && $user->getNiveau()) {
				$users->andWhere('u.niveau = :niveau')
					->setParameter('niveau', $user->getFiliere().$user->getNiveau());
			} elseif ($user->getFiliere()) {
				$users->andWhere('u.niveau LIKE :niveau')
					->setParameter('niveau', $user->getFiliere().'%');
			} elseif ($user->getNiveau()) {
				$users->andWhere('u.niveau LIKE :niveau')
					->setParameter('niveau', '%'.$user->getNiveau());
			}

			if ($user->getPersonnalMail()) {
				$users->andWhere('u.personnalMail = :personnalMail')
					->setParameter('personnalMail', $user->getPersonnalMail());
			}

			$users = $this->get('knp_paginator')->paginate($users->getQuery(), $page, 20);
		}

		return array(
			'form' => $form->createView(),
			'search' => $search,
			'pagination' => $users
		);
	}


	/**
	 * @Route("/user/{login}/edit", name="admin_user_edit")
	 * @Template()
	 */
	public function userEditAction($login)
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $user User */
		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		if (! $user) {
			throw $this->createNotFoundException('Login "'.$login.'" not found');
		}

		$privacyChoice = array(
			'choices' => array(
				User::PRIVACY_PUBLIC => 'user.privacy.public',
				User::PRIVACY_PRIVATE => 'user.privacy.private',
			),
			'attr' => array(
				'class' => 'profileEdit-privacy-select'
			),
			'required' => false
		);

		$form = $this->createFormBuilder($user)
			->add('phoneNumber', null, array('required' => false))
			->add('phoneNumberPrivacy', 'choice', $privacyChoice)
			->add('sex', 'choice', array('choices' => array(
				User::SEX_MALE => 'base.user.sex.male',
				User::SEX_FEMALE => 'base.user.sex.female'
			), 'required' => false))
			->add('sexPrivacy', 'choice', $privacyChoice)
			->add('nationality', null, array('required' => false))
			->add('nationalityPrivacy', 'choice', $privacyChoice)
			->add('address', null, array('required' => false))
			->add('addressPrivacy', 'choice', $privacyChoice)
			->add('postalCode', null, array('required' => false))
			->add('postalCodePrivacy', 'choice', $privacyChoice)
			->add('city', null, array('required' => false))
			->add('cityPrivacy', 'choice', $privacyChoice)
			->add('country', null, array('required' => false))
			->add('countryPrivacy', 'choice', $privacyChoice)
			->add('birthday', 'birthday', array(
				'widget' => 'single_text',
				'format' => 'dd/MM/yyyy',
				'required' => false
			))
			->add('birthdayPrivacy', 'choice', $privacyChoice)
			->add('birthdayDisplayOnlyAge', null, array('required' => false))
			->add('personnalMail', null, array('required' => false))
			->add('personnalMailPrivacy', 'choice', $privacyChoice)
			->add('website', null, array('required' => false))
			->add('facebook', null, array('required' => false))
			->add('twitter', null, array('required' => false))
			->add('linkedin', null, array('required' => false))
			->add('viadeo', null, array('required' => false))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em = $this->getDoctrine()->getManager();

			if ($user->getProfileCompletion() == 100) {
				BadgesManager::userAddBadge($user, 'profile_completed');
			} else {
				BadgesManager::userRemoveBadge($user, 'profile_completed');
			}

			if ($user->getTrombiCompletion() == 100) {
				BadgesManager::userAddBadge($user, 'trombi_completed');
			} else {
				BadgesManager::userRemoveBadge($user, 'trombi_completed');
			}

			BadgesManager::userPersistBadges($user);
			$em->persist($user);
			$em->flush();

			$logger = $this->get('monolog.logger.admin');
			$logger->info('`'.$this->getUser()->getLogin().'` update profil of `'.$user->getLogin().'`');

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.admin.userEdit.confirm'
			));

			return $this->redirect($this->generateUrl('user_view', array('login' => $user->getLogin())));
		}

		// Avatar lightbox
		$avatarForm = $this->createFormBuilder($user)
			->add('file', 'file')
			->getForm();

		return array(
			'user' => $user,
			'form' => $form->createView(),
			'avatarForm' => $avatarForm->createView()
		);
	}


	/**
	 * @Route("/users/permissions", name="admin_user_roles_list")
	 * @Template()
	 */
	public function userRolesListAction()
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_ROLES');

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$qb = $em->getRepository('EtuUserBundle:User')->createQueryBuilder('u');
		$qb->where('u.storedRoles != \'a:0:{}\'');
		$users = $qb->getQuery()->getResult();

		// Retrieve role list
		$predefinedRoles = ['ROLE_READONLY', 'ROLE_BANNED', 'ROLE_USER', 'ROLE_ORGA', 'ROLE_STUDENT', 'ROLE_STAFFUTT', 'ROLE_EXTERNAL', 'ROLE_CAS'];
		$hierarchy = $this->getParameter('security.role_hierarchy.roles');
		$roles = array_keys($hierarchy);
		$roles = array_diff($roles, $predefinedRoles);

		return array(
			'users' => $users,
			'hierarchy' => $hierarchy
		);
	}

	/**
	 * @Route("/user/{login}/permissions", name="admin_user_roles")
	 * @Template()
	 */
	public function userRolesAction($login)
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_ROLES');

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $user User */
		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		if (! $user) {
			throw $this->createNotFoundException('Login "'.$login.'" not found');
		}

		// Get 'from' to choose the right back button
		$from = null;
		if (in_array($this->getRequest()->get('from'), array('profile', 'admin', 'organizations', 'badges', 'schedule'))) {
			$from = $this->getRequest()->get('from');
		}

		// Retrieve role list
		$predefinedRoles = ['ROLE_READONLY', 'ROLE_BANNED', 'ROLE_USER', 'ROLE_ORGA', 'ROLE_STUDENT', 'ROLE_STAFFUTT', 'ROLE_EXTERNAL', 'ROLE_CAS'];
		$hierarchy = $this->getParameter('security.role_hierarchy.roles');
		$roles = array_keys($hierarchy);
		$roles = array_diff($roles, $predefinedRoles);

		// See what role user already have
		$storedRoles = $user->getStoredRoles();
		$roleArray = [];
		foreach ($roles as $role) {
			$roleArray[] = [
				'role' => $role,
				'checked' => (in_array($role, $storedRoles)),
				'subroles' => $hierarchy[$role]
			];
		}

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST') {
			$roles = [];
			$postedRoles = $request->get('role');
			if(empty($postedRoles)) {
				$postedRoles = [];
			}

			foreach ($postedRoles as $key => $value) {
				if(isset($hierarchy[$key])) {
					$roles[] = $key;
				}
			}
			$user->setStoredRoles($roles);
			$em->persist($user);
			$em->flush();

			$logger = $this->get('monolog.logger.admin');
			$logger->warn('`'.$this->getUser()->getLogin().'` update roles of `'.$user->getLogin().'`');

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.admin.userRoles.confirm'
			));

			return $this->redirect($this->generateUrl('admin_user_roles', array('login' => $user->getLogin(), 'from' => $from)));
		}

		return array(
			'user' => $user,
			'roles' => $roleArray,
			'fromVar' => $from
		);
	}


	/**
	 * @Route("/user/{login}/avatar", name="admin_user_edit_avatar", options={"expose"=true})
	 * @Template()
	 */
	public function userAvatarAction($login)
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $user User */
		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		if (! $user) {
			throw $this->createNotFoundException('Login "'.$login.'" not found');
		}

		$form = $this->createFormBuilder($user)
			->add('file', 'file')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em = $this->getDoctrine()->getManager();

			$user->upload();

			$em->persist($user);
			$em->flush();

			$logger = $this->get('monolog.logger.admin');
			$logger->info('`'.$this->getUser()->getLogin().'` update avatar of `'.$user->getLogin().'`');

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.admin.userEdit.confirm'
			));

			return $this->redirect($this->generateUrl('user_view', array('login' => $user->getLogin())));
		}

		return array(
			'user' => $user,
			'form' => $form->createView()
		);
	}


	/**
	 * @Route("/user/{login}/toggle-readonly", name="admin_user_toggle_readonly")
	 * @Template()
	 */
	public function userReadOnlyAction($login)
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $user User */
		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		if (! $user) {
			throw $this->createNotFoundException('Login "'.$login.'" not found');
		}

		if (! $user->isReadOnly()) {
			$expiration = new \DateTime();
			$expiration->add(new \DateInterval('P1Y'));
			$user->setReadOnlyExpirationDate($expiration);

			$logger = $this->get('monolog.logger.admin');
			$logger->warn('`'.$this->getUser()->getLogin().'` put `'.$user->getLogin().'` on read only ');

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.admin.userReadOnly.confirm_set'
			));
		} else {
			$user->setReadOnlyExpirationDate(null);

			$logger = $this->get('monolog.logger.admin');
			$logger->warn('`'.$this->getUser()->getLogin().'` remove `'.$user->getLogin().'` from read only');

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.admin.userReadOnly.confirm_unset'
			));
		}

		$em->persist($user);
		$em->flush();

		return $this->redirect($this->generateUrl('user_view', array('login' => $user->getLogin())).'?from=admin');
	}


	/**
	 * @Route("/user/create", name="admin_user_create")
	 * @Template()
	 */
	public function userCreateAction()
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

		/** @var $user User */
		$user = new User();
		$user->setIsStudent(true);
		$user->setIsInLDAP(false);

		$privacyChoice = array(
			'choices' => array(
				User::PRIVACY_PUBLIC => 'user.privacy.public',
				User::PRIVACY_PRIVATE => 'user.privacy.private',
			),
			'attr' => array(
				'class' => 'profileEdit-privacy-select'
			),
			'required' => false
		);

		$form = $this->createFormBuilder($user)
			->add('login', null, array('required' => true))
			->add('fullName', null, array('required' => true))
			->add('mail', null, array('required' => true))
			->add('password', null, array('required' => true))
			->add('studentId', null, array('required' => false))
			->add('phoneNumber', null, array('required' => false))
			->add('phoneNumberPrivacy', 'choice', $privacyChoice)
			->add('sex', 'choice', array('choices' => array(
				User::SEX_MALE => 'base.user.sex.male',
				User::SEX_FEMALE => 'base.user.sex.female'
			), 'required' => false))
			->add('sexPrivacy', 'choice', $privacyChoice)
			->add('nationality', null, array('required' => false))
			->add('nationalityPrivacy', 'choice', $privacyChoice)
			->add('address', null, array('required' => false))
			->add('addressPrivacy', 'choice', $privacyChoice)
			->add('postalCode', null, array('required' => false))
			->add('postalCodePrivacy', 'choice', $privacyChoice)
			->add('city', null, array('required' => false))
			->add('cityPrivacy', 'choice', $privacyChoice)
			->add('country', null, array('required' => false))
			->add('countryPrivacy', 'choice', $privacyChoice)
			->add('birthday', 'birthday', array(
				'widget' => 'single_text',
				'format' => 'dd/MM/yyyy',
				'required' => false
			))
			->add('birthdayPrivacy', 'choice', $privacyChoice)
			->add('birthdayDisplayOnlyAge', null, array('required' => false))
			->add('personnalMail', null, array('required' => false))
			->add('personnalMailPrivacy', 'choice', $privacyChoice)
			->add('website', null, array('required' => false))
			->add('facebook', null, array('required' => false))
			->add('twitter', null, array('required' => false))
			->add('linkedin', null, array('required' => false))
			->add('viadeo', null, array('required' => false))
			->add('isStudent', null, array('required' => false))
			->add('isStaffUTT', null, array('required' => false))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em = $this->getDoctrine()->getManager();

			if ($user->getProfileCompletion() == 100) {
				BadgesManager::userAddBadge($user, 'profile_completed');
			} else {
				BadgesManager::userRemoveBadge($user, 'profile_completed');
			}

			if ($user->getTrombiCompletion() == 100) {
				BadgesManager::userAddBadge($user, 'trombi_completed');
			} else {
				BadgesManager::userRemoveBadge($user, 'trombi_completed');
			}

			BadgesManager::userPersistBadges($user);
			$user->setPassword($this->get('security.password_encoder')->encodePassword($user,$user->getPassword()));
			$user->setIsInLDAP(false);

			$em->persist($user);
			$em->flush();

			$logger = $this->get('monolog.logger.admin');
			$logger->info('`'.$this->getUser()->getLogin().'` create an user `'.$user->getLogin().'`');

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.admin.userCreate.confirm'
			));

			return $this->redirect($this->generateUrl('user_view', array('login' => $user->getLogin())));
		}

		return array(
			'user' => $user,
			'form' => $form->createView()
		);
	}


	/**
	 * @Route("/user/{login}/delete/{confirm}", defaults={"confirm" = ""}, name="admin_user_delete")
	 * @Template()
	 */
	public function userDeleteAction($login, $confirm = '')
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $user User */
		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		if (! $user) {
			throw $this->createNotFoundException('Login "'.$login.'" not found');
		}

		if ($confirm == 'confirm') {
			$user->setDeletedAt(new \DateTime());

			$em->persist($user);
			$em->flush();

			$logger = $this->get('monolog.logger.admin');
			$logger->warn('`'.$this->getUser()->getLogin().'` delete an user `'.$user->getLogin().'`');

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.admin.userDelete.confirm'
			));

			return $this->redirect($this->generateUrl('admin_users_index'));
		}

		return array(
			'user' => $user,
		);
	}

	/**
	 * @Route("/orgas/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="admin_orgas_index")
	 * @Template()
	 */
	public function orgasIndexAction($page = 1)
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$orgas = $em->createQueryBuilder()
			->select('o, p')
			->from('EtuUserBundle:Organization', 'o')
			->leftJoin('o.president', 'p')
			->orderBy('o.name')
			->getQuery();

		$orgas = $this->get('knp_paginator')->paginate($orgas, $page, 20);

		return array(
			'pagination' => $orgas
		);
	}

	/**
	 * @Route("/orgas/create", name="admin_orgas_create")
	 * @Template()
	 */
	public function orgasCreateAction()
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

		/** @var $orga Organization */
		$orga = new Organization();

		$form = $this->createFormBuilder($orga)
			->add('login', null, array('required' => true))
			->add('name', null, array('required' => true))
			->add('descriptionShort', 'textarea', array('required' => true))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em = $this->getDoctrine()->getManager();

			$em->persist($orga);
			$em->flush();

			$logger = $this->get('monolog.logger.admin');
			$logger->info('`'.$this->getUser()->getLogin().'` create organization `'.$orga->getLogin().'`');

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.admin.orgasCreate.confirm'
			));

			return $this->redirect($this->generateUrl('admin_orgas_index'));
		}

		return array(
			'orga' => $orga,
			'form' => $form->createView()
		);
	}

	/**
	 * @Route("/orgas/{login}/delete", name="admin_orgas_delete")
	 * @Template()
	 */
	public function orgasDeleteAction($login)
	{
		$this->denyAccessUnlessGranted('ROLE_CORE_ADMIN_PROFIL');

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $orga Organization */
		$orga = $em->getRepository('EtuUserBundle:Organization')->findOneBy(array('login' => $login));

		if (! $orga) {
			throw $this->createNotFoundException(sprintf('Login %s not found', $login));
		}

		/** @var $members Members of the organisation to be removed */
		$members = $em->getRepository('EtuUserBundle:Member')
				->findBy(array('organization' => $orga));

		foreach ($members as $member) {
			$member->setDeletedAt(new \DateTime());
			$em->persist($member);
		}

		$orga->setDeletedAt(new \DateTime());

		$em->persist($orga);
		$em->flush();

		$logger = $this->get('monolog.logger.admin');
		$logger->warn('`'.$this->getUser()->getLogin().'` delete organization `'.$orga->getLogin().'`');

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'user.admin.orgasDelete.confirm'
		));

		return $this->redirect($this->generateUrl('admin_orgas_index'));
	}


	/**
	 * @Route("/log-as", name="admin_log-as")
	 * @Template()
	 */
	public function logAsAction()
	{
		$this->denyAccessUnlessGranted('ROLE_ALLOWED_TO_SWITCH');

		$em = $this->getDoctrine()->getManager();
		$request = $this->getRequest();

		if ($request->getMethod() == 'POST') {
			if(!empty($request->get('orga'))) {
				$orga = $em->createQueryBuilder()
					->select('o')
					->from('EtuUserBundle:Organization', 'o')
					->where('o.name = :input')
					->orWhere('o.login = :input')
					->setParameter('input', $request->get('orga'))
					->setMaxResults(1)
					->getQuery()
					->getOneOrNullResult();

				if (! $orga) {
					$this->get('session')->getFlashBag()->set('message', array(
						'type' => 'error',
						'message' => 'user.admin.logAs.orga_not_found'
					));
				}
				else {
					$logger = $this->get('monolog.logger.admin');
					$logger->warn('`'.$this->getUser()->getLogin().'` login as organization `'.$orga->getLogin().'`');

					$this->get('session')->getFlashBag()->set('message', array(
						'type' => 'success',
						'message' => 'user.auth.connect.confirm'
					));
					return $this->redirect($this->generateUrl('homepage', ['_switch_user' => $orga->getLogin() ]));
				}
			}
			else {
				$user = $em->createQueryBuilder()
					->select('u')
					->from('EtuUserBundle:User', 'u')
					->where('u.fullName = :input')
					->orWhere('u.login = :input')
					->setParameter('input', $request->get('user'))
					->setMaxResults(1)
					->getQuery()
					->getOneOrNullResult();

				if (! $user) {
					$this->get('session')->getFlashBag()->set('message', array(
						'type' => 'error',
						'message' => 'user.admin.logAs.user_not_found'
					));
				}
				else {
					$logger = $this->get('monolog.logger.admin');
					$logger->warn('`'.$this->getUser()->getLogin().'` login as an user `'.$user->getLogin().'`');

					$this->get('session')->getFlashBag()->set('message', array(
						'type' => 'success',
						'message' => 'user.auth.connect.confirm'
					));
					return $this->redirect($this->generateUrl('homepage', ['_switch_user' => $user->getLogin() ]));
				}
			}
		}
	}


	/**
	 * @Route("/log-as/back", name="admin_log-as_back")
	 * @Template()
	 */
	public function logAsBackAction()
	{
		$this->denyAccessUnlessGranted('ROLE_PREVIOUS_ADMIN');

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'user.admin.logAs.welcomeBack'
		));

		return $this->redirect($this->generateUrl('homepage', ['_switch_user' => '_exit' ]));
	}
}

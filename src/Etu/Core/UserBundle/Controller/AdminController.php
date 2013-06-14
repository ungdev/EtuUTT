<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\CoreBundle\Framework\Definition\Controller;

use Etu\Core\CoreBundle\Framework\Definition\Permission;
use Etu\Core\UserBundle\Entity\Organization;
use Etu\Core\UserBundle\Entity\User;
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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

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
				$users->andWhere('u.fullName LIKE :fullName')
					->setParameter('fullName', '%'.str_replace(' ', '%', $user->getFullName()).'%');
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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

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
			->add('adress', null, array('required' => false))
			->add('adressPrivacy', 'choice', $privacyChoice)
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

			if ($user->getProfileCompletion() == 100 && ! $user->hasBadge('profile_completed')) {
				$user->addBadge('profile_completed');
			}

			if ($user->getProfileCompletion() != 100 && $user->hasBadge('profile_completed')) {
				$user->removeBadge('profile_completed');
			}

			$em->persist($user);
			$em->flush();

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
	 * @Route("/user/{login}/permissions", name="admin_user_permissions")
	 * @Template()
	 */
	public function userPermissionsAction($login)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $user User */
		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		if (! $user) {
			throw $this->createNotFoundException('Login "'.$login.'" not found');
		}

		/** @var Permission[] $availablePermissions */
		$availablePermissions = $this->getKernel()->getAvailablePermissions()->toArray();

		$permissions1 = array();
		$permissions2 = array();

		$i = floor(count($availablePermissions) / 2);

		foreach ($availablePermissions as $permission) {
			if ($user->hasPermission($permission->getName(), $permission->getDefaultEnabled())) {
				$permission = array('definition' => $permission, 'checked' => true);
			} else {
				$permission = array('definition' => $permission, 'checked' => false);
			}

			if ($i == 0) {
				$permissions1[] = $permission;
			} else {
				$permissions2[] = $permission;
				$i--;
			}
		}

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $request->get('sent')) {
			if ($request->get('isAdmin')) {
				$user->setIsAdmin(true);
			} elseif ($permissions = $request->get('permissions')) {
				$user->setIsAdmin(false);

				$userClassicPermissions = array();
				$userRemovedPermissions = array();

				foreach ($availablePermissions as $permission) {
					if ($permission->getDefaultEnabled()) {
						$userRemovedPermissions[$permission->getName()] = $permission;
					}
				}

				foreach ($permissions as $permission => $value) {
					if (isset($availablePermissions[$permission])) {
						/** @var Permission $permission */
						$permission = $availablePermissions[$permission];

						if ($permission->getDefaultEnabled()) {
							unset($userRemovedPermissions[$permission->getName()]);
						} else {
							$userClassicPermissions[$permission->getName()] = $permission;
						}
					}
				}

				foreach ($userClassicPermissions as $key => $permission) {
					unset($userClassicPermissions[$key]);
					$userClassicPermissions[] = $permission->getName();
				}

				foreach ($userRemovedPermissions as $key => $permission) {
					unset($userRemovedPermissions[$key]);
					$userRemovedPermissions[] = $permission->getName();
				}

				$user->setPermissions($userClassicPermissions);
				$user->setRemovedPermissions($userRemovedPermissions);
			} else {
				$userClassicPermissions = array();
				$userRemovedPermissions = array();

				foreach ($availablePermissions as $permission) {
					if ($permission->getDefaultEnabled()) {
						$userRemovedPermissions[$permission->getName()] = $permission;
					}
				}

				foreach ($userClassicPermissions as $key => $permission) {
					unset($userClassicPermissions[$key]);
					$userClassicPermissions[] = $permission->getName();
				}

				foreach ($userRemovedPermissions as $key => $permission) {
					unset($userRemovedPermissions[$key]);
					$userRemovedPermissions[] = $permission->getName();
				}

				$user->setPermissions($userClassicPermissions);
				$user->setRemovedPermissions($userRemovedPermissions);
			}

			$em->persist($user);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.admin.userPermissions.confirm'
			));

			return $this->redirect($this->generateUrl('admin_user_permissions', array('login' => $user->getLogin())));
		}

		return array(
			'user' => $user,
			'permissions1' => $permissions1,
			'permissions2' => $permissions2
		);
	}


	/**
	 * @Route("/user/{login}/avatar", name="admin_user_edit_avatar", options={"expose"=true})
	 * @Template()
	 */
	public function userAvatarAction($login)
	{
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $user User */
		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		if (! $user) {
			throw $this->createNotFoundException('Login "'.$login.'" not found');
		}

		if (! $user->getIsReadOnly()) {
			$user->setIsReadOnly(true)->setReadOnlyPeriod('42 days');

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.admin.userReadOnly.confirm_set'
			));
		} else {
			$user->setIsReadOnly(false);

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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $user User */
		$user = new User();

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
			->add('adress', null, array('required' => false))
			->add('adressPrivacy', 'choice', $privacyChoice)
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

			if ($user->getProfileCompletion() == 100 && ! $user->hasBadge('profile_completed')) {
				$user->addBadge('profile_completed');
			}

			if ($user->getProfileCompletion() != 100 && $user->hasBadge('profile_completed')) {
				$user->removeBadge('profile_completed');
			}

			$user->setPassword($this->get('etu.user.crypting')->encrypt($user->getPassword()));
			$user->setKeepActive(true);

			$em->persist($user);
			$em->flush();

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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $user User */
		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		if (! $user) {
			throw $this->createNotFoundException('Login "'.$login.'" not found');
		}

		if ($confirm == 'confirm') {
			$user->setIsDeleted(true);

			$em->persist($user);
			$em->flush();

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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		$orgas = $em->createQueryBuilder()
			->select('o, p')
			->from('EtuUserBundle:Organization', 'o')
			->leftJoin('o.president', 'p')
			->where('o.deleted = 0')
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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

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
		if (! $this->getUserLayer()->isUser() || ! $this->getUser()->getIsAdmin()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $orga Organization */
		$orga = $em->getRepository('EtuUserBundle:Organization')->findOneBy(array('login' => $login));

		if (! $orga) {
			throw $this->createNotFoundException(sprintf('Login %s not found', $login));
		}

		$orga->setDeleted(true);

		$em->persist($orga);
		$em->flush();

		$this->get('session')->getFlashBag()->set('message', array(
			'type' => 'success',
			'message' => 'user.admin.orgasDelete.confirm'
		));

		return $this->redirect($this->generateUrl('admin_orgas_index'));
	}
}

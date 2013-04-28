<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\CoreBundle\Framework\Definition\Controller;

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

			$users = $this->get('knp_paginator')->paginate($users->getQuery(), $page, 10);
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
				'message' => 'admin.user.edit.confirm'
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
				'message' => 'admin.user.edit.confirm'
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
				'message' => 'admin.user.readonly.confirm_set'
			));
		} else {
			$user->setIsReadOnly(false);

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'admin.user.readonly.confirm_unset'
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

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

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
				'message' => 'admin.user.create.confirm'
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
				'message' => 'admin.user.delete.confirm'
			));

			return $this->redirect($this->generateUrl('admin_users_index'));
		}

		return array(
			'user' => $user,
		);
	}
}

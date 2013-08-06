<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;

use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\Course;
use Etu\Core\UserBundle\Entity\Member;
use Etu\Core\UserBundle\Entity\User;
use Etu\Core\UserBundle\Model\BadgesManager;
use Etu\Core\UserBundle\Model\CountriesManager;
use Etu\Core\UserBundle\Schedule\Helper\ScheduleBuilder;

use Symfony\Component\Form\FormError;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

class ProfileController extends Controller
{
	/**
	 * @Route("/user/profile", name="user_profile")
	 * @Template()
	 */
	public function profileAction()
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		return array();
	}

	/**
	 * @Route("/user/profile/edit", name="user_profile_edit")
	 * @Template()
	 */
	public function profileEditAction()
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $user User */
		$user = $this->getUser();

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
			->add('country', 'choice', array('choices' => CountriesManager::getCountriesList(), 'required' => false))
			->add('countryPrivacy', 'choice', $privacyChoice)
			->add('birthday', 'birthday_picker', array('required' => false))
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

			BadgesManager::userPersistBadges($user);
			$em->persist($user);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.profile.profileEdit.confirm'
			));

			return $this->redirect($this->generateUrl('user_profile_edit'));
		}

		// Avatar lightbox
		$avatarForm = $this->createFormBuilder($user)
			->add('file', 'file')
			->getForm();

		return array(
			'form' => $form->createView(),
			'avatarForm' => $avatarForm->createView()
		);
	}

	/**
	 * @Route("/user/profile/avatar", name="user_profile_avatar")
	 * @Template()
	 */
	public function profileAvatarAction()
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $user User */
		$user = $this->getUser();

		$form = $this->createFormBuilder($user)
			->add('file', 'file')
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em = $this->getDoctrine()->getManager();

			$file = $user->upload();

			$em->persist($user);
			$em->flush();

			return array(
				'result' => 'success',
				'data' => json_encode(array(
					'filename' => '/photos/'.$file
				))
			);
		}

		/** @var $error FormError[] */
		$error = $form->get('file')->getErrors();

		return array(
			'result' => 'error',
			'data' => json_encode(array(
				'message' => (isset($error[0])) ? $error[0]->getMessage() : 'An error occured'
			))
		);
	}

	/**
	 * @Route("/user/trombi/edit", name="user_trombi_edit")
	 * @Template()
	 */
	public function trombiEditAction()
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $user User */
		$user = $this->getUser();

		$form = $this->createFormBuilder($user)
			->add('surnom', null, array('required' => false))
			->add('jadis', null, array('required' => false, 'attr' => array('class' => 'trombi-textarea')))
			->add('passions', null, array('required' => false, 'attr' => array('class' => 'trombi-textarea')))
			->getForm();

		$request = $this->getRequest();

		if ($request->getMethod() == 'POST' && $form->bind($request)->isValid()) {
			$em = $this->getDoctrine()->getManager();

			if ($user->getTrombiCompletion() == 100) {
				BadgesManager::userAddBadge($user, 'trombi_completed');
			} else {
				BadgesManager::userRemoveBadge($user, 'trombi_completed');
			}

			BadgesManager::userPersistBadges($user);
			$em->persist($user);
			$em->flush();

			$this->get('session')->getFlashBag()->set('message', array(
				'type' => 'success',
				'message' => 'user.profile.trombiEdit.confirm'
			));

			return $this->redirect($this->generateUrl('user_profile'));
		}

		return array(
			'form' => $form->createView()
		);
	}

	/**
	 * @Route("/user/{login}", name="user_view")
	 * @Template()
	 */
	public function viewAction($login)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		if ($login != $this->getUser()->getLogin()) {
			/** @var $em EntityManager */
			$em = $this->getDoctrine()->getManager();

			/** @var $user User */
			$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

			if (! $user) {
				throw $this->createNotFoundException('Login "'.$login.'" not found');
			}
		} else {
			$user = $this->getUser();
		}

		$from = null;

		if (in_array($this->getRequest()->get('from'), array('search', 'profile', 'trombi', 'admin'))) {
			$from = $this->getRequest()->get('from');
		}

		return array(
			'user' => $user,
			'from' => $from
		);
	}

	/**
	 * @Route("/user/{login}/organizations", name="user_organizations")
	 * @Template()
	 */
	public function organizationsAction($login)
	{
		if (! $this->getUserLayer()->isUser()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $user User */
		$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

		if (! $user) {
			throw $this->createNotFoundException('Login "'.$login.'" not found');
		}

		/** @var $memberships Member[] */
		$memberships = $em->createQueryBuilder()
			->select('m, u, o')
			->from('EtuUserBundle:Member', 'm')
			->leftJoin('m.user', 'u')
			->leftJoin('m.organization', 'o')
			->where('u.login = :login')
			->setParameter('login', $user->getLogin())
			->getQuery()
			->getResult();

		$from = null;

		if (in_array($this->getRequest()->get('from'), array('search', 'profile', 'trombi', 'admin'))) {
			$from = $this->getRequest()->get('from');
		}

		return array(
			'user' => $user,
			'memberships' => $memberships,
			'from' => $from
		);
	}

	/**
	 * @Route("/user/{login}/schedule", name="user_view_schedule")
	 * @Template()
	 */
	public function scheduleAction($login)
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		if ($login != $this->getUser()->getLogin()) {

			/** @var $user User */
			$user = $em->getRepository('EtuUserBundle:User')->findOneBy(array('login' => $login));

			if (! $user) {
				throw $this->createNotFoundException('Login "'.$login.'" not found');
			}
		} else {
			$user = $this->getUser();
		}

		$from = null;

		if (in_array($this->getRequest()->get('from'), array('search', 'profile', 'trombi', 'admin'))) {
			$from = $this->getRequest()->get('from');
		}

		/** @var $courses Course[] */
		$courses = $em->getRepository('EtuUserBundle:Course')->findByUser($user);

		// Builder to create the schedule
		$builder = new ScheduleBuilder();

		foreach ($courses as $course) {
			$builder->addCourse($course);
		}

		return array(
			'courses' => $builder->build(),
			'user' => $user,
			'from' => $from
		);
	}
}

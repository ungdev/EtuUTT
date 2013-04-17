<?php

namespace Etu\Module\TrombiBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\CoreBundle\Framework\Definition\Controller;

// Import annotations
use Etu\Core\UserBundle\Entity\User;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

class MainController extends Controller
{
	/**
	 * @Route("/trombi/{page}", defaults={"page" = 1}, requirements={"page" = "\d+"}, name="trombi_index")
	 * @Template()
	 */
	public function indexAction($page)
	{
		if (! $this->getUserLayer()->isStudent()) {
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
	 * @Route(
	 *      "/trombi/search",
	 *      defaults={"_format"="json"},
	 *      name="trombi_search",
	 *      options={"expose"=true}
	 * )
	 * @Template()
	 */
	public function searchAction()
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $users QueryBuilder */
		$users = $em->createQueryBuilder()
			->select('u')
			->from('EtuUserBundle:User', 'u');

		if ($search = $this->getRequest()->get('name')) {
			$users->andWhere('u.fullName LIKE :fullName')
				->setParameter('fullName', '%'.str_replace(' ', '%', urldecode($search)).'%');
		}

		if ($search = $this->getRequest()->get('id')) {
			$users->andWhere('u.studentId = :id')
				->setParameter('id', $search);
		}

		if ($search = $this->getRequest()->get('phone')) {
			$users->andWhere('u.phoneNumber = :phone')
				->setParameter('phone', $search);
		}

		if ($search = $this->getRequest()->get('uv')) {
			$users->andWhere('u.uvs LIKE :uv')
				->setParameter('uv', '%'.$search.'%');
		}

		if ($search = $this->getRequest()->get('branch')) {
			$users->andWhere('u.branch = :branch')
				->setParameter('branch', $search);
		}

		if ($search = $this->getRequest()->get('level')) {
			$users->andWhere('u.niveau = :level')
				->setParameter('niveau', $search);
		}

		if ($search = $this->getRequest()->get('personnalMail')) {
			$users->andWhere('u.personnalMail = :personnalMail')
				->setParameter('personnalMail', $search);
		}

		if ($search = $this->getRequest()->get('student')) {
			$users->andWhere('u.isStudent = :isStudent')
				->setParameter('isStudent', $search);
		}

		$users = $users->setMaxResults(1)->getQuery()->getArrayResult();

		// Privacy
		foreach ($users as &$user) {
			unset(
			$user['id'], $user['password'], $user['language'], $user['ldapInformations'],
			$user['keepActive'], $user['permissions'], $user['options']
			);

			if ($user['phoneNumberPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['phoneNumber']);
			}

			if ($user['sexPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['sex']);
			}

			if ($user['nationalityPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['nationality']);
			}

			if ($user['adressPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['adress']);
			}

			if ($user['postalCodePrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['postalCode']);
			}

			if ($user['cityPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['city']);
			}

			if ($user['countryPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['country']);
			}

			if ($user['birthdayPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['birthday']);
			}

			if ($user['personnalMailPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['personnalMail']);
			}
		}

		return new Response(json_encode($users));
	}
}

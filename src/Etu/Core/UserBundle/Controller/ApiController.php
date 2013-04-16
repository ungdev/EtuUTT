<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
use Doctrine\ORM\QueryBuilder;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Etu\Core\UserBundle\Entity\User;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api")
 */
class ApiController extends Controller
{
	/**
	 * @Route(
	 *      "/user/search",
	 *      defaults={"_format"="json"},
	 *      name="api_users_search",
	 *      options={"expose"=true}
	 * )
	 * @Template()
	 */
	public function searchAction()
	{
		if (! $this->getUserLayer()->isConnected()) {
			return $this->createAccessDeniedResponse();
		}

		if (! $this->getRequest()->get('term')) {
			$search = '';
		} else {
			$search = $this->getRequest()->get('term');
		}

		if (strlen($search) < 3) {
			throw new \RuntimeException('You must provide at least 3 characters.');
		}

		$search = '%'.str_replace(' ', '%', urldecode($search)).'%';

		/** @var $em EntityManager */
		$em = $this->getDoctrine()->getManager();

		/** @var $users User[] */
		$users = $em->createQueryBuilder()
			->select('u')
			->from('EtuUserBundle:User', 'u')
			->where('u.login LIKE :login')
			->orWhere('u.fullName LIKE :fullName')
			->setParameter('login', $search)
			->setParameter('fullName', $search)
			->setMaxResults(10)
			->getQuery()
			->getArrayResult();

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

	/**
	 * @Route(
	 *      "/user/search/complete",
	 *      defaults={"_format"="json"},
	 *      name="api_users_search_complete",
	 *      options={"expose"=true}
	 * )
	 * @Template()
	 */
	public function searchCompleteAction()
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

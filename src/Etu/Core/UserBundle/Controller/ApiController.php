<?php

namespace Etu\Core\UserBundle\Controller;

use Doctrine\ORM\EntityManager;
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
				$user['keepActive'], $user['permissions'], $user['isAdmin'], $user['options']
			);

			if ($user['phoneNumberPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['phoneNumber'], $user['phoneNumberPrivacy']);
			}

			if ($user['sexPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['sex'], $user['sexPrivacy']);
			}

			if ($user['nationalityPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['nationality'], $user['nationalityPrivacy']);
			}

			if ($user['adressPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['adress'], $user['adressPrivacy']);
			}

			if ($user['postalCodePrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['postalCode'], $user['postalCodePrivacy']);
			}

			if ($user['cityPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['city'], $user['cityPrivacy']);
			}

			if ($user['countryPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['country'], $user['countryPrivacy']);
			}

			if ($user['birthdayPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['birthday'], $user['birthdayPrivacy']);
			}

			if ($user['personnalMailPrivacy'] != User::PRIVACY_PUBLIC) {
				unset($user['personnalMail'], $user['personnalMailPrivacy']);
			}
		}

		return new Response(json_encode($users));
	}
}

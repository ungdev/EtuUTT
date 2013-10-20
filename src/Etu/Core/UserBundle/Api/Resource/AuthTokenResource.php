<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Etu\Core\CoreBundle\Framework\Api\Definition\Resource;
use Tga\Api\Component\HttpFoundation\Response;
use Goutte\Client;

// Annotations
use Tga\Api\Framework\Annotations as Api;

/**
 * @Api\Resource("/auth/token")
 */
class UserTokenResource extends Resource
{
	/**
	 * @Api\Operation(method="GET")
	 */
	public function getOperation()
	{
		// App token required
		$this->getAuthorizationProxy()->needAppToken();

		// Login and password required
		$login = $this->getRequest()->query->get('login');
		$password = $this->getRequest()->query->get('password');

		if (! $login) {
			return $this->getResponseBuilder()->createErrorResponse(
				Response::HTTP_BAD_REQUEST, 'Login required'
			);
		}

		if (! $password) {
			return $this->getResponseBuilder()->createErrorResponse(
				Response::HTTP_BAD_REQUEST, 'Password required'
			);
		}

		/*
		 * Login with CAS
		 */
		$client = new Client();
		$crawler = $client->request('GET', 'http://cas.utt.fr/cas/login?locale=fr');

		// Find CRSF token
		$token = $crawler->filter('input[name=lt]')->extract('value');

		if (isset($token[0])) {
			$token = $token[0];
		} else {
			return $this->getResponseBuilder()->createErrorResponse(
				Response::HTTP_BAD_REQUEST, 'Token generation is currently unavailable'
			);
		}

		// Submit form
		$form = $crawler->selectButton('SE CONNECTER')->form();

		$client->submit($form, array(
			'username' => $login,
			'password' => $password,
			'lt' => $token,
			'_eventId' => 'submit',
			'submit' => 'SE CONNECTER',
			'reset' => 'EFFACER',
		));

		if (! preg_match('/Connexion réussie/i', $client->getResponse()->getContent())) {
			return $this->getResponseBuilder()->createErrorResponse(
				Response::HTTP_FORBIDDEN, 'Authentication failed (wrong credentials)'
			);
		}

		/*
		 * Connection is successful: we generate a user token
		 */
		$appToken = $this->getAuthenticationProxy()->getApplicationToken();

		$userId = $this->getDoctrine()->createQueryBuilder()
			->select('u.id')
			->from('etu_users', 'u')
			->where('u.login = :login')
			->setParameter('login', $login)
			->setMaxResults(1)
			->execute()
			->fetch(\PDO::FETCH_OBJ);

		if (! $userId) {
			return $this->getResponseBuilder()->createErrorResponse(
				Response::HTTP_FORBIDDEN, 'Authentication failed (not authorized on EtuUTT)'
			);
		}

		$userId = $userId->id;

		$oldToken = $this->getDoctrine()->createQueryBuilder()
			->select('t.token')
			->from('etu_api_tokens_users', 't')
			->where('t.user_id = :user_id')
			->andWhere('t.application_id = :application_id')
			->setParameter('user_id', $userId)
			->setParameter('application_id', $appToken->getId())
			->setMaxResults(1)
			->execute()
			->fetch(\PDO::FETCH_OBJ);

		if (! $oldToken) {
			$token = hash('sha256', uniqid($userId, true).time());

			$now = new \DateTime();

			$this->getDoctrine()->insert('etu_api_tokens_users', array(
				'user_id' => $userId,
				'token' => $token,
				'createdAt' => $now->format('Y-m-d H:i:s'),
				'updatedAt' => $now->format('Y-m-d H:i:s'),
				'application_id' => $appToken->getId(),
			));
		} else {
			$token = $oldToken->token;
		}

		return array(
			'token' => $token,
		);
	}
}

<?php

namespace Etu\Core\UserBundle\Api\Resource;

use Etu\Core\CoreBundle\Framework\Api\Definition\Resource;
use Tga\Api\Component\HttpFoundation\Response;
use Goutte\Client;

// Annotations
use Tga\Api\Framework\Annotations as Api;

/**
 * @Api\Resource("/user/token/refresh")
 */
class UserTokenRefreshResource extends Resource
{
	/**
	 * @Api\Operation(method="GET")
	 */
	public function getOperation()
	{
		$this->getAuthorizationProxy()->needAppToken();
		$this->getAuthorizationProxy()->needUserToken();

		$userToken = $this->getAuthenticationProxy()->getUserToken();

		$token = hash('sha256', uniqid($userToken->getUser()->getId(), true).time());

		$now = new \DateTime();

		$this->getDoctrine()->createQueryBuilder()
			->update('etu_api_tokens_users', 't')
			->set('t.token', '\''.$token.'\'')
			->set('t.updatedAt', '\''.$now->format('Y-m-d H:i:s').'\'')
			->where('t.id = :id')
			->setParameter('id', (int) $userToken->getId())
			->execute();

		return array(
			'oldToken' => $userToken->getToken(),
			'newToken' => $token,
		);
	}
}

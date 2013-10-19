<?php

namespace Etu\Core\CoreBundle\Framework\Api\Security;

use Etu\Core\CoreBundle\Framework\Api\Security\ApplicationToken\AnonymousApplicationToken;
use Etu\Core\CoreBundle\Framework\Api\Security\Exception\InvalidAppTokenException;
use Etu\Core\CoreBundle\Framework\Api\Security\Exception\InvalidUserTokenException;
use Etu\Core\CoreBundle\Framework\Api\Security\UserToken\AnonymousUserToken;

class AuthorizationProxy
{
	/**
	 * @var AuthenticationProxy
	 */
	protected $authenticationProxy;

	/**
	 * @param AuthenticationProxy $authenticationProxy
	 */
	public function __construct(AuthenticationProxy $authenticationProxy)
	{
		$this->authenticationProxy = $authenticationProxy;
	}

	/**
	 * @throws \Tga\Api\Component\HttpFoundation\Exception\AccessDeniedException
	 */
	public function needAppToken()
	{
		$token = $this->authenticationProxy->getApplicationToken();

		if (! $token || $token instanceof AnonymousApplicationToken) {
			throw new InvalidAppTokenException();
		}
	}

	/**
	 * @throws \Tga\Api\Component\HttpFoundation\Exception\AccessDeniedException
	 */
	public function needUserToken()
	{
		$token = $this->authenticationProxy->getUserToken();

		if (! $token || $token instanceof AnonymousUserToken) {
			throw new InvalidUserTokenException();
		}
	}
}

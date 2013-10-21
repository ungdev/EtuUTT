<?php

namespace Etu\Core\CoreBundle\Api\Resource;

use Etu\Core\CoreBundle\Framework\Api\Definition\Resource;
use Etu\Module\ApiBundle\Entity\ApplicationToken;
use Etu\Module\ApiBundle\Entity\UserToken;

use Symfony\Component\HttpFoundation\Request;

// Annotations
use Tga\Api\Framework\Annotations as Api;

/**
 * @Api\Resource("/")
 */
class AboutResource extends Resource
{
	/**
	 * @Api\Operation(method="GET")
	 */
	public function getOperation(Request $request)
	{
		if ($request->server->has('HTTP_X_FORWARDED_FOR')) {
			$ip = $request->server->get('HTTP_X_FORWARDED_FOR');
		} else {
			$ip = $request->getClientIp();
		}

		/** @var ApplicationToken $application */
		$application = $this->getAuthenticationProxy()->getApplicationToken();

		/** @var UserToken $user */
		$user = $this->getAuthenticationProxy()->getUserToken();

		if ($application instanceof ApplicationToken) {
			$application = array(
				'name' => $application->getName(),
				'token' => $application->getToken(),
			);
		} else {
			$application = 'anonymous';
		}

		if ($user instanceof UserToken) {
			$user = array(
				'name' => $user->getUser()->getFullName(),
				'token' => $user->getToken(),
				'ip' => $ip,
			);
		} else {
			$user = 'anonymous';
		}

		return array(
			'api' => $this->getConfig()->get('api'),
			'application' => $application,
			'user' => $user
		);
	}
}

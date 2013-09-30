<?php

namespace Etu\Core\CoreBundle\Api\Resource;

use Etu\Api\Framework\Resource;
use Etu\Api\Http\Response;

use Etu\Api\Security\SecurityToken;
use Symfony\Component\HttpFoundation\Request;

// Annotations
use Etu\Api\Annotations as Api;
use Swagger\Annotations as SWG;

/**
 * @SWG\Resource(resourcePath="about")
 *
 * @Api\Resource("/")
 */
class AboutResource extends Resource
{
	/**
	 * @SWG\Api(
	 *      path="/",
	 *      description="About the API",
	 *      @SWG\Operations(
	 *          @SWG\Operation(
	 *              httpMethod="GET",
	 *              summary="About the APi",
	 *              notes="Give some informations about the EtuUTT API",
	 *              nickname="about"
	 *          )
	 *      )
	 * )
	 *
	 * @Api\Operation(method="GET")
	 */
	public function aboutGet(Request $request)
	{
		if ($request->server->has('HTTP_X_FORWARDED_FOR')) {
			$ip = $request->server->get('HTTP_X_FORWARDED_FOR');
		} else {
			$ip = $request->getClientIp();
		}

		$accessType = array(
			SecurityToken::ANONYMOUS => 'anonymous',
			SecurityToken::CONNECTED => 'connected',
		);

		return Response::success(array(
			'api' => $this->getConfig()->get('api'),
			'access' => array(
				'application' => $this->getSecurityToken()->getApplication(),
				'token' => $this->getSecurityToken()->getToken(),
				'type' => $accessType[$this->getSecurityToken()->getAuthorization()],
				'ip' => $ip,
			)
		));
	}
}

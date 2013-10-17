<?php

namespace Etu\Core\CoreBundle\Framework\Api\Definition;

use Etu\Core\CoreBundle\Framework\Api\Security\AuthenticationProxy;
use Tga\Api\Framework\HttpKernel\Definition\Resource as BaseResource;

class Resource extends BaseResource
{
	/**
	 * @return \Etu\Core\CoreBundle\Framework\EtuKernel
	 */
	public function getKernel()
	{
		return $this->get('kernel');
	}

	/**
	 * @return AuthenticationProxy
	 */
	public function getAuthenticationProxy()
	{
		return $this->get('security_proxy');
	}
}

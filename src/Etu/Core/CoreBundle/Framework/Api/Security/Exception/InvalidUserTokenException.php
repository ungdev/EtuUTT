<?php

namespace Etu\Core\CoreBundle\Framework\Api\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class InvalidUserTokenException extends AccessDeniedException
{
	public function __construct()
	{
		parent::__construct('Invalid or expired user token');
	}
}

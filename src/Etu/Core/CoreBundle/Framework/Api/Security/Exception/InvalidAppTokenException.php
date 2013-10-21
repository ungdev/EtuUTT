<?php

namespace Etu\Core\CoreBundle\Framework\Api\Security\Exception;

use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class InvalidAppTokenException extends AccessDeniedException
{
	public function __construct()
	{
		parent::__construct('Invalid application token');
	}
}

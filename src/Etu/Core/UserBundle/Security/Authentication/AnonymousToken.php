<?php

/*
 * This file is part of the TgaDrupalBridgeBundle package.
 *
 * (c) Titouan Galopin <galopintitouan@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Etu\Core\UserBundle\Security\Authentication;

use Symfony\Component\Security\Core\Authentication\Token\AnonymousToken as BaseAnonymousToken;

/**
 * Anonymous token
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class AnonymousToken extends BaseAnonymousToken
{
	/**
	 * Constructor.
	 */
	public function __construct()
	{
		parent::__construct(session_id(), 'anonymous');
	}
}
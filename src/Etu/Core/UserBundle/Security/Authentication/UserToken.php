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

use Etu\Core\UserBundle\Ldap\Model\User;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * User token
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class UserToken extends AbstractToken
{
    public function __construct($user)
    {
	    if (! $user || ! $user instanceof UserInterface) {
		    $this->setAuthenticated(false);
	    } else {
		    $roles = $user->getRoles();

		    if (! $roles) {
			    $roles = array();
		    }

		    parent::__construct($roles);

		    $this->setUser($user);
		    $this->setAuthenticated(true);
	    }
    }

    public function getCredentials()
    {
    	return '';
    }
}
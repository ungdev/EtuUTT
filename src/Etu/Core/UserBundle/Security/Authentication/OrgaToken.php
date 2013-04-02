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

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Authentication\Token\AbstractToken;

/**
 * Organization token
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class OrgaToken extends AbstractToken
{
    public function __construct($orga)
    {
	    if (! $orga || ! $orga instanceof UserInterface) {
		    $this->setAuthenticated(false);

		    parent::__construct(array());
	    } else {
		    $roles = $orga->getRoles();

		    if (! $roles) {
			    $roles = array();
		    }

		    parent::__construct($roles);

		    $this->setUser($orga);
		    $this->setAuthenticated(true);
	    }
    }

    public function getCredentials()
    {
    	return '';
    }
}
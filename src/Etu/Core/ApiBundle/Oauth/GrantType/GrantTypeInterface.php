<?php

namespace Etu\Core\ApiBundle\Oauth\GrantType;

use Etu\Core\ApiBundle\Entity\OauthAccessToken;
use Symfony\Component\HttpFoundation\Request;

interface GrantTypeInterface
{
    /**
     * @return OauthAccessToken
     */
    public function createToken(Request $request);

    /**
     * @return array
     */
    public function format(OauthAccessToken $token);

    /**
     * @return string
     */
    public function getName();
}

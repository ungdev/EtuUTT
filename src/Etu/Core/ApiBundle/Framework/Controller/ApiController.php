<?php

namespace Etu\Core\ApiBundle\Framework\Controller;

use Etu\Core\ApiBundle\Entity\OauthAccessToken;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

abstract class ApiController extends Controller
{
    /**
     * @param array  $data
     * @param int    $status
     * @param string $message
     *
     * @return Response
     */
    protected function format($data, $status, $message, Request $request)
    {
        return $this->get('etu.formatter')->format($request, $data, $status, $message);
    }

    /**
     * @return OauthAccessToken
     */
    protected function getAccessToken(Request $request)
    {
        if (!$request->attributes->get('_oauth_token')) {
            return false;
        }

        return $request->attributes->get('_oauth_token');
    }
}

<?php

namespace Etu\Core\ApiBundle\Framework\Controller;

use Etu\Core\ApiBundle\Entity\OauthAccessToken;
use Etu\Core\CoreBundle\Framework\Definition\Controller;
use Symfony\Component\HttpFoundation\Response;

abstract class ApiController extends Controller
{
    /**
     * @param array $data
     * @param int $status
     * @param string $message
     * @return Response
     */
    protected function format($data = array(), $status = 200, $message = null)
    {
        return $this->get('etu.formatter')->format($this->getRequest(), $data, $status, $message);
    }

    /**
     * @return OauthAccessToken
     */
    protected function getAccessToken()
    {
        if (! $this->getRequest()->attributes->get('_oauth_token')) {
            return false;
        }

        return $this->getRequest()->attributes->get('_oauth_token');
    }
}

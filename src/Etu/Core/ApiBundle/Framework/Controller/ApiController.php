<?php

namespace Etu\Core\ApiBundle\Framework\Controller;

use Etu\Core\ApiBundle\Framework\Model\Token;
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
     * @return Token
     */
    protected function getAccessToken()
    {
        if (! $this->getRequest()->attributes->get('_token')) {
            return false;
        }

        if (! is_array($this->getRequest()->attributes->get('_token'))) {
            return false;
        }

        return new Token($this->getRequest()->attributes->get('_token'));
    }
}

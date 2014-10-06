<?php

namespace Etu\Core\ApiBundle\Oauth;

use Etu\Core\ApiBundle\Entity\OauthAccessToken;

class ApiAccess
{
    /**
     * @var bool
     */
    protected $isGranted;

    /**
     * @var string
     */
    protected $error;

    /**
     * @var string
     */
    protected $errorMessage;

    /**
     * @var OauthAccessToken
     */
    protected $token;

    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }

    /**
     * @param string $error
     */
    public function setError($error)
    {
        $this->error = $error;
    }

    /**
     * @return string
     */
    public function getErrorMessage()
    {
        return $this->errorMessage;
    }

    /**
     * @param string $errorMessage
     */
    public function setErrorMessage($errorMessage)
    {
        $this->errorMessage = $errorMessage;
    }

    /**
     * @return boolean
     */
    public function isGranted()
    {
        return $this->isGranted;
    }

    /**
     * @param boolean $isGranted
     */
    public function setIsGranted($isGranted)
    {
        $this->isGranted = $isGranted;
    }

    /**
     * @return OauthAccessToken
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param OauthAccessToken $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }
}

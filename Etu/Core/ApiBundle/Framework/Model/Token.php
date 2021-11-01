<?php

namespace Etu\Core\ApiBundle\Framework\Model;

class Token
{
    /**
     * @var string
     */
    protected $token;

    /**
     * @var string
     */
    protected $client;

    /**
     * @var int
     */
    protected $user;

    /**
     * @var \DateTime
     */
    protected $expires;

    /**
     * @var array
     */
    protected $scopes;

    public function __construct(array $requestToken)
    {
        $this->token = $requestToken['access_token'];
        $this->client = $requestToken['client_id'];
        $this->user = (int) $requestToken['user_id'];
        $this->expires = \DateTime::createFromFormat('U', $requestToken['expires']);
        $this->scopes = explode(' ', $requestToken['scope']);
    }

    /**
     * @return mixed
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @return mixed
     */
    public function getExpires()
    {
        return $this->expires;
    }

    /**
     * @return mixed
     */
    public function getScopes()
    {
        return $this->scopes;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @return mixed
     */
    public function getUser()
    {
        return $this->user;
    }
}

<?php

namespace Etu\Core\ApiBundle\Oauth;

use Etu\Core\ApiBundle\Formatter\DataFormatter;
use OAuth2\Response as OAuthResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response as HttpResponse;

/**
 * Tranform OAuth libraries responses into Symfony responses.
 */
class ResponseHandler
{
    /**
     * @var DataFormatter
     */
    protected $formatter;

    /**
     * @param DataFormatter $formatter
     */
    public function __construct(DataFormatter $formatter)
    {
        $this->formatter = $formatter;
    }

    /**
     * @param Request $request
     * @param OAuthResponse $oauthResponse
     * @return HttpResponse
     */
    public function handle(Request $request, OAuthResponse $oauthResponse)
    {
        $response = $this->formatter->format(
            $request, $oauthResponse->getParameters(), $oauthResponse->getStatusCode(), $oauthResponse->getStatusText()
        );

        foreach ($oauthResponse->getHttpHeaders() as $key => $value) {
            if (strtolower($key) != 'content-type') {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}

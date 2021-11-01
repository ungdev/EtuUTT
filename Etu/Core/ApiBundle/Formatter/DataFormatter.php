<?php

namespace Etu\Core\ApiBundle\Formatter;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

class DataFormatter
{
    /**
     * @var Serializer
     */
    protected $serializer;

    /**
     * @param Serializer $serializer
     */
    public function __construct($serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param $request
     * @param array $data
     * @param int   $status
     * @param null  $message
     *
     * @return Response
     */
    public function format($request, $data = [], $status = 200, $message = null)
    {
        $data = array_merge($data, [
            'http' => [ // @TODO: remove this when everything is OAuth2 compatible
                'status' => $status,
                'message' => ($message) ? $message : Response::$statusTexts[$status],
                '_note' => 'http and response fields are deprecated',
            ],
            'response' => $data,
        ]);

        $format = 'json';

        if ($request->query->has('format')) {
            $format = $request->query->get('format');
        } elseif ($request->headers->has('format')) {
            $format = $request->headers->get('format');
        }

        if (!in_array($format, ['xml', 'json'])) {
            $format = 'json';
        }

        $response = new Response($this->serializer->encode($data, $format), $status);

        if ('json' == $format) {
            $response->headers->set('Content-Type', 'application/json');
        } else {
            $response->headers->set('Content-Type', 'text/'.$format);
        }

        return $response;
    }
}

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
     * @param int $status
     * @param null $message
     * @return Response
     */
    public function format($request, $data = array(), $status = 200, $message = null)
    {
        $data = [
            'http' => [
                'status' => $status,
                'message' => ($message) ? $message : Response::$statusTexts[$status]
            ],
            'response' => $data
        ];

        $format = 'json';

        if ($request->query->has('format')) {
            $format = $request->query->get('format');
        } else if ($request->headers->has('format')) {
            $format = $request->headers->get('format');
        }

        if (! in_array($format, ['xml', 'json'])) {
            $format = 'json';
        }

        $options = ($format == 'json') ? ['json_encode_options' => JSON_PRETTY_PRINT] : [];

        $response = new Response($this->serializer->encode($data, $format, $options), $status);
        $response->headers->set('Content-Type', 'text/'.$format);

        return $response;
    }
}

<?php

namespace Etu\Core\ApiBundle\Framework\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;

abstract class ApiController extends Controller
{
    protected function format($data = array(), $status = 200, $message = null)
    {
        $data = [
            'http' => [
                'status' => $status,
                'message' => ($message) ? $message : Response::$statusTexts[$status]
            ],
            'response' => $data
        ];

        $request = $this->getRequest();

        $format = 'json';

        if ($request->query->has('format')) {
            $format = $request->query->get('format');
        } else if ($request->headers->has('format')) {
            $format = $request->headers->get('format');
        }

        if (! in_array($format, ['xml', 'json'])) {
            $format = 'json';
        }

        /** @var Serializer $serializer */
        $serializer = $this->get('etu.serializer');

        $options = ($format == 'json') ? ['json_encode_options' => JSON_PRETTY_PRINT] : [];

        $response = new Response($serializer->encode($data, $format, $options), $status);
        $response->headers->set('Content-Type', 'text/'.$format);

        return $response;
    }
}

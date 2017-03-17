<?php

namespace Etu\Core\ApiBundle\Listener;

use Etu\Core\ApiBundle\Formatter\DataFormatter;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\HttpException;

class ExceptionListener
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
     * @param GetResponseForExceptionEvent $event
     */
    public function onKernelException(GetResponseForExceptionEvent $event)
    {
        if (mb_strpos($event->getRequest()->attributes->get('_controller'), 'Api\\Resource') !== false) {
            $exception = $event->getException();

            if ($exception instanceof HttpException) {
                $statusCode = $exception->getStatusCode();
            } else {
                $statusCode = 500;
            }

            $event->setResponse($this->formatter->format($event->getRequest(), [], $statusCode));
        }
    }
}

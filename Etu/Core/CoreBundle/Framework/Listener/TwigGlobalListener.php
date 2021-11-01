<?php

namespace Etu\Core\CoreBundle\Framework\Listener;

use Etu\Core\CoreBundle\Framework\Twig\GlobalAccessorObject;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class TwigGlobalListener
{
    /**
     * @var \Twig_Environment
     */
    protected $twig;

    /**
     * @var GlobalAccessorObject
     */
    protected $globalAccessorObject;

    public function __construct(\Twig_Environment $twig, GlobalAccessorObject $globalAccessorObject)
    {
        $this->twig = $twig;
        $this->globalAccessorObject = $globalAccessorObject;
    }

    public function onKernelRequest(GetResponseEvent $event)
    {
        // Give to Twig the accessor object
        $this->twig->addGlobal('etu', $this->globalAccessorObject);
    }
}

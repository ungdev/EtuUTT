<?php

namespace Etu\Core\ApiBundle\Controller;

use Etu\Core\ApiBundle\Framework\Controller\ApiController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

class MainController extends ApiController
{
    /**
     * @Route("")
     */
    public function aboutAction()
    {
        return $this->format([
            'name' => 'EtuUTT API',
            'version' => '1.0-alpha',
            'documentation' => 'Not available yet',
            'author' => 'Titouan Galopin <galopintitouan@gmail.com>'
        ]);
    }
}

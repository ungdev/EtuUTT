<?php

namespace Etu\Module\ArgentiqueBundle\Glide;

use League\Glide\ServerFactory;

/**
 * Customize the original Glide ServerFactory to add our custom image manipulator.
 */
class CustomServerFactory extends ServerFactory
{
    /**
     * Get image manipulators.
     *
     * @return array image manipulators
     */
    public function getManipulators()
    {
        return [
            new CustomizedEncode(),
        ];
    }
}

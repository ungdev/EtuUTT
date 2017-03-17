<?php

namespace Etu\Core\CoreBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;

/**
 * Helper interface.
 *
 * An helper is a class that know how to display a given kind of notification
 */
interface HelperInterface
{
    /**
     * @return string
     */
    public function getName();

    /**
     * @param Notification $notification
     *
     * @return string
     */
    public function render(Notification $notification);
}

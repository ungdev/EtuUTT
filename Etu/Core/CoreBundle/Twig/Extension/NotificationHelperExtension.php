<?php

namespace Etu\Core\CoreBundle\Twig\Extension;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperManager;

/**
 * StringManipulationExtension.
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class NotificationHelperExtension extends \Twig_Extension
{
    /**
     * @var HelperManager
     */
    protected $helperManager;

    public function __construct(HelperManager $helperManager)
    {
        $this->helperManager = $helperManager;
    }

    /**
     * @return array
     */
    public function getFunctions()
    {
        return [
            new \Twig_SimpleFunction('render_notif', [$this, 'render'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('highlight_notif_data', [$this, 'highlight'], ['is_safe' => ['html']]),
            new \Twig_SimpleFunction('duck_catched', [$this, 'duck_catched']),
        ];
    }

    /**
     * @return string
     */
    public function render(Notification $notification)
    {
        return $this->helperManager->getHelper($notification->getHelper())->render($notification);
    }

    /**
     * @param $string
     *
     * @return string
     */
    public function highlight($string)
    {
        return sprintf('<span class="notif-data">%s</span>', $string);
    }

    /**
     * @return bool
     */
    public function duck_catched()
    {
        return 0 != file_get_contents(__DIR__.'/../../../../../../duck_catched.txt');
    }
}

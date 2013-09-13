<?php

namespace Etu\Core\CoreBundle\Twig\Extension;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperManager;

/**
 * StringManipulationExtension
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class NotificationHelperExtension extends \Twig_Extension
{
	/**
	 * @var HelperManager
	 */
	protected $helperManager;

	/**
	 * @param HelperManager $helperManager
	 */
	public function __construct(HelperManager $helperManager)
	{
		$this->helperManager = $helperManager;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'notifs_helper';
	}

	/**
	 * @return array
	 */
	public function getFunctions()
	{
		return array(
			'render_notif' => new \Twig_Function_Method($this, 'render', array('is_safe' => array('html'))),
			'highlight_notif_data' => new \Twig_Function_Method($this, 'highlight', array('is_safe' => array('html'))),
			'duck_catched' => new \Twig_Function_Method($this, 'duck_catched'),
		);
	}

	/**
	 * @param Notification $notification
	 * @return string
	 */
	public function render(Notification $notification)
	{
		return $this->helperManager->getHelper($notification->getHelper())->render($notification);
	}

	/**
	 * @param $string
	 * @return string
	 */
	public function highlight($string)
	{
		return sprintf('<span class="notif-data">%s</span>', $string);
	}

	/**
	 * @return boolean
	 */
	public function duck_catched()
	{
		return file_get_contents(__DIR__.'/../../../../../../duck_catched.txt') != 0;
	}
}

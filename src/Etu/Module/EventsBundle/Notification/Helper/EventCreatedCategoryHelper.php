<?php

namespace Etu\Module\EventsBundle\Notification\Helper;

use Etu\Core\CoreBundle\Entity\Notification;
use Etu\Core\CoreBundle\Notification\Helper\HelperInterface;

class EventCreatedCategoryHelper implements HelperInterface
{
	/**
	 * @var \Twig_Environment
	 */
	protected $twig;

	/**
	 * @param \Twig_Environment $twig
	 */
	public function __construct(\Twig_Environment $twig)
	{
		$this->twig = $twig;
	}

	/**
	 * @return string
	 */
	public function getName()
	{
		return 'event_created_category';
	}

	/**
	 * @param Notification $notification
	 * @return string
	 */
	public function render(Notification $notification)
	{
		return $this->twig->render('EtuModuleEventsBundle:Notification:eventCreatedCategory.html.twig', array(
			'notif' => $notification
		));
	}
}

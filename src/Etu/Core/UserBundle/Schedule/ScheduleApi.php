<?php

namespace Etu\Core\UserBundle\Schedule;

use Etu\Core\UserBundle\Schedule\Browser\CriBrowser;
use Etu\Core\UserBundle\Schedule\Model\Course;

/**
 * Schedules manager based on CRI-hosted API.
 */
class ScheduleApi
{
	/**
	 * @var CriBrowser
	 */
	protected $browser;

	/**
	 * Constructor
	 */
	public function __construct()
	{
		$this->browser = new CriBrowser();
	}

	/**
	 * @param integer $page
	 * @return bool
	 */
	public function findPage($page)
	{
		$result = json_decode($this->browser->request(array('all' => '1', 'page' => $page)));

		$courses = array();

		foreach ($result->body->courses as $values) {
			$courses[] = new Course($values);
		}

		return array(
			'paging' => $result->body->paging,
			'courses' => $courses,
		);
	}
}

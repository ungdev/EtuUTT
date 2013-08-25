<?php

namespace Etu\Core\UserBundle\Schedule\Browser;

/**
 * CRI's server browser.
 */
class CriBrowser
{
	const ROOT_URL = 'http://bellodalix.utt.fr/ung/site-etu-api/app.php';

	public function request(array $parameters = array())
	{
		$get = '';

		foreach ($parameters as $key => $value) {
			$get .= $key.'='.$value.'&';
		}

		return file_get_contents(self::ROOT_URL.'?'.substr($get, 0, -1));
	}
}

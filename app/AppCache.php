<?php

require_once __DIR__.'/AppKernel.php';

use Symfony\Bundle\FrameworkBundle\HttpCache\HttpCache;

class AppCache extends HttpCache
{
	/*
	 * Application cache reverse proxy
	 *
	 * We cache all the requests for 15 minutes.
	 * The cache is unique for each cookie.
	 * Anyone can ask the reverse proxy to generate its cache using the classic Ctrl+F5 in his browser.
	 * The "Stale if error" options allow the cache to send the same response for 1 hour if an error occured,
	 * without purgind its content.
	 */
	protected function getOptions()
	{
		return array(
			'default_ttl'            => 15 * 60,
			'private_headers'        => array('Authorization', 'Cookie'),
			'allow_reload'           => true,
			'allow_revalidate'       => true,
			'stale_while_revalidate' => 2,
			'stale_if_error'         => 3600,
		);
	}
}

<?php

namespace Etu\Core\CoreBundle\Util;

/**
 * RedactorJsEscaper
 *
 * @author Titouan Galopin <galopintitouan@gmail.com>
 */
class RedactorJsEscaper
{
	/**
	 * Protect a string from XSS injections allowing RedactorJS tags
	 *
	 * @param $str
	 * @return string
	 */
	public static function escape($str)
	{
		// Catch YouTube videos
		$str = preg_replace(
			'/<iframe.+src="https?:\/\/www.youtube.com\/embed\/([a-z0-9_\-]+)".+><\/iframe>/iU',
			'https://www.youtube.com/watch?v=$1',
			$str
		);

		// Strip tags
		$str = strip_tags($str, '<code><span><div><label><a><br><p><b><i><del><strike><u><img><blockquote><mark><cite><small><ul><ol><li><hr><dl><dt><dd><sup><sub><big><pre><code><figure><figcaption><strong><em><table><tr><td><th><tbody><thead><tfoot><h1><h2><h3><h4><h5><h6>');

		// Reload YouTube videos
		$str = preg_replace(
			'/https?:\/\/www.youtube.com\/watch\?v=([a-z0-9_\-]+)/i',
			'<iframe width="560" height="315" src="http://www.youtube.com/embed/$1" frameborder="0" allowfullscreen></iframe>',
			$str
		);

		return $str;
	}
}

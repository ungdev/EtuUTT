<?php

namespace Etu\Core\UserBundle\Imap;

class ImapManager
{
	public function __construct()
	{
		require __DIR__.'/../Resources/lib/phpCAS/CAS.php';

		\phpCAS::proxy('2.0', 'cas.utt.fr', 443, '/cas/', false);
		\phpCAS::setNoCasServerValidation();
		\phpCAS::setPGTStorageFile(__DIR__.'/../Resources/temp/pgt.txt');
		\phpCAS::setDebug(__DIR__.'/../Resources/temp/logs.txt');

		\phpCAS::forceAuthentication();

		$client = \phpCAS::getClient();

		var_dump(file_get_contents(__DIR__.'/../Resources/temp/pgt.txt'));
	}
}

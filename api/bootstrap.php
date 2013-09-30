<?php

define('__API__', __DIR__);
define('__APP__', __API__.'/..');

require __APP__ . '/vendor/autoload.php';

$kernel = new \Etu\Api\Kernel(true);
$kernel->boot();

$kernel->handle(\Symfony\Component\HttpFoundation\Request::createFromGlobals())
	->send();

<?php

/**
 * This file is the front controller for your API. In other words, that means all requests to
 * your API have to pass throw it. It loads the kernel, handles the request using it and terminates the
 * process.
 *
 * By default, it's location is in the web directory to avoid direct access to the source files
 * using browser. However, you can move it anywhere you want, you juste have to edit the following line
 * corresponding to the api/bootstrap.php location.
 *
 * You can learn more about the front controller in the documentation :
 * @link http://api.titouangalopin.com/doc/master/front-controller
 */

require __DIR__.'/../../api/bootstrap.php';

// Boot the Kernel, handle the request and terminate the proccess
use Symfony\Component\HttpFoundation\Request;

$kernel = new ApiKernel('dev', true);

$kernel
	->boot()
	->handle(Request::createFromGlobals())
	->send();

$kernel->terminate();

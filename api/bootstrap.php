<?php

/**
 * This file is the bootstrap file: it load the kernel, handle the request using it and terminate the
 * process. You should NOT use it directly, you should use the index.php file that include it, to
 * avoid access to your code.
 */

define('__API__', dirname(__DIR__));

// Load dependencies
require __DIR__.'/../vendor/autoload.php';
require 'ApiKernel.php';

<?php

use Doctrine\Common\Annotations\AnnotationRegistry;

// Move vendor directory under vagrant
if (isset($_SERVER['COMPOSER_VENDOR_DIR'])) {
    $loader = require $_SERVER['COMPOSER_VENDOR_DIR'].'/autoload.php';
} elseif (isset($_SERVER['SYMFONY__KERNEL__VENDOR_DIR'])) {
    $loader = require $_SERVER['SYMFONY__KERNEL__VENDOR_DIR'].'/autoload.php';
} else {
    $loader = require __DIR__.'/../vendor/autoload.php';
}

AnnotationRegistry::registerLoader([$loader, 'loadClass']);

return $loader;

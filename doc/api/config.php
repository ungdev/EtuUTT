<?php

use Sami\Sami;
use Symfony\Component\Finder\Finder;

$iterator = Finder::create()
	->files()
	->name('*.php')
	->exclude('Resources')
	->exclude('Tests')
	->in(__DIR__.'/../../src/Etu/Core')
;

return new Sami($iterator);
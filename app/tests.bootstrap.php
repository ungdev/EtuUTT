<?php

$symfonyDir = __DIR__.'/../vendor/symfony/symfony/src';
$etuUttDir = __DIR__.'/../src';

/*
 * Polyfill from https://github.com/symfony/symfony/issues/21534
 */
class PHPUnit_Framework_TestCase extends \PHPUnit\Framework\TestCase
{
}

/*
 * Clear the cache
 */
if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
    passthru(
        sprintf(
            'php "%s/../bin/console" cache:clear --env=%s --no-warmup',
            __DIR__,
            $_ENV['BOOTSTRAP_CLEAR_CACHE_ENV']
        )
    );
}

/*
 * Recreate the database
 */
passthru(
    sprintf(
        'php "%s/../bin/console" doctrine:schema:drop --force --env=test',
        __DIR__
    )
);
passthru(
    sprintf(
        'php "%s/../bin/console" doctrine:schema:create --env=test',
        __DIR__
    )
);
passthru(
    sprintf(
        'php "%s/../bin/console" doctrine:fixtures:load --no-interaction --env=test',
        __DIR__
    )
);

echo "\n\n";

/*
 * Bootstrap
 */
require_once __DIR__.'/autoload.php';

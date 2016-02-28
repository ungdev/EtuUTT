<?php
$symfonyDir = __DIR__.'/../vendor/symfony/symfony/src';
$etuUttDir = __DIR__.'/../src';

/*
 * Clear the cache
 */
if (isset($_ENV['BOOTSTRAP_CLEAR_CACHE_ENV'])) {
    passthru(
        sprintf(
            'php "%s/console" cache:clear --env=%s --no-warmup',
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
        'php "%s/console" doctrine:schema:drop --force --env=test',
        __DIR__
    )
);
passthru(
    sprintf(
        'php "%s/console" doctrine:schema:create --env=test',
        __DIR__
    )
);
passthru(
    sprintf(
        'php "%s/console" doctrine:fixtures:load --no-interaction --env=test',
        __DIR__
    )
);

echo "\n\n";

/*
 * Bootstrap
 */
require_once __DIR__.'/bootstrap.php.cache';
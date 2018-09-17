#!/bin/bash
php -d memory_limit=-1 bin/console etu:users:sync --no-interaction --env=prod
php -d memory_limit=-1 bin/console etu:users:sync-bde-members --no-interaction --env=prod
php -d memory_limit=-1 bin/console etu:users:sync-daymail --no-interaction --env=prod
php -d memory_limit=-1 bin/console etu:users:sync-schedule --no-interaction --env=prod

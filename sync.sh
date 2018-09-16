#!/bin/bash
php -d memory_limit=-1 bin/console etu:users:sync
php -d memory_limit=-1 bin/console etu:users:sync-bde-members
php -d memory_limit=-1 bin/console etu:users:sync-daymail
php -d memory_limit=-1 bin/console etu:users:sync-schedule

Installation
============

Ce document résume en quelques lignes les éléments importants lors de l'installation du serveur pour
faire fonctionner EtuUTT.

## Installation

Le contexte d'exécution recomandé pour EtuUTT est nginx + php-fpm + mysql. Nous allons donc installer
ces programmes, et quelques autres améliorant les performances, la sécurité et les fonctionnalités.

### PHP

EtuUTT requiert au moins PHP 5.3.8 (Symfony 2.3). Cependant, il est recommandé d'utiliser la
dernière version stable disponible sur votre machine (PHP 5.4.4 sous debian wheezy au moment où
j'écrit ces lignes).

Installons PHP et des modules importants de base :

	sudo apt-get install php5 php5-cgi php5-cli php5-common php5-curl php5-dev php5-gd php5-imagick
	php5-mcrypt php5-mysql php5-suhosin php-xdebug php5-fpm php5-intl php5-ldap php5-sqlite

Si vous avez une version de PHP en dessous de la version 5.5, vous ne disposez pas par défaut d'un
opcache (comme APC ou ZendOptimiser+), celui-ci étant intétgré au coeur de PHP à partir de la
version 5.5. Installons APC :

	sudo apt-get install php-apc

Si vous utilisez PHP 5.5, activez l'utilisation de OpCache dans php.ini.

### Nginx

	sudo apt-get install nginx

### MySQL

	sudo apt-get install mysql-server mysql-client

### Composer

Composer est le gestionnaire de paquet de PHP utilisé par Symfony. Il permet de mettre à jour les
librairies et le framework d'EtuUTT.
Pour l'installer, passons par curl :

	sudo apt-get install curl
	curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer

Composer est désormais accessible depuis n'importe où sur le système en utilisant `composer`.
Pour mettre à jour les dépendances d'EtuUTT, exécutez :

	sudo composer self-update			// Met à jour Composer lui-même
	sudo composer update				// Met à jour les dépendances d'EtuUTT

### PHPUnit

PHPUnit permet de lancer les tests unitaires et fonctionnels d'EtuUTT. Il faut l'installer avec
Composer. Je vous propose de l'installer de manière globale lui aussi, pour y accéder depuis n'importe où :

	sudo mkdir /usr/share/phpunit
	cd /usr/share/phpunit

Créons le fichier composer.json pour installer PHPUnit :

	{
		"require": {
			"phpunit/phpunit": "3.*"
		},
		"config": {
			"bin-dir": "/usr/local/bin/"
		}
	}

Ensuite, démarrons Composer :

	sudo composer install

PHPUnit est maintenant installé sur votre machine. Lancez `phpunit --version` pour vous en assurer.

### Poppler-utils

**Note:** Poppler-utils sera déprécié lorsque nous aurons un accès à la base de données des UV depuis
le CRI.

Poppler-utils n'est pas nécessaire au bon fonctionnement d'EtuUTT. Cependant, il est utilisé par le
parser du guide des UV pour importer facilement les UV depuis une source officielle, il est donc
fortement recommandé de l'installer.

	sudo apt-get install poppler-utils

## Configuration

### Nginx

	server {
		listen 80;

		server_name etu.utt.fr;
		root /var/www/web/;

		access_log /var/log/nginx/etu.utt.fr.access_log;
		error_log /var/log/nginx/etu.utt.fr.error_log;

		# Supprime les préfixes de Symfony
		rewrite ^/app\.php/?(.*)$ /$1 permanent;
		rewrite ^/app_dev\.php/?(.*)$ /$1 permanent;

		# Réécritures pour éviter d'appeler Symfony
		rewrite ^/mail/?$ /mail/index.php last;

		# Développement : index index.php app_dev.php;
		index index.php app.php;

		location / {
			if (-f $request_filename) {
					break;
			}

			# Développement : rewrite ^(.*)$ /app_dev.php/$1 last;
			rewrite ^(.*)$ /app.php/$1 last;
		}

		location ~ \.php($|/) {
			include fastcgi_params;

			set $script $uri;
			set $path_info "";

			if ($uri ~ "^(.+\.php)($|/)") {
					set $script $1;
			}

			if ($uri ~ "^(.+\.php)(/.+)") {
					set $script $1;
					set $path_info $2;
			}

			fastcgi_buffers 8 256k;
			fastcgi_buffer_size 128k;
			fastcgi_send_timeout  5m;
			fastcgi_read_timeout 5m;
			fastcgi_connect_timeout 5m;
			fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
			fastcgi_pass unix:/var/run/php5-fpm.sock;
			fastcgi_param SCRIPT_NAME $script;
			fastcgi_param PATH_INFO $path_info;
		}
	}

### PHP-FPM

#### www.conf

listen = /var/run/php5-fpm.sock

#### php.ini

date.timezone = Europe/Paris
html_errors = On
upload_max_filesize = 4M
allow_url_fopen = On

Développement :
	error_reporting = E_ALL
	display_errors = On
	display_startup_errors = On
	log_errors = Off

Production :
	error_reporting = E_ALL & ~E_DEPRECATED & ~E_STRICT
	display_errors = Off
	display_startup_errors = Off
	log_errors = On

#### 20-xdebug.ini

xdebug.max_nesting_level = 256
xdebug.var_display_max_depth = 5

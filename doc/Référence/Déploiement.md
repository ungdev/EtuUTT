
Référence des éléments à ne pas oublier lors du déploiement
===========================================================

Pour déployer, il nous faut tout d'abord préparer le terrain. Voyons les éléments à configurer.

## Nginx

Il faut configurer Nginx en utilisant la configuration classique pour les applications Symfony2 :

```
server {
	listen 80;

	server_name etu.utt.fr;

	root /usr/share/nginx/www/web/;

	access_log /var/log/nginx/etu.utt.fr.access_log;
	error_log /var/log/nginx/etu.utt.fr.error_log;

	# Enlève app.php/ ou app_dev.php/ si il est présent
	rewrite ^/app\.php/?(.*)$ /$1 permanent;
	rewrite ^/app_dev\.php/?(.*)$ /$1 permanent;

	# Pour la launch
	# rewrite ^/launch$ /launch/index.php last;
	# rewrite ^/launcher$ /launch/launcher.php last;

	rewrite ^/mail/?$ /mail/index.php last;

	# Production: index.php app.php
	index index.php app.php;

	location / {
		if (-f $request_filename) {
			break;
		}

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

		fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
		fastcgi_intercept_errors on;
		fastcgi_pass 127.0.0.1:9000;
		fastcgi_param SCRIPT_NAME $script;
		fastcgi_param PATH_INFO $path_info;
	}
}
```

```
/usr/share/nginx/nginx.conf :

http {
	client_max_body_size 2M;
}
```


## PHP

Il est classique d'utiliser php-fpm avec nginx. Vous devez donc configurer PHP dans
/etc/php5/fpm/php.ini.

### Upload max size

Il faut définir la taille maximale par requête à 2 MB pour le module d'upload.
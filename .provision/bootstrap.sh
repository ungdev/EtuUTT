#!/usr/bin/env bash
# This script should install a dev env inside an Ubuntu Vagrant VM

# Configuration
MYSQL_USER=etuutt
MYSQL_PASS=etuutt
MYSQL_DATABASE=etuutt


# Tell ubuntu we are a script
export DEBIAN_FRONTEND=noninteractive
export ETUUTT_DATABASE_PASSWORD=$MYSQL_PASS
export ETUUTT_DATABASE_USER=$MYSQL_USER
export ETUUTT_DATABASE_NAME=$MYSQL_DATABASE

# Nginx
sudo apt-get -y install nginx
sudo systemctl enable nginx
sudo rm /etc/nginx/sites-enabled/* /etc/nginx/sites-available/*
sudo cp /vagrant/.provision/nginx/etuutt.conf /etc/nginx/sites-available/etuutt.conf
sudo cp /vagrant/.provision/nginx/phpmyadmin.conf /etc/nginx/sites-available/phpmyadmin.conf
sudo chmod 644 /etc/nginx/sites-available/*.conf
sudo ln -s /etc/nginx/sites-available/*.conf /etc/nginx/sites-enabled/
sudo systemctl restart nginx

# Php 7
sudo apt-get -y install php7.0 php7.0-fpm php7.0-mbstring php7.0-curl php7.0-mysql php7.0-xml php7.0-zip

# MariaDB (mysql)
sudo apt-get -y install mariadb-server-10.0
sudo mysql -u root -e "CREATE USER '$MYSQL_USER'@'localhost' IDENTIFIED BY '$MYSQL_PASS';
		GRANT ALL PRIVILEGES ON *.* TO '$MYSQL_USER'@'localhost' WITH GRANT OPTION;
		FLUSH PRIVILEGES;"
sudo mysql -u root -e "CREATE DATABASE $MYSQL_DATABASE;"

# PhpMyAdmin
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/dbconfig-install boolean true"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/app-password-confirm password $MYSQL_PASS"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/admin-pass password $MYSQL_PASS"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/mysql/app-pass password $MYSQL_PASS"
sudo debconf-set-selections <<< "phpmyadmin phpmyadmin/reconfigure-webserver multiselect"
sudo apt-get -y install phpmyadmin

# Composer
sudo apt-get -y install composer
composer global require "fxp/composer-asset-plugin:~1.2"

# Install project depencies and configure it
cd /vagrant
composer install

# Save db schema and fixtures
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load -n

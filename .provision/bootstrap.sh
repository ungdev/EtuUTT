#!/usr/bin/env bash
# This script should install a dev env inside an Ubuntu Vagrant VM

# Configuration
export MYSQL_USER=etuutt
export MYSQL_PASS=etuutt
export MYSQL_DATABASE=etuutt
export VUSER=ubuntu


# Tell ubuntu we are a script and set locals
export DEBIAN_FRONTEND=noninteractive
export ETUUTT_DATABASE_PASSWORD=$MYSQL_PASS
export ETUUTT_DATABASE_USER=$MYSQL_USER
export ETUUTT_DATABASE_NAME=$MYSQL_DATABASE

# Move composer vendor directory to speed up symfony
sudo mkdir -p /srv/composer-vendor/
sudo chown $VUSER /srv/composer-vendor/
sudo chmod u+rw /srv/composer-vendor/
export COMPOSER_VENDOR_DIR=/srv/composer-vendor/
echo "export COMPOSER_VENDOR_DIR=/srv/composer-vendor/" >> /home/$VUSER/.bashrc
sudo echo "export COMPOSER_VENDOR_DIR=/srv/composer-vendor/" >> /root/.bashrc

# Nginx
sudo apt-get update
sudo apt-get -y install nginx
sudo systemctl enable nginx
sudo rm /etc/nginx/sites-enabled/* /etc/nginx/sites-available/*
sudo cp /vagrant/.provision/nginx/etuutt.conf /etc/nginx/sites-available/etuutt.conf
sudo cp /vagrant/.provision/nginx/phpmyadmin.conf /etc/nginx/sites-available/phpmyadmin.conf
sudo chmod 644 /etc/nginx/sites-available/*.conf
sudo ln -s /etc/nginx/sites-available/*.conf /etc/nginx/sites-enabled/
sudo systemctl restart nginx

# Php 7
sudo add-apt-repository ppa:ondrej/php
sudo apt-get update
sudo apt-get -y install php7.1 php7.1-fpm php7.1-mbstring php7.1-curl php7.1-mysql php7.1-xml php7.1-zip

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

# Manually copy vendor directory because some packages ignore autoload file
cp -R /srv/composer-vendor /var/www/EtuUTT/vendor
chown -R $VUSER /var/www/EtuUTT/vendor

# Save db schema and fixtures
php bin/console doctrine:schema:update --force
php bin/console doctrine:fixtures:load -n

# Ensure rights
chown -R $VUSER /vagrant
chown -R $VUSER /srv/composer-vendor
chown -R www-data:www-data /var/www

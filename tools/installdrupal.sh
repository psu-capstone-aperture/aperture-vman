#!/bin/bash

PASSWD=$1
DBPASS=$2
DRUPALN=2
DRUPALS="drupal"
DRUPAL=$DRUPALS$DRUPALN

if [ -z $PASSWD ] || [ -z $DBPASS ]
then
	echo "USAGE: $0 <DB root password> <drupal DB user password>"
	echo ""
	echo "This is a simple script to install a new drupal instance on centos6."
	exit
fi

while true
do
	if [ -d "/var/www/$DRUPAL" ]
	then
		DRUPALN=$[DRUPALN + 1]
		DRUPAL=$DRUPALS$DRUPALN
		echo $DRUPAL
	else
		break
	fi
done

cd /var/www

wget http://ftp.drupal.org/files/projects/drupal-7.14.tar.gz

tar -xvzf drupal-7.14.tar.gz

rm -f drupal-7.14.tar.gz

mv drupal-7.14 $DRUPAL

echo "" >> /etc/httpd/conf/httpd.conf
echo "Alias /$DRUPAL \"/var/www/$DRUPAL/\"" >> /etc/httpd/conf/httpd.conf
echo "<Directory \"/var/www/$DRUPAL\">" >> /etc/httpd/conf/httpd.conf
echo "	AllowOverride All" >> /etc/httpd/conf/httpd.conf
echo "</Directory>" >> /etc/httpd/conf/httpd.conf

cp /var/www/$DRUPAL/sites/default/default.settings.php /var/www/$DRUPAL/sites/default/settings.php

chmod 644 /var/www/$DRUPAL/sites/default/settings.php

chown -R apache /var/www/$DRUPAL/

chgrp -R apache /var/www/$DRUPAL/

mysqladmin -u root -p$PASSWD create $DRUPAL

echo "CREATE USER '$DRUPAL'@'localhost' IDENTIFIED BY '$DBPASS';" | mysql -u root -p$PASSWD mysql

echo "GRANT ALL ON $DRUPAL.* TO '$DRUPAL'@localhost;" | mysql -u root -p$PASSWD mysql

service httpd restart

echo ""
echo ""
echo "Drupal db user: $DRUPAL"
echo "Drupal db user's password: $DBPASS"

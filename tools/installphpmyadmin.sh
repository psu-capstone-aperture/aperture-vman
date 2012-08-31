#!/bin/bash

PASSWD=$1

if [ -z $PASSWD ]
then
	echo "USAGE: $0 <password>"
	echo ""
	echo "This is a simple script to install phpmyadmin on centos 6."
	echo "	<password>: password to be set for the database's root user."
	exit
fi

wget http://packages.sw.be/rpmforge-release/rpmforge-release-0.5.2-2.el6.rf.i686.rpm

rpm --import http://apt.sw.be/RPM-GPG-KEY.dag.txt

rpm -i rpmforge-release-0.5.2-2.el6.rf.i686.rpm

yum --enablerepo=rpmforge install phpmyadmin -y

wget http://dl.fedoraproject.org/pub/epel/6/i386/epel-release-6-7.noarch.rpm

rpm --import https://fedoraproject.org/static/0608B895.txt

rpm -i epel-release-6-7.noarch.rpm

yum update -y

yum install php-mcrypt -y

mysqladmin -u root password $PASSWD

sed -i.bak -e s/all/none/ -e s/127\.0\.0\.1/all/ /etc/httpd/conf.d/phpmyadmin.conf

sed -i.bak -e 's/\$cfg\['\''blowfish_secret'\''\] = '\'''\''\;/\$cfg\['\''blowfish_secret'\''\] = '\'''$PASSWD''\''\;/' /usr/share/phpmyadmin/config.inc.php

service httpd restart

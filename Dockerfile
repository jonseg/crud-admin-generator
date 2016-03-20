#vim:set ft=dockerfile:
FROM jnmik/docker-centos7-httpd-utilities-php5.6:latest
MAINTAINER Jean-Michael Cyr <cyrjeanmichael@gmail.com>
ADD . /var/www/html
WORKDIR /var/www/html
COPY ./apache.conf /etc/httpd/conf.d/apache.conf

CMD ["/bin/bash", "/var/www/html/boot-init.sh"]
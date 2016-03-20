!#/bin/bash

echo "Generate admin based on current mysql schema ... "
php console generate:admin

echo "Start supervisord ... "
/usr/bin/supervisord -n -c /etc/supervisord.conf
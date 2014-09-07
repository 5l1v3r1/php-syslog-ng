#!/bin/bash
# WARNING: THIS WILL DELETE ALL USERS FROM THE DATABASE AND CREATE NEW ONES
config="/www/php-syslog-ng/html/config/config.php"

user=`cat $config | grep "'DBADMIN'" | awk -F"'" '{print $4}' `
pw=`cat $config | grep "'DBADMINPW'" | awk -F"'" '{print $4}' `
db=`cat $config | grep "'DBNAME'" | awk -F"'" '{print $4}' `

echo
echo "WARNING: THIS WILL DELETE ALL USERS FROM THE DATABASE AND CREATE NEW ONES"
echo
echo "continuing in 5 seconds"
echo "ctrl-c now to abort!"
sleep 5
echo "delete from users;" | mysql -u $user --password=$pw $db

echo "
INSERT INTO users VALUES ('admin','21232f297a57a5a743894a0e4a801fc3','c65e0e4a8fd3ea3d433d78c4ab27f8e7','2007-07-06 12:15:35'),('demo','fe01ce2a7fbac8fafaed7c982a04e229',NULL,NULL);
" | mysql -u $user --password=$pw $db
echo "script completed..."


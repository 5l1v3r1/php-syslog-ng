<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com

require_once 'includes/html_header.php';

if($dbProblem) {
	echo "<b>A database connection problem was encountered.<br />Please check config/config.php to make sure everything is correct and make sure the MySQL server is up and running.</b>";
}
?>
<table class="pagecontent">
<tr><td><span class="longtext">
<h3 class="title">User Manual</h3>
<font color="blue">Click <a href="userguide.doc">here</a> to download the user guide.</font><br>
<h3 class="title">Search</h3>
Some of the input fields only affect the regular search result. Those fields are:<br>
<ul>
<li>DATE and TIME</li>
<li>ORDER BY</li>
<li>ORDER</li>
</ul>
The rest of the input fields affect both kinds of search results.<br />
If you have more than one table with log data but you do not have a merge table then you have to select what table to work with before you can do anything else. If you have multiple tables and a merge table then the host and facility fields are populated using the merge table.<br />
A blank date field indicates any date. And if a date is entered and no time is specified then 00:00:00 is used for the FROM time and 23:59:59 is used for the TO time. Entering a time without a date results in the time being ignored. 'now', 'today' and 'yesterday' are valid date inputs (now and today are the same) and 'now' is a valid time.

<h3 class="title">Config</h3>
The Config page lets you change your password. It is also the place where you add and delete users and edit the access control settings.

<h3 class="title">Search results</h3>
You can view the search results in two modes: regular or tail. In the regular view you are able to page through all the entries that match your query. In the tail view you will only see the number of entries you specified in the 'records per page' field but the page will keep refreshing so that you can monitor the logs in near real-time.<br />
At the top there is a link to reference this paricular query. It can be useful to create bookmarks to queries you use often. The entries in the HOST column are links. If you click on one of those links then your query will be narrowed down to logs from that particular host.

<h3 class="title">Installation</h3>
<h4 class="title">Configure MySQL</h4>
The quickest way to do this is to use the dbsetup.sql file in the scripts directory. Just edit the file and set some passwords for the three users that are created (replace PW_HERE). The script will create a table for logs and a table for user authentication and give the three users some sensible priviliges. If you make other changes like changing the name of the database or the name of the tables then make sure you edit config.php to reflect that. After editing the dbsetup.sql file then just run it like this:
<p class="code">
shell> mysql -uroot -p < dbsetup.sql
</p>

<h4 class="title">Configure syslog-ng</h4>
Now you need to configure syslog-ng to send the desired log messages to a pipe that can be read to send the entries to MySQL. You will need to add two entries to the syslog-ng configuration file. The configuration file is usually in /etc/syslog-ng/syslog-ng.conf.<br />
You first need to add a new 'destination' entry. Add something like this:
<p class="code">
destination d_mysql {<br />
&nbsp;&nbsp;&nbsp;pipe("/var/log/mysql.pipe"<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;template("INSERT INTO logs<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;(host, facility, priority, level, tag, datetime, program, msg)<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;VALUES ( '$HOST', '$FACILITY', '$PRIORITY', '$LEVEL', '$TAG', '$YEAR-$MONTH-$DAY $HOUR:$MIN:$SEC',<br />
&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'$PROGRAM', '$MSG' );\n") template-escape(yes));<br />
};
</p>
That will take your log entries and format them into a SQL query that can be run to add it to the database.<br />
You also need to add an entry that determines what log entries to forward to the FIFO pipe. You will usually want to forward everything to MySQL and there should already be a 'source' entry for that in your syslog-ng.conf file (usually called src or s_all). To tie that source to the destination you just created you will add something like this:
<p class="code">
log {<br />
&nbsp;&nbsp;&nbsp;source(s_all);<br />
&nbsp;&nbsp;&nbsp;destination(d_mysql);<br />
};
</p>

<h4 class="title">Setup syslog-ng to MySQL pipe</h4>
An example for a script that feeds log entries from the FIFO pipe to MySQL is included in the scripts directory. The script is called syslog2mysql.sh.
<p class="code">
#!/bin/bash<br />
<br />
if [ ! -e /var/log/mysql.pipe ]<br />
then<br />
&nbsp;&nbsp;&nbsp;mkfifo /var/log/mysql.pipe<br />
fi<br />
while [ -e /var/log/mysql.pipe ]<br />
do<br />
&nbsp;&nbsp;&nbsp;mysql -u syslogfeeder --password=PASS_HERE syslog < /var/log/mysql.pipe >/dev/null<br />
done
</p>
If you decide to use this script then you have to replace PASS_HERE with the password for the syslogfeeder user. You will also probably want to have this script started automatically whenever you start the server. So add an entry in the inittab or start it through init.d (or whatever is appropriate on your system). But make sure you call it after MySQL has been started.<br />
Now start the syslog2mysql.sh script:
<p class="code">
shell> ./syslog2mysql.sh &
</p>
or if you created an init.d script:
<p class="code">
shell> /etc/init.d/syslog2mysql start
</p>
It's finally time to restart the syslog-ng daemon and start sending your logs to the database:
<p class="code">
shell> /etc/init.d/syslog-ng restart
</p>

<h4 class="title">Edit config.php</h4>
If you are using the default database setup from the dbsetup.sql file then all you need to do is to enter the passwords for the sysloguser and syslogadmin users, set the right host and port for the database server if it is not on the same server as the web server and set the correct URL. Otherwise read through the config.php file and configure things to suit your needs. All the different options are explained in the file.

<h4 class="title">Securing php-syslog-ng</h4>
It is recommended that you enable authentication in the config/config.php file. Leaving php-syslog-ng open to the public is not a good idea because some log entries might contain information that should not be shared. If you do not want to use the authentication in php-syslog-ng then you should consider using the authentication that is available in Apache.<br>
Another thing which is strongly recommended is to deny access to the config, includes and scripts directories. Users accessing the files in those directories directly might be able to cause damage to your logs and/or gain access to things they should not have access to. Add something like this to the apache configuration file:
<p class="code">
&lt;Directory "/var/www/phpsyslogng/scripts"&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;Deny from all<br />
&lt;/Directory&gt;<br />
&lt;Directory "/var/www/phpsyslogng/includes"&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;Deny from all<br />
&lt;/Directory&gt;<br />
&lt;Directory "/var/www/phpsyslogng/config"&gt;<br />
&nbsp;&nbsp;&nbsp;&nbsp;Deny from all<br />
&lt;/Directory&gt;
</p>
<h4 class="title">Log rotation</h4>
Log rotation should be part of most installations where you use php-syslog-ng. It is better to use log rotation than deleting rows in the main table because deleting rows can lead to performance problems. Rotating old logs out of the main table will also usually result in better performance because the tables with old logs are static and can be optimized. There is a logrotate.php script in the scripts directory. You may have to edit it to enter the correct path to your php-syslog-ng installation but after that it should be ready for use. If you enable merge tables in the config.php file then a merge table of all log tables will be created at the end of the script. The merge table will allow you to search across all tables instead of having to do searches against one table at a time. The merge table does equate to a slight performance hit on the search form because the fields are populated based on all tables instead of one particular table.<br />
You can also specify enable the LOGRETENTION setting in config.php. If you enable this then logs older than this setting will be dropped whenever the logrotate.php is run.<br />
If you decide to use the logrotate.php script then just add it to your crontab and have it run however frequent you want (max is currently one time per day).

<h3 class="title">Other resources</h3>
Check the following places for help:<br />
- The sourceforge mailing list: <a href="http://sourceforge.net/mailarchive/forum.php?forum=php-syslog-ng-support">http://sourceforge.net/mailarchive/forum.php?forum=php-syslog-ng-support</a><br />
- The Gentoo Linux Wiki: <a href="http://gentoo-wiki.com/HOWTO_setup_PHP-Syslog-NG">http://gentoo-wiki.com/HOWTO_setup_PHP-Syslog-NG</a>
</span></td></tr></table>

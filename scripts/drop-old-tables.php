<?php
/*
 * drop-old-tables.php
 *
 * Developed by Clayton Dukes <cdukes@cdukes.com>
 * Copyright (c) 2007 http://www.gdd.net
 * Licensed under terms of GNU General Public License.
 * All rights reserved.
 *
 * Changelog:
 * 2007-02-05 - created
 *
 */

/* $Platon$ */

/* Modeline for ViM {{{
 * vim: set ts=4:
 * vim600: fdm=marker fdl=0 fdc=0:
 * }}} */

// This script will make DROP queries for getting rid of tables that have the same prefix
// (i.e. tables logs_*). This makes it easier to clean up old data.
//

$basePath = dirname( __FILE__ );
include_once "$basePath/../html/includes/common_funcs.php";
include_once "$basePath/../html/config/config.php";

if (!mysql_connect(DBHOST, DBADMIN, DBADMINPW)) {
	print 'Could not connect to mysql';
	exit;
}

$result = mysql_list_tables(DBNAME);

if (!$result) {
	print "DB Error, could not list tables\n";
	print 'MySQL Error: ' . mysql_error();
	exit;
}

if ($_GET['prefix'] == null) {
	print "<b>TABLE LIST:</b><br>";

	while ($row = mysql_fetch_row($result)) {
		print "Table: $row[0]<br>";
	}
	print "<br><b>Syntax to make a DROP TABLE is 'drop_tables.php?prefix=\"prefix that the tables have\"'.</b>";
} else {

	echo "<b>DROP TABLE QUERY FOR TABLES WITH PREFIX '".$_GET['prefix']."'</b><br>Copy this query into your MySQL program and execute to remove these tables.<br><br>";

	while ($row = mysql_fetch_row($result)) {
		if (substr($row[0], 0, strlen($_GET['prefix'])) == $_GET['prefix']) {
			print "DROP TABLE $row[0];<br>";
		}
	}
}

mysql_free_result($result);
?>

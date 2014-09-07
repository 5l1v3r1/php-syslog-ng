#!/usr/bin/php
<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com
// Copyright (C) 2006 Clayton Dukes cdukes@cdukes.com

echo "\nStarting logrotate\n";
echo date("Y-m-d H:i:s")."\n";


include_once "/www/php-syslog-ng/html/includes/common_funcs.php";
include_once "/www/php-syslog-ng/html/config/config.php";

$dbLink = db_connect_syslog(DBADMIN, DBADMINPW);

// Drop temp table if it exists
echo "Dropping temp".DEFAULTLOGTABLE." if it exists ... ";
$query = "DROP TABLE IF EXISTS temp".DEFAULTLOGTABLE;
perform_query($query, $dbLink);
$q = perform_query($query, $dbLink);
if ( strstr( $q, "Error") ) {
   	echo ("\nAn error was encountered while dropping temp".DEFAULTLOGTABLE ."\n");
} else {
   	echo "ok.\n";
}

// Make sure we're not overwriting $today
$today = date("Ymd");
$query = "SHOW CREATE TABLE ".DEFAULTLOGTABLE.$today;
$q = perform_query_quiet($query, $dbLink);
$row = @mysql_fetch_array($q);
if ($row[0]) {
   	echo ("\nERROR: " .DEFAULTLOGTABLE.$today ." already exists!\nEither DROP the table or wait until tomorrow to re-run this script." ."\n");
   	die("Here's the duplicate table:\n" .$row[1] ."\n");
} 

// Create new table
echo "Creating temp".DEFAULTLOGTABLE." ... ";
$query = "SHOW CREATE TABLE ".DEFAULTLOGTABLE;
$result = perform_query($query, $dbLink);
$row = mysql_fetch_array($result);

// CDUKES 7/4/08: Added stripslashes below to fix quoting error
// Ref: http://groups.google.com/group/php-syslog-ng-support/browse_thread/thread/a41fc933fe705f90
$createQuery = stripslashes($row[1]);
$search = "CREATE TABLE `".DEFAULTLOGTABLE."`";
$replace = "CREATE TABLE `temp".DEFAULTLOGTABLE."`";
$createQuery = str_replace($search, $replace, $createQuery);
$q = perform_query($createQuery, $dbLink);
if ( strstr( $q, "Error") ) {
   	echo ("\nAn error was encountered while dropping temp".DEFAULTLOGTABLE ."\n");
} else {
   	echo "ok.\n";
}


// Drop the merge table
echo "Dropping ".MERGELOGTABLE." if it exists ... ";
if(defined('MERGELOGTABLE') && MERGELOGTABLE) {
   	$query = "FLUSH TABLES";
   	perform_query($query, $dbLink);
   	$query = "DROP TABLE IF EXISTS ".MERGELOGTABLE;
   	$q = perform_query($query, $dbLink);
   	if ( strstr( $q, "Error") ) {
	   	echo ("\nAn error was encountered while dropping ".MERGELOGTABLE ."\n");
   	} else {
	   	echo "ok.\n";
   	}
}

// Rename the two tables
echo "Renaming '".DEFAULTLOGTABLE."' to '".DEFAULTLOGTABLE.$today."' and 'temp".DEFAULTLOGTABLE."' to '".DEFAULTLOGTABLE."' ... ";
$query = "RENAME TABLE ".DBNAME.".".DEFAULTLOGTABLE." TO ".DBNAME.".".DEFAULTLOGTABLE.$today.", "
.DBNAME.".temp".DEFAULTLOGTABLE." TO ".DBNAME.".".DEFAULTLOGTABLE;
$q = perform_query($query, $dbLink);
if ( strstr( $q, "Error") ) {
   	echo ("\nError, unable to complete log rotation\n");
   	die("Failed Query: \n" .$query);
} else {
   	echo "ok.\n";
}

/* cdukes 2-27-08 - Changed below to prevent corruption of table data since myisamchk should really not be used while the mysql server is up
   "The easiest way to avoid this problem is to use CHECK TABLE instead of myisamchk to check tables."
Ref: http://dev.mysql.com/doc/refman/5.0/en/myisamchk.html
Ref: http://dev.mysql.com/doc/refman/5.0/en/check-table.html
$cmd = "myisampack /var/lib/mysql/".DBNAME."/".DEFAULTLOGTABLE.$today.".MYI && myisamchk -rq --sort-index --analyze /var/lib/mysql/".DBNAME."/".DEFAULTLOGTABLE.$today.".MYI";
$output = `$cmd`;
 */
echo "Checking " .DEFAULTLOGTABLE.$today." for errors...";
$query = "CHECK TABLE ".DBNAME.".".DEFAULTLOGTABLE.$today ."QUICK";
$q = perform_query($query, $dbLink);
if ( strstr( $q, "Error") ) {
   	echo ("\nError, something failed during CHECK phase\n");
   	die("Failed Query: \n" .$query);
} else {
   	echo "ok.\n";
}
echo "Now optimizing " .DEFAULTLOGTABLE.$today."...";
$query = "OPTIMIZE TABLE ".DBNAME.".".DEFAULTLOGTABLE.$today;
$q = perform_query($query, $dbLink);
if ( strstr( $q, "Error") ) {
   	echo ("\nError, something failed during Optimization\n");
   	die("Failed Query: \n" .$query);
} else {
   	echo "ok.\n";
}

if(defined('LOGROTATERETENTION') && LOGROTATERETENTION) {
   	$cutoffDate = date("Ymd", mktime(0, 0, 0, date("m"), date("d")-LOGROTATERETENTION, date("Y")));
   	echo "Deleting logs older than $cutoffDate:\n";
   	$logTableArray = get_logtables($dbLink);
   	foreach($logTableArray as $value) {
	   	if(preg_match("([0-9]{8}$)", $value)) {
		   	// determine if datestamp is old enough
		   	$tableDate = strrev(substr(strrev($value), 0, 8));
		   	if($cutoffDate > $tableDate) {
			   	echo "(Dropping) $value\n";
			   	$query = "DROP TABLE ".$value;
			   	$q = perform_query($query, $dbLink);
			   	if ( strstr( $q, "Error") ) {
				   	echo ("\nError, something failed during Retention Phase!\n");
				   	die("Failed Query: \n" .$query);
			   	} 
			}
		   	else {
			   	echo "(Keeping) $value\n";
		   	}
	   	}
   	}
}

if(defined('MERGELOGTABLE') && MERGELOGTABLE) {
   	$logTableArray = get_logtables($dbLink);
   	echo "Creating merge table...";
   	$query = "SHOW CREATE TABLE ".DEFAULTLOGTABLE;

	$result = perform_query($query, $dbLink);
   	$row = mysql_fetch_array($result);
   	$createQuery = stripslashes($row[1]);

	$oldStr = "CREATE TABLE `".DEFAULTLOGTABLE."`";
   	$newStr = "CREATE TABLE `".MERGELOGTABLE."`";
   	$createQuery = str_replace($oldStr, $newStr, $createQuery);

	$oldStr = "ENGINE=MyISAM";
   	// $newStr = "TYPE=MRG_MyISAM";
   	$newStr = "ENGINE=MRG_MyISAM";
   	$createQuery = str_replace($oldStr, $newStr, $createQuery);
   	$oldStr = "TYPE=MyISAM";
   	// $newStr = "TYPE=MRG_MyISAM";
   	$newStr = "ENGINE=MRG_MyISAM";
   	$createQuery = str_replace($oldStr, $newStr, $createQuery);


	// REMOVED below because it was causing an error in newer version of Mysql
	// Ref: http://bugs.mysql.com/bug.php?id=26881
	// $createQuery = str_replace('PRIMARY KEY', 'INDEX', $createQuery);


	$unionStr = " UNION=(";
   	foreach($logTableArray as $value) {
	   	$unionStr = $unionStr.$value.", ";
   	}
   	$unionStr = rtrim($unionStr, ", ");
   	$unionStr = $unionStr.")";

	$createQuery = $createQuery.$unionStr;
// 	die($createQuery);

	// $query = "FLUSH TABLES";
   	$flushQuery = "FLUSH TABLES";
   	perform_query($flushQuery, $dbLink);
   	perform_query($createQuery, $dbLink);
   	perform_query($flushQuery, $dbLink);
   	echo "ok.\n";
	// echo "$createQuery\n";
}

echo "\n".date("Y-m-d H:i:s")."\n";
echo "All done!\n";
?>

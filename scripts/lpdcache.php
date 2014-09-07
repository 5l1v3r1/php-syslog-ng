<?php
/*
 * cache.php
 *
 * Developed by Clayton Dukes <cdukes@cdukes.com>
 * Copyright (c) 2008 http://www.gdd.net
 * Licensed under terms of GNU General Public License.
 * All rights reserved.
 *
 * Changelog:
 * 2008-08-07 - created
 *
 */

/* $Platon$ */


/* Cdukes: 08-07-2008
   Added below to use cache table for filling in graph values on logs per day graph on main page
   Note that this requires a small modification to the current search_cache table:
   ALTER TABLE search_cache MODIFY type enum('HOST','FACILITY','PROGRAM','LPD') default NULL
   By default, this will cache X amount of days based on your LOGROTATERETENTION setting in config.php
 */

require_once "/www/php-syslog-ng/html/includes/common_funcs.php";
require_once "/www/php-syslog-ng/html/config/config.php";

$dbLink = db_connect_syslog(DBADMIN, DBADMINPW);

echo "\nStarting LPD Cache\n";
echo date("Y-m-d H:i:s")."\n";

// First check to see if we have caching turned on
if(defined('LPD_CACHE') && LPD_CACHE == TRUE) {
   	echo "Setting daily log counts in ".CACHETABLENAME."\n";
   	$cacheready = 0;
   	// Check to make sure the search cache has the LPD field set
   	$query="DESCRIBE ".CACHETABLENAME;
   	$result = perform_query($query, $dbLink);
   	while($row = fetch_array($result)) {
	   	if (ereg('LPD', $row['Type'])) {
		   	// 	echo $row['Type']."\n";
		   	$cacheready = 1;
	   	}
   	}
   	// We're ready to start
   	if ($cacheready == 1) {
	   	// check to see if this is a new install (MERGELOGTABLE doesn't exists)
	   	$check = mysql_query ("SELECT * FROM ".MERGELOGTABLE." LIMIT 0,1"); /* >>limit<< is just to make it faster in case the db is huge */
	   	if ($check) {
		   	for($i=0;$i<=LOGROTATERETENTION;$i++) {
			   	$query="SELECT value as count from ".CACHETABLENAME." WHERE type='LPD' AND updatetime=DATE_SUB(CURDATE(), INTERVAL $i DAY)";
			   	$result = perform_query($query, $dbLink);
			   	if(num_rows($result) >= 1) {
				   	echo "Entry $i already in cache...";
				   	if ($i == 0) { // We only need to update today's count since (presumably) old logs should not be getting new INSERTs
					   	echo "UPDATING\n";
					   	// Test for SqueezeDB function since count fields are different
					   	if(defined('SQZ_ENABLED') && SQZ_ENABLED == TRUE) {
							   	$query="SELECT SUM(counter) as value FROM ".MERGELOGTABLE." WHERE datetime BETWEEN DATE_SUB(CURDATE(), INTERVAL $i DAY) AND DATE_SUB(DATE_ADD(CURDATE( ), INTERVAL 1 DAY), INTERVAL $i DAY)";
					   	} else {
							   	$query="SELECT count(*) as value FROM ".MERGELOGTABLE." WHERE datetime BETWEEN DATE_SUB(CURDATE(), INTERVAL $i DAY) AND DATE_SUB(DATE_ADD(CURDATE( ), INTERVAL 1 DAY), INTERVAL $i DAY)";
					   	}
					   	$result = perform_query($query, $dbLink);
					   	$row = fetch_array($result);
					   	$query="UPDATE ".CACHETABLENAME." SET value='".$row['value']."' WHERE type='LPD' AND updatetime=DATE_SUB(CURDATE(), INTERVAL $i DAY) AND DATE_SUB(DATE_ADD(CURDATE( ), INTERVAL 1 DAY), INTERVAL $i DAY)";
					   	perform_query($query, $dbLink);
				   	} else {
					   	echo "No update needed\n";
				   	}
			   	} else {
				   	echo "Entry $i...INSERTing\n";
					   	$query="INSERT INTO ".CACHETABLENAME." (tablename, type, value, updatetime) SELECT '".MERGELOGTABLE."' as tablename, 'LPD' as type, SUM(counter) as value, DATE_SUB(CURDATE(), INTERVAL $i DAY) as updatetime FROM ".MERGELOGTABLE." WHERE datetime BETWEEN DATE_SUB(CURDATE(), INTERVAL $i DAY) AND DATE_SUB(DATE_ADD(CURDATE( ), INTERVAL 1 DAY), INTERVAL $i DAY)";
				   	perform_query($query, $dbLink);
			   	}
		   	}
	   	} else {
		   	echo "Either you have no ".MERGELOGTABLE." table or it is messed up, assuming this is a fresh install and updating cache for today only...\n";
		   	if(defined('SQZ_ENABLED') && SQZ_ENABLED == TRUE) {
		   	$query="INSERT INTO ".CACHETABLENAME." (tablename, type, value, updatetime) SELECT '".MERGELOGTABLE."' as tablename, 'LPD' as type, SUM(counter) as value, DATE_SUB(CURDATE(), INTERVAL 0 DAY) as updatetime FROM ".DEFAULTLOGTABLE." WHERE datetime BETWEEN DATE_SUB(CURDATE(), INTERVAL 0 DAY) AND DATE_SUB(DATE_ADD(CURDATE( ), INTERVAL 1 DAY), INTERVAL 0 DAY)";
			} else {
		   	$query="INSERT INTO ".CACHETABLENAME." (tablename, type, value, updatetime) SELECT '".MERGELOGTABLE."' as tablename, 'LPD' as type, count(*) as value, DATE_SUB(CURDATE(), INTERVAL 0 DAY) as updatetime FROM ".DEFAULTLOGTABLE." WHERE datetime BETWEEN DATE_SUB(CURDATE(), INTERVAL 0 DAY) AND DATE_SUB(DATE_ADD(CURDATE( ), INTERVAL 1 DAY), INTERVAL 0 DAY)";
			}
		   	perform_query($query, $dbLink);
	   	}
   	} else {
	   	echo "ERROR: ".CACHETABLENAME." is missing a column\n";
	   	echo "You will need to run the following command in mysql before using this script:\n";
	   	echo "ALTER TABLE ".CACHETABLENAME." MODIFY `type` enum('HOST','FACILITY','PROGRAM','LPD') default NULL;\n";
   	}
} else {
   	echo "\nLPD Cache is not enabled!\n";
   	echo "Please make sure the following variable is set in your config.php file:\n";
   	echo "define('LPD_CACHE', FALSE);\n";
}

echo "\n".date("Y-m-d H:i:s")."\n";
echo "All done!\n";
?>

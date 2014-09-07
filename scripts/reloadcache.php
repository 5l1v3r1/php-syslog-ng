#!/usr/bin/php
<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com
echo "\nStarting to reload cache\n";
echo date("Y-m-d H:i:s")."\n\n";

require_once "/www/php-syslog-ng/html/includes/common_funcs.php";
require_once "/www/php-syslog-ng/html/config/config.php";

$dbLink = db_connect_syslog(DBUSER, DBUSERPW);

// If merge table exists and is not empty
// then load the cache with data from that table
/* BEGIN REMOVE cdukes 2-27-08: Removed this check for MERGELOGTABLE
   I don't see why we're reloading cache from the MERGETABLE???
   Wouldn't that just make a cache of the all_logs data from midnight of each day and
   not current data?

if(table_exists(MERGELOGTABLE, $dbLink) == TRUE ) {
	$mergelog = TRUE;
	$sql = "SELECT * FROM ".MERGELOGTABLE." LIMIT 1";
	$result = perform_query($sql, $dbLink);
	if(num_rows($result)) {
	echo "Loading the cache with data from the merge table\n";
	reload_cache(MERGELOGTABLE, $dbLink);
	}
} else {
// Else load the cache with data from each log table
*/
	$tableArray = get_logtables($dbLink);
	foreach($tableArray as $table) {
		if ($table == MERGELOGTABLE) {
			continue;
		}
		echo "Loading the cache with data from: ".$table."\n";
		reload_cache($table, $dbLink);
	}
// }

// Delete rows with data from log tables that do not exist
echo "\nDeleting cache entries for tables that no longer exist...\n";
$tableArray = get_logtables($dbLink);

$sql = "SELECT DISTINCT tablename FROM ".CACHETABLENAME;
$result = perform_query($sql, $dbLink);
while($row = fetch_array($result)) {
	if(array_search($row['tablename'], $tableArray) === FALSE) {
		$sql = "DELETE FROM ".CACHETABLENAME." WHERE tablename='".$row['tablename']."'";
		perform_query($sql, $dbLink);
	}
}

if($mergelog) {
	$sql = "DELETE FROM ".CACHETABLENAME." WHERE tablename!='".MERGELOGTABLE."'";
	perform_query($sql, $dbLink);
}

echo "\n".date("Y-m-d H:i:s")."\n";
echo "Reloadcache Completed!\n";

include ("lpdcache.php");
?>

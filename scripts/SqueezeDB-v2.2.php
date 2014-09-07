<?php
/*
 * squeezedb.php
 *
 * Developed by Clayton Dukes <cdukes@cdukes.com>
 * Copyright (c) 2008 http://nms.gdd.net
 * Licensed under terms of GNU General Public License.
 * All rights reserved.
 *
 * Changelog:
 * 2008-02-08 - created
 * 2008-02-10 - Fixed count bug (db inserts were calculating incorrect count)

 *  $Log: SqueezeDB-v2.2.php,v $
 *  Revision 2.2  2008/07/21 06:13:04  cdukes
 *  Added test for max iterations due to excessive memory consumption in arrays for large systems.
 *  Set $max to whatever your system can handle
 *  Memory usage will print during run so you can test for your needs.
 *  This will allow you to run the script more often to deduplicate messages on larger systems
 *  For example, you can now just create a small shell script instead of cron to run every x seconds:
 * i=0
 * while [ $i -le 1000000 ]
 *  do
 *    php /www/php-syslog-ng/scripts/SqueezeDB-v2.2.php
 *	    sleep 3
 *		    i=`expr $i + 1`
 *			done
 *
 *
 * Ethan's RCS log:
 *
 *  $Log: SqueezeDB-v2.0.php,v $
 *  Revision 1.3  2008/03/01 06:13:04  ethan
 *  Implemented database transaction functions.  Lowered $matchpercent from 95 to 90.
 *
 *  Revision 1.2  2008/03/01 02:58:10  ethan
 *  Modified matching strings to include hostname like Clayton's
 *  original algorithm.
 *
 *  Revision 1.1  2008/03/01 01:02:32  ethan
 *  Initial revision
 *
 *
 */

/*
   NOTE: Versions of php-syslog-ng 2.9.5d and below will need to alter the database to use this script
   ALTER TABLE logs ADD counter INT NOT NULL DEFAULT 1;
   ALTER TABLE logs ADD fo datetime default NULL;
   ALTER TABLE logs ADD lo datetime default NULL;

   Basic Usage:
   $s = "/USR/SBIN/CRON[10749]: (root) CMD (php /www/php-syslog-ng/scripts/reloadcache.php >> /var/log/php-syslog-ng/reloadcache.log)";
   $s2 = "/USR/SBIN/CRON[10849]: (root) CMD (php /www/php-syslog-ng/scripts/reloadcache.php >> /var/log/php-syslog-ng/reloadcache.log)";
   similar_text($s, $s2, $p);
   echo "Percent: $p%";

   Description:
   This calculates the similarity between two strings as described in Oliver [1993]. 
   Note that this implementation does not use a stack as in Oliver's pseudo code, but 
   recursive calls which may or may not speed up the whole process. Note also that the 
   complexity of this algorithm is O(N**3) where N is the length of the longest string.
   
   Once a match is made, the source row is updated with a "count" of the destination (compared to) row.
   It also logs the "first occurance" and "last occurance" of the message so that you can get an idea
   of how long the message has been repeating.

   Why is this useful?
   Running this script makes a HUGE difference on the amount of data you have to store, and subsequently, search through
   to get to what you really want - an answer to the # 1 question for most customers: Where are my problem children?

   Example:
   When tested on a smaller database, I get the following results:
   // Starting Row Count = 12832
   // Ending Row Count = 1770
   // Cleaned 11062 records saving 86 percent
   // Squeeze finished in 1318.39662099 seconds (9.7 MPS)
   (note: I hope to get this script to run faster/more efficiently in the future, if you have ideas, please let me know!)
   (note2: The amount of time it takes to run may vary greatly for you based on hardware 
   - it will take a long time on the first run, but subsequent runs should improve markedly.
   Update for v2.0 Script:
   Starting Row Count = 4271
   Ending Row Count = 170
   Cleaned 4101 records saving 96 percent
   Squeeze finished in 2.07 seconds (2063.29 MPS)

   Now, we can quickly pull valuable data from the (much smaller!) databse by doing:
   (this will show the top 10 "problem" children")
   mysql> SELECT host,counter,msg from logs WHERE counter>1 ORDER BY counter DESC limit 10;

   +--------+---------+-----------------------------------------------------------------------------------------------------------------------------------------------------+
   | host    | counter | msg
   +--------+---------+-----------------------------------------------------------------------------------------------------------------------------------------------------+
   | host-a |     668 | 466927:         If Cisco determines that your insertion of non-Cisco memory, WIC cards,
   |
   | host-b |     644 | 466928:         AIM cards, Network Modules, SPA cards, GBICs or other modules into a
   |
   | host-c |     614 | 466925:         The module in slot 4 in this router may not be a genuine Cisco product.
   |
   | host-d |     585 | 466926:         Cisco warranties and support programs only apply to genuine Cisco products.
   |
   | host-e |     572 | 466929:         Cisco product is the cause of a support issue, Cisco may deny support under
   |
   | host-f |     385 | 466930:         your warranty or under a Cisco support program such as SmartNet.
   |
   | host-g |     215 | 467114:         your warranty or under a Cisco support program such as SmartNet.
   |
   | host-h |     165 | 32574: %CDP-4-DUPLEX_MISMATCH: duplex mismatch discovered on FastEthernet1/26 (not full duplex), with 7200-3 FastEthernet1/0 (full duplex).  
   |
   | host-i |     133 | /USR/SBIN/CRON[2858]: (root) CMD (php /www/php-syslog-ng/scripts/reloadcache.php >> /var/log/php-syslog-ng/reloadcache.log)
   |
   | host-j |     133 | /USR/SBIN/CRON[2861]: (root) CMD (php /www/rtpnml-xray/htdocs/cacti/poller.php > /dev/null 2>&1)
   |
   +--------+---------+---------------------------------------------------------------------------------------------------------------------------------------------------+
   10 rows in set (0.01 sec)

   As you can see, most of the "spammy" messages are useless and could easily be ignored or deleted (and definitely shouldn't be taking up so many rows!)
   Also note that one of the top issues listed is CDP Duplex (gee there's a surprise) - maybe we should go fix that host/device!

   This is an excellent way (and much faster) to get your Top X problem children with an added benefit of trimming 85-95% of the fat off your database!"
*/

$max_sql_in_list_count = 255;

include_once "/www/php-syslog-ng/html/includes/common_funcs.php";
include_once "/www/php-syslog-ng/html/config/config.php";


$time_start = get_microtime();
echo "\nStarting Squeeze\n";

// Open connection to DB
$dbLink = db_connect_syslog(DBADMIN, DBADMINPW);

//========================================================================
// START: Set level to match on
// Possible values are 1 to 99.9999999999
// I highly recommend NOT using 1 unless you want to lose all your data...
//========================================================================

// Original value in Clayton's code (things go much faster at 90):
#$matchpercent = 95;
$matchpercent = 86;
// CDUKES: Jun 18, 2008: Changed this to 86%, it seemed to work much better for me in finding duplicate rows

//------------------------------------------------------------------------
// Grab all rows by sequence # (the primary key) for processing
//------------------------------------------------------------------------
// if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
//    	$query = "SELECT SQL_CALC_FOUND_ROWS seq from " .DEFAULTLOGTABLE ." ORDER BY seq ASC";
// }
// else {
//    	$query = "SELECT seq from " . DEFAULTLOGTABLE . " ORDER BY seq ASC";
// }


// Set a limit to avoid puking
// $limit = "LIMIT 50000";

$query = "SELECT * from " . DEFAULTLOGTABLE . " ORDER BY seq ASC $limit";



$result = perform_query($query, $dbLink);

//------------------------------------------------------------------------
// Get row count
//------------------------------------------------------------------------
if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
	$num_results_array = perform_query("SELECT FOUND_ROWS()", $dbLink);
   	$num_results_array = fetch_array($num_results_array);
   	$num_rows = $num_results_array[0];
} else {
   	$num_rows = mysql_num_rows($result);
}
echo "$num_rows total rows to process\n";



//------------------------------------------------------------------------
// Begin outer loop
// Loop through each record and collect information in a multi-dimensional
// associative array keyed by host.
//------------------------------------------------------------------------
$exact_matches = 0;
$similar_matches = 0;
$rowcnt = 1;
error_reporting(E_ALL);
$host_message_cache = array();
$max = 8000; // Max messages to process at a single time
while ($row = fetch_array($result)) {
   	if (count($host_message_cache, COUNT_RECURSIVE) < $max ) {
	   	//print_r($row); exit();
	   	$r_host = $row['host'];
	   	$r_message = $row['msg'];
	   	$r_seq = $row['seq'];
	   	$r_entry_timestamp = $row['datetime'];
	   	$r_fo = $row['fo'];
	   	$r_lo = $row['lo']; 

		if (array_key_exists($r_host, $host_message_cache))
	   	{
		   	// Host already in cache.  Update or create message entry.

			$message_found = false;
		   	$messages = $host_message_cache[$r_host];

			// First check for exact match (on key value).  Otherwise, do the
		   	// similar_text() (expensive) check.

			if (array_key_exists($r_message, $host_message_cache[$r_host]))
		   	{
			   	// Found match in existing message set.  Update stats:
			   	// echo "Found match in existing message set.  Update stats...\n";
			   	$host_message_cache[$r_host][$r_message]['child_seqs'][]
				   	= $r_seq;
			   	$host_message_cache[$r_host][$r_message]['last_message_time']
				   	= $r_lo ? $r_lo : $r_entry_timestamp;
			   	$message_found = true;
			   	$exact_matches++;
		   	}
		   	else
		   	{
			   	$tc=0;
			   	foreach ($messages as $message => $message_data)
			   	{
				   	// Note: similar_text should be changed to return the matchpercent,
				   	// instead of returning value by side effect.
				  	//   (The host . message concatenation is done for consistency
				   	//    with Clayton's original algorithm).
				  	similar_text("$r_host,$r_message", "$r_host,$message", $p);	
					/*
					   if ($r_host = "11.16.254.160") { 
					   echo "DEBUG $p\n\t$r_host,$r_message\n\t$r_host,$message\n";
					   }
					   if ($tc > 120) {
					   exit;
					   }
					 */
				   	if ($p >= $matchpercent)
				   	{
					   	// Found match in existing message set.  Update stats:
					   	// echo "MATCH: $r_host,$r_message", "$r_host,$message, $p\n ";
					   	$host_message_cache[$r_host][$message]['child_seqs'][]
						   	= $r_seq;
					   	$host_message_cache[$r_host][$message]['last_message_time']
						   	= $r_lo ? $r_lo : $r_entry_timestamp;

						$message_found = true;
					   	$similar_matches++;
					   	// echo "$similar_matches matches\n";
					   	break;
				   	}
				   	$tc++;
			   	}
		   	}
		   	if (!$message_found)
		   	{
			   	// Found not match in existing message set.  Create new entry.
			  	$message_info = array('parent_seq' => $r_seq,
					   	'child_seqs' => array(),
					   	'first_message_time' => $r_entry_timestamp,
					   	'last_message_time' => $r_lo ? $r_lo : $r_entry_timestamp);
			   	$host_message_cache[$r_host][$r_message] = $message_info;
		   	}
	   	}
	   	else
	   	{
		   	// Host didn't exist in cache, so create an entry for it

			$message_info = array('parent_seq' => $r_seq,
				   	'child_seqs' => array(),
				   	'first_message_time' => $r_entry_timestamp,
				   	'last_message_time' => $r_lo ? $r_lo : $r_entry_timestamp);
		   	$host_message_cache[$r_host][$r_message] = $message_info;

		}

		if (($rowcnt++ % 1000 == 0))
	   	{
		   	// print_r($host_message_cache);
		   	// print " $rowcnt\n ";
		   	echo "Row $rowcnt Memory used: ".number_format(memory_get_usage())." bytes\n";
		   	// exit();
	   	}

	} 
	else {
	   	// print_r($host_message_cache);
	   	break;
   	}
}

#print_r ($host_message_cache); exit;

// We now have all of the information in the $host_message_cache.  Loop over
// each message for each host and modify the database accordingly by
// updating the message parent row's stats and deleting child rows.

// We are doing a lot of database modification.  It should all be part of the same transaction.

print "\n\n";
print "Debug: Log table analysis complete.\n";
print "Debug: Exact log message matches: $exact_matches\n";
print "Debug: Similar log message matches: $similar_matches\n";


print "Debug: Starting log table modifications...\n";
$db_time_start = get_microtime();

begin_transaction($dbLink);
foreach ($host_message_cache as $host => $messages)
{
   	foreach ($messages as $message => $message_data)
   	{
	   	if (!$message_data['child_seqs'])
		   	continue; // Solitary message with no associations.


		// Bugfix: http://code.google.com/p/php-syslog-ng/issues/detail?id=70
	   	// $count = count($message_data['child_seqs']) + 1; // Children + parent
	   	$parent_seq = $message_data['parent_seq'];
	   	$query_count = "SELECT counter FROM " . DEFAULTLOGTABLE . " WHERE seq = '$parent_seq'";
	   	$result_count = perform_query($query_count, $dbLink);
	   	$row_count = fetch_array($result_count);
	   	$r_counter = $row_count['counter'];

		if (($r_counter == '') || ($r_counter == 0))
	   	{
		   	$count = count($message_data['child_seqs']) + 1; // Children + parent
	   	}
	   	else
	   	{
		   	$count = count($message_data['child_seqs']) + $r_counter; // Children + parent
	   	}
	   	$fo = $message_data['first_message_time'];
	   	$lo = $message_data['last_message_time'];
	   	$query = "UPDATE " . DEFAULTLOGTABLE . "  SET counter = $count, fo = '$fo', lo = '$lo' WHERE seq = '$parent_seq'";

		// Update the parent record to reflect count and start, end times.
	  	if (! perform_query($query, $dbLink)) // Assuming that your perform_query returns false on error
	   	{
		   	rollback($dbLink);
		   	die("Error: Update failed.");
	   	}

		// Delete all the child records.

		// Create a set of SQL IN lists (e.g. "('seq1', 'seq2',...).  You
	   	// need a set because there can be limits of 255 IN list members.
	  	// print "Splitting lists into array of 255\n";
	   	$lists = array_chunk($message_data['child_seqs'], $max_sql_in_list_count);
	   	foreach ($lists as $list)
	   	{
		   	$in_list = create_sql_in_list($list);
		   	$query = "DELETE FROM " . DEFAULTLOGTABLE . " WHERE seq IN $in_list";
		   	// print "Processing in list\n";
		   	if (! perform_query($query, $dbLink)) // Assuming that your perform_query returns false on error
		   	{
			   	rollback($dbLink);
			   	die("Error: Delete failed.");
		   	}
	   	}
   	}
}

commit($dbLink);

$dbsecs = get_microtime() - $db_time_start;
print "Debug: Log table modifications complete in $dbsecs seconds...\n";

//------------------------------------------------------------------------
// Gather and spit out some stats
//------------------------------------------------------------------------

$query = 'SELECT count(*) AS "count" from ' . DEFAULTLOGTABLE ;
$result = perform_query($query, $dbLink);
$row = fetch_array($result);
$num_rows_after = $row['count'];
$savings = $num_rows - $num_rows_after;
$savings_p = round( ($savings/$num_rows)*100, 0 );
echo "\nStarting Row Count = $num_rows\n";
echo "Ending Row Count = $num_rows_after\n";
echo "Cleaned $savings records saving $savings_p percent\n";
$time_end = get_microtime();
$exetime = round($time_end - $time_start, 2);
$mps = round($num_rows/$exetime, 2);
echo "Squeeze finished in ".$exetime." seconds ($mps MPS)\n";
//========================================================================
// END
//========================================================================

///// Functions (should probably be put in include file common functions

/**
 * Create an SQL in list '(val1,val2,...,valn)' from an array.  If any
 * elements are not numeric, treat all as characters.
 * One may also use the $force_string arg to force a string type.
 */
function create_sql_in_list($vals, $force_string = false)
{
   	$is_str = array_filter($vals,
		   	create_function('$val', 'return (! is_numeric($val));'));

	$is_str = $is_str || $force_string;
   	$res = '(';
   	$sep = '';
   	foreach ($vals as $val)
   	{
	   	$res .= $sep;
	   	if ($is_str)
		   	$res .= "'";
	   	$res .= $val;
	   	if ($is_str)
		   	$res .= "'";
	   	$sep = ',';
   	}
   	$res .= ')';
   	return $res;
}

/**
 * Database transaction functions.
 *
 *  
 */

function begin_transaction($dbLink)
{
   	mysql_query('begin');
}

function rollback($dbLink)
{
   	mysql_query('rollback');
}

function commit($dbLink)
{
   	mysql_query('commit');
}
?>

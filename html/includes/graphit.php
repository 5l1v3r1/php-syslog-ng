<?php
/*
 * graphit.php
 *
 * Developed by Clayton Dukes <cdukes@cisco.com>
 * Copyright (c) 2006 Cisco Systems, Inc.
 * Licensed under terms of GNU General Public License.
 * All rights reserved.
 *
 * Changelog:
 * 2006-07-11 - created
 *
 */

/* $Platon$ */

/* Modeline for ViM {{{
 * vim: set ts=4:
 * vim600: fdm=marker fdl=0 fdc=0:
 * }}} */

$basePath = dirname( __FILE__ );
require_once($basePath . "/jpgraph/jpgraph.php");
require_once($basePath . "/jpgraph/jpgraph_pie.php");
require_once($basePath . "/jpgraph/jpgraph_pie3d.php");
require_once($basePath . "/common_funcs.php");
require_once($basePath . "/html_header.php");

//========================================================================
// BEGIN: GET THE INPUT VARIABLES
//========================================================================
$host = get_input('host');
$host2 = get_input('host2');
$excludeHost = get_input('excludeHost');
$facility = get_input('facility');
$excludeFacility = get_input('excludeFacility');
$priority = get_input('priority');
$excludePriority = get_input('excludePriority');
$date = get_input('date');
$date2 = get_input('date2');
$time = get_input('time');
$time2 = get_input('time2');
$limit = get_input('limit');
$topx = get_input('topx');
$orderby = "host";
$order = get_input('order');
$offset = get_input('offset');
if(!$offset) {
	$offset = 0;
}
$collapse = get_input('collapse');
$table = get_input('table');

// Set an arbitrary number of msg# and ExcludeMsg# vars
$msgvarnum=1;
$msgvarname="msg".$msgvarnum;
$excmsgvarname="ExcludeMsg".$msgvarnum;

while(get_input($msgvarname)) {
	${$msgvarname} = get_input($msgvarname);
	${$excmsgvarname} = get_input($excmsgvarname);

	$msgvarnum++;
	$msgvarname="msg".$msgvarnum;
	$excmsgvarname="ExcludeMsg".$msgvarnum;
}
//========================================================================
// END: GET THE INPUT VARIABLES
//========================================================================

/* BEGIN: Added by BPK to save search form variables into the session. */
$_SESSION['host'] = (isset($host)) ? $host : '';   
$_SESSION['host2'] = (isset($host2)) ? $host2 : '';
$_SESSION['excludeHost'] = (isset($excludeHost)) ? $excludeHost : '';
$_SESSION['regexpHost'] = (isset($regexpHost)) ? $regexpHost : '';
$_SESSION['program'] = (isset($program)) ? $program : '';   
$_SESSION['program2'] = (isset($program2)) ? $program2 : '';
$_SESSION['excludeProgram'] = (isset($excludeProgram)) ? $excludeProgram : '';
$_SESSION['regexpProgram'] = (isset($regexpProgram)) ? $regexpProgram : '';
$_SESSION['facility'] = (isset($facility)) ? $facility : '';
$_SESSION['excludeFacility'] = (isset($excludeFacility)) ? $excludeFacility : '';
$_SESSION['priority'] = (isset($priority)) ? $priority : '';
$_SESSION['excludePriority'] = (isset($excludePriority)) ? $excludePriority : '';
$_SESSION['date'] = (isset($date)) ? $date : '';   
$_SESSION['date2'] = (isset($date2)) ? $date2 : '';
$_SESSION['time'] = (isset($time)) ? $time : '';   
$_SESSION['time2'] = (isset($time2)) ? $time2 : '';
$_SESSION['limit'] = (isset($limit)) ? $limit : '';
$_SESSION['orderby'] = (isset($orderby)) ? $orderby : '';
$_SESSION['order'] = (isset($order)) ? $order : '';   
$_SESSION['offset'] = (isset($offset)) ? $offset : '';
$_SESSION['collapse'] = (isset($collapse)) ? $collapse : '';
$_SESSION['table'] = (isset($table)) ? $table : '';
$_SESSION['topx'] = (isset($topx)) ? $topx : '';
for ($i=1; $i<=3; $i++) {
        $_SESSION['msg'.$i] = (isset(${'msg'.$i})?${'msg'.$i}:'');
        $_SESSION['ExcludeMsg'.$i] = (isset(${'ExcludeMsg'.$i})?${'ExcludeMsg'.$i}:'');
        $_SESSION['RegExpMsg'.$i] = (isset(${'RegExpMsg'.$i})?${'RegExpMsg'.$i}:'');
}
/* END: Added by BPK to save search form variables info the session. */

//========================================================================
// BEGIN: INPUT VALIDATION
//========================================================================
$inputValError = array();

if($excludeHost && !validate_input($excludeHost, 'excludeX')) {
	array_push($inputValError, "excludeHost");
}
if($host && !validate_input($host, 'host')) {
	array_push($inputValError, "host1");
}
if($host2 && !validate_input($host2, 'host')) {
	array_push($inputValError, "host2");
}
if($excludeFacility && !validate_input($excludeFacility, 'excludeX')) {
	array_push($inputValError, "excludeFacility");
}
if($facility && !validate_input($facility, 'facility')) {
	array_push($inputValError, "facility");
}
if($excludePriority && !validate_input($excludePriority, 'excludeX')) {
	array_push($inputValError, "excludePriority");
}
if($priority && !validate_input($priority, 'priority')) {
	array_push($inputValError, "priority");
}
if($time && !validate_input($time, 'time')) {
	array_push($inputValError, "time1");
}
if($time2 && !validate_input($time2, 'time')) {
	array_push($inputValError, "time2");
}
if($limit && !validate_input($limit, 'limit')) {
	array_push($inputValError, "limit");
}
if($topx && !validate_input($topx, 'topx')) {
	array_push($inputValError, "topx");
}
if($orderby && !validate_input($orderby, 'orderby')) {
	array_push($inputValError, "orderby");
}
if($order && !validate_input($order, 'order')) {
	array_push($inputValError, "order");
}
if(!validate_input($offset, 'offset')) {
	array_push($inputValError, "offset");
}
if($collapse && !validate_input($collapse, 'collapse')) {
	array_push($inputValError, "collapse");
}
if($table && !validate_input($table, 'table')) {
	array_push($inputValError, "table");
}

if($inputValError) {
	require_once ($basePath . "/html_header.php");
	echo "Input validation error! The following fields had the wrong format:<p>";
	foreach($inputValError as $value) {
		echo $value."<br>";
	}
	require_once ($basePath . "/html_footer.php");
	exit;
}
//========================================================================
// END: INPUT VALIDATION
//========================================================================

//========================================================================
// BEGIN: BUILD AND EXECUTE SQL STATEMENT
// AND BUILD PARAMETER LIST FOR HTML GETS
//========================================================================
//------------------------------------------------------------------------
// Create WHERE statement and GET parameter list
//------------------------------------------------------------------------
$where = "";
$ParamsGET = "&";

if($table) {
	$ParamsGET=$ParamsGET."table=".$table."&";
}

if($limit) {
	$ParamsGET=$ParamsGET."limit=".$limit."&";
}

if($topx) {
	$ParamsGET=$ParamsGET."limit=".$topx."&";
}

if($orderby) {
	$ParamsGET=$ParamsGET."orderby=".$orderby."&";
}

if($order) {
	$ParamsGET=$ParamsGET."order=".$order."&";
}

if($collapse) {
	$ParamsGET=$ParamsGET."collapse=".$collapse."&";
}

if($pageId) {
	$ParamsGET=$ParamsGET."pageId=".$pageId."&";
}

if($host2) {
	if ($where!="") {
		$where=$where." and ";
	}
	if($excludeHost==1) {
		$where = $where." host not like '%".$host2."%' ";
	}
	else {
		$where = $where." host like '%".$host2."%' ";
	}
	$ParamsGET=$ParamsGET."host2=".$host2."&excludeHost=".$excludeHost."&";
}

if($host) {
	$hostGET=implode("&host[]=",$host);
	$hostSQL=implode("','",$host);
	if($where!="") {
		$where = $where." and ";
	}
	if($excludeHost==1) {
		$where = $where." host not in ('".$hostSQL."') ";
	}
	else {
		$where = $where." host in ('".$hostSQL."') ";
	}
	$ParamsGET=$ParamsGET."host[]=".$hostGET."&excludeHost=".$excludeHost."&";	
}

if($facility) {
	$facilityGET=implode("&facility[]=",$facility);
	$facilitySQL=implode("','",$facility);
	if($where!="") {
		$where = $where." and ";
	}
	if($excludeFacility==1) {
		$where = $where." facility not in ('".$facilitySQL."') ";
	}
	else {
		$where = $where." facility in ('".$facilitySQL."') ";
	}
	$ParamsGET=$ParamsGET."facility[]=".$facilityGET."&excludeFacility=".$excludeFacility."&";
}

if($priority) {
	$priorityGET=implode("&priority[]=",$priority);
	$prioritySQL=implode("','",$priority);
	if($where!="") {
		$where = $where." and ";
	}
	if($excludePriority==1) {
		$where = $where." priority not in ('".$prioritySQL."') ";
	}
	else {
		$where = $where." priority in ('".$prioritySQL."') ";
	}
	$ParamsGET=$ParamsGET."priority[]=".$priorityGET."&excludePriority=".$excludePriority."&";
}

$datetime = "";
$datetime2 = "";

if($date) {
	$ParamsGET=$ParamsGET."date=".$date."&time=".$time."&";
	if(strcasecmp($date, 'now') == 0 || strcasecmp($date, 'today') == 0) {
		$date = date("Y-m-d");
	}
	elseif(strcasecmp($date, 'yesterday') == 0) {
		$date = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
	}
	if(!$time) {
		$time = "00:00:00";
	}
	elseif(strcasecmp($time, 'now') == 0) {
		$time = date("H:i:s");
	}
	$datetime = $date." ".$time ;
}
if($date2) {
	$ParamsGET=$ParamsGET."date2=".$date2."&time2=".$time2."&";
	if(strcasecmp($date2, 'now') == 0 || strcasecmp($date2, 'today') == 0) {
		$date2 = date("Y-m-d");
	}
	elseif(strcasecmp($date2, 'yesterday') == 0) {
		$date2 = date("Y-m-d", mktime(0, 0, 0, date("m")  , date("d")-1, date("Y")));
	}
	if(!$time2) {
		$time2 = "23:59:59";
	}
	elseif(strcasecmp($time2, 'now') == 0) {
		$time2 = date("H:i:s");
	}
	$datetime2 = $date2." ".$time2 ;
}

if($datetime && $datetime2) {
	if($where != "") {
		$where = $where." and ";
	}
	$where = $where." datetime between '".$datetime."' and '".$datetime2."' ";
}
elseif($datetime) {
	if($where != "") {
		$where = $where." and ";
	}
	$where = $where." datetime > '".$datetime."' ";
}
elseif($datetime2) {
	if($where != "") {
		$where = $where." and ";
	}
	$where = $where." datetime < '".$datetime2."' ";
}

$msgvarnum=1;
$msgvarname="msg".$msgvarnum;
$excmsgvarname="ExcludeMsg".$msgvarnum;

while(isset(${$msgvarname})) {
	if($where !="") {
		$where = $where." and ";
	}
	if(${$excmsgvarname} == "on") {
		$where = $where." msg not like '%".${$msgvarname}."%' ";
		$ParamsGET=$ParamsGET.$excmsgvarname."=".${$excmsgvarname}."&";
	}
	else {
		$where = $where." msg like '%".${$msgvarname}."%' ";
	}
	$ParamsGET=$ParamsGET.$msgvarname."=".${$msgvarname}."&";
	$msgvarnum++;
	$msgvarname="msg".$msgvarnum;
	$excmsgvarname="ExcludeMsg".$msgvarnum;
}

//------------------------------------------------------------------------
// Create the GET string without host variables
//------------------------------------------------------------------------
$pieces = explode("&", $ParamsGET);
$hostParamsGET = "";
foreach($pieces as $value) {
	if(!strstr($value, "host[]=") && !strstr($value, 'excludeHost=') && !strstr($value, 'offset=') && $value) {
		$hostParamsGET = $hostParamsGET.$value."&";
	}
}

//------------------------------------------------------------------------
// Create the complete SQL statement
// SQL_CALC_FOUND_ROWS is a MySQL 4.0 feature that allows you to get the
// total number of results if you had not used a LIMIT statement. Using
// it saves an extra query to get the total number of rows.
//------------------------------------------------------------------------
if($table) {
	$srcTable = $table;
}
else {
	$srcTable = DEFAULTLOGTABLE;
}
// CDUKES: Jun 18, 2008: Added in support of the SQZ feature
// CDUKES: Feb 23, 2009: Changed DEFAULTLOGTABLE to srcTable
// Ref: http://groups.google.com/group/php-syslog-ng-dev/browse_thread/thread/e3bddab850f2ea0a/e834f8d983a7f339?lnk=gst&q=dfound#e834f8d983a7f339
if(defined('SQZ_ENABLED') && SQZ_ENABLED == TRUE) {
   	$query = "SELECT host,SUM(counter) as count FROM ".$srcTable." ";
} else {
   	if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
	   	$query = "SELECT SQL_CALC_FOUND_ROWS host, count(host) as count FROM ".$srcTable." ";
   	}
   	else {
	   	$query = "SELECT host, count(host) as count FROM ".$srcTable." ";
   	}
}

if($where) {
	$query = $query."WHERE ".$where." GROUP by host ORDER BY count DESC LIMIT " .$topx;
}
else {
	$query = $query."GROUP by host ORDER BY count DESC LIMIT " .$topx;
}

//------------------------------------------------------------------------
// Execute the query
// The FOUND_ROWS function returns the value from the SQL_CALC_FOUND_ROWS
// count.
//------------------------------------------------------------------------
 $results = perform_query($query, $dbLink);
if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
	$num_results_array = perform_query("SELECT FOUND_ROWS()", $dbLink);
	$num_results_array = fetch_array($num_results_array);
	$num_results = $num_results_array[0];
}

//========================================================================
// END: BUILD AND EXECUTE SQL STATEMENT
// AND BUILD PARAMETER LIST FOR HTML GETS
//========================================================================

//========================================================================
// BEGIN: PREPARE RESULT ARRAY
//========================================================================
//------------------------------------------------------------------------
// Collapse consecutive identical messages into one line
//------------------------------------------------------------------------
/* 
if($collapse == 1) {
	$n = 0;
	while($row = fetch_array($results)) {
		if($row['msg'] == $result_array[$n-1]['msg'] 
				&& $row['host'] == $result_array[$n-1]['host']) {
			$result_array[$n-1]['count'] = $result_array[$n-1]['count'] + 1;
			$n--;
		}
		else {
			$row['count'] = 1;
			$result_array[$n] = $row;
		}
		$n++;
	}
}
else {
	$n = 0;
	while($row = fetch_array($results)) {
		$row['count'] = 1;
		$result_array[$n] = $row;
		$n++;
	}
}	
*/
//========================================================================
// END: PREPARE RESULT ARRAY
//========================================================================

//========================================================================
// BEGIN: BUILDING THE HTML PAGE
//========================================================================
// Print result sub-header
require_once 'includes/html_result_subheader.php';
// If there is a result list then print it
// if (count($result_array)){
	// die(print_r($result_array));

	//------------------------------------------------------------------------
	// If the query returned some results then start the table with the
	// results
	//------------------------------------------------------------------------

	?>
		<table align="center">
		<tr><td>
		<?php
		// Set up Graph variables
		$slice = 0;
	$type = "pie3d";
	$mapName = 'Top10'; 
	$fileName = IMG_CACHE_DIR . "Top10-" . time(3600) . ".png";

	if ( $slice > 0 ) {
		// include ("pageload.php");
		$url = SITEURL . "$ParamsGET";
		$mode = "JS";
		// die ("$url -- $mode -- $host");
		g_redirect($url,$mode);
		die ("Error in redirect to graph"); // just in case g_redir fails or something weird
	}

	// $query="SELECT host, count(host) as count FROM " . DEFAULTLOGTABLE . " WHERE $where GROUP BY host ORDER BY count DESC LIMIT $limit";
	// die($query);
	$result = perform_query($query, $dbLink) or die (mysql_error());
	$numrows = mysql_num_rows($result);
	// echo "$numrows Rows\n<br>";

	if ( $numrows < 1 ) { 
	die ("<center>No results found.<br><a href=\"index.php?pageId=searchform\">BACK TO SEARCH</a></center>");
	} else {
		while ($row = mysql_fetch_assoc($result)) {
			$count[]  = $row['count'];
			// Use something like below to filter off domain names
			// $host[]   = preg_replace("/\.tld.domain.*/", "", $row['host']);
			$host[]   = $row['host'];
		}
	}

// CDUKES: Jun 18, 2008: Added in support of the SQZ feature
if(defined('SQZ_ENABLED') && SQZ_ENABLED == TRUE) {
   	$query = "SELECT SUM(counter) FROM ".DEFAULTLOGTABLE." ";
		$result = perform_query($query, $dbLink) or die (mysql_error());
		 $row = fetch_array($result);
		$totalrows = commify($row[0]);
		  // die ("Total COUNT rows: $totalrows");
} else {
	if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE && $num_results) {
		$totalrows = commify(get_total_rows(DEFAULTLOGTABLE));
		 // die ("Total COUNT rows:" . $totalrows);
	} else {
		// Get Total number of rows
		$query="SELECT count(*) from " . DEFAULTLOGTABLE;
		$result = perform_query($query, $dbLink) or die (mysql_error());
		$numrows = mysql_num_rows($result);
		$totalrows = commify($numrows);
		// die ("Total rows: $query<br>" . $totalrows);
	}
}



	// A new pie graph
	$graph = new PieGraph(640,480,'auto');
	$graph->SetShadow();

	// Title setup
	/* cdukes - 2-28-08: Added a test to notify the user if they selected more TopX than what was available in the database
    Example: Selecting Top 100 when only 50 hosts are in the DB
	 */
   	$numhosts = (count($host)); 
   	// die("Hostcount:$numhosts \nTopx: $topx\n");
	if ($numhosts >= $topx) {
	$graph->title->Set("Top $topx Hosts of " . $totalrows . " total messages");
	} else {
	$graph->title->Set("Top $numhosts Hosts of " . $totalrows . " total messages\n(Unable to get Top $topx, You only have $numhosts hosts in the database)");
	$topx = $numhosts;
	}
	$graph->title->SetFont(FF_FONT1,FS_BOLD);

	// Setup the pie plot
	$p1 = new PiePlot3D($count);
	$p1->SetLegends($host);

	$targ = array();
	//count number of hosts for pie slices
	$array_count = count($host);
	for($y=0; $y<$array_count; $y++) {
		   if(isset($host[$y])) {
				  array_push($targ, $_SERVER["PHP_SELF"] . "?pageId=Search&slice=1&table=$table&excludeHost=0&host2=&host%5B%5D=$host[$y]&excludeFacility=1&excludePriority=1&date=$date&time=&date2=$date2&time2=&limit=100&orderby=datetime&order=DESC&msg1=&msg2=&msg3=&collapse=1");
				     }
		      else { $array_count++; }
	}
// die(print_r($targ));

	$p1->SetCSIMTargets($targ,$alts);

	// Horizontal: 'left','right','center'
	// Vertical: 'bottom','top','center' 
	$graph->legend->SetAbsPos(10,20,'right','top');
	// $graph->legend->Pos(0.5,0.5); 
	// $graph->legend->SetColumns(2); 
	$graph->legend->SetFont(FF_VERDANA,FS_NORMAL, 8);


	// Adjust size and position of plot
	$p1->SetSize(0.40);
	$p1->SetCenter(0.39,0.6);

	// Setup slice labels and move them into the plot
	$p1->value->SetFont(FF_FONT1,FS_BOLD);
	$p1->value->SetColor("darkred");
	$p1->SetLabelPos(0.70);

	// Set perM  > 1  below to enable per million labels
	$perM = 0;
	if ( $perM < 1 ) {
		$p1->SetLabelType(PIE_VALUE_ABS);
		$p1->value->SetFormat("%d");
	} else {
		$p1->SetLabelType(PIE_VALUE_PER); 
		$p1->value->SetFormat(".%dM");
	}
	// Set percentage to enable per percent labels
	$percentage = 1;
	if ( $percentage > 0 ) {
		$p1->SetLabelType(PIE_VALUE_ABS);
		$p1->value->SetFormat("%d%%");
		$p1->SetValueType(PIE_VALUE_PERCENTAGE);
	}


	// Set theme colors
	// Options are "earth", "sand", "water" and doodoo, no, I mean "pastel" :-)
	$p1->SetTheme("earth");

	// Explode all slices
	$p1->ExplodeAll($topx);

	// Add drop shadow
	$aColor = "darkgray";
	$p1->SetShadow($aColor);

	// Finally add the plot
	$graph->Add($p1);

	// ... and stroke it
	// $graph->Stroke();
	//$ih = $graph->Stroke(_IMG_HANDLE); 
	// $graph->StrokeCSIM("$graph_name");

	$graph->Stroke($fileName);

	// $mapName = 'Top10'; 
	$imgMap = $graph->GetHTMLImageMap($mapName);
	// die("?offset=".$offset. "PPP" .$ParamsGET);
	 echo "$imgMap <TD ALIGN=\"center\"><img src=\"$fileName\" alt=\"$mapName Graph - Click on slice to drill down\" ismap usemap=\"#$mapName\" border=\"0\"></TD></TR>";
	require_once 'includes/html_footer.php';

	//------------------------------------------------------------------------
	// Else just direct the user back to the form
	//------------------------------------------------------------------------
/* } else {
	echo "No results found.<br><a href=\"index.php?pageId=searchform\">BACK TO SEARCH</a>";
}
*/

//========================================================================
// END: BUILDING THE HTML PAGE
//========================================================================
?>

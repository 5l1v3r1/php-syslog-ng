<?php
// Copyright (c) 2001-2004 by Michael Earls, michael@michaelearls.com
// Copyright (c) 2004-2005 Jason Taylor, j@jtaylor.ca
// Copyright (c) 2005 Claus Lund, clauslund@gmail.com
// Copyright (c) 2006-2008 Clayton Dukes, cdukes@cdukes.com

require_once 'includes/html_header.php';

//========================================================================
// BEGIN: GET THE INPUT VARIABLES
//========================================================================
$host = get_input('host', false);
$host2 = get_input('host2', false);
$excludeHost = get_input('excludeHost', false);
/* BEGIN: RegExp and Program additions by BPK */
$regexpHost = get_input('regexpHost', false);
$program = get_input('program', false);
$program2 = get_input('program2', false);
$excludeProgram = get_input('excludeProgram', false);
$regexpProgram = get_input('regexpProgram', false);
/* END: RegExp and Program additions by BPK */
$facility = get_input('facility', false);
$excludeFacility = get_input('excludeFacility', false);
$priority = get_input('priority', false);
$excludePriority = get_input('excludePriority', false);
$date = get_input('date', false); 
/* BEGIN: Switched by BPK - if this is the default it should be displayed as such
   It's preferable to be able to search through an entire table structure without
   the need to know which dates it encompasses.
   if (! $date) { 
   $date = "yesterday"; 
   }   
   $date2 = get_input('date2'); 
   if (! $date2) { 
   $date = "today"; 
   }   
 */
$date2 = get_input('date2', false); 
/* END: Switched by BPK */
$time = get_input('time', false);
$time2 = get_input('time2', false);
$limit = get_input('limit', false);
$orderby = get_input('orderby', false);
$dupop = get_input('dupop', false);
$dupcount = get_input('dupcount', false);
$order = get_input('order', false);
$offset = get_input('offset', false);
if(!$offset) {
   	$offset = 0;
}
$collapse = get_input('collapse', false);
$table = get_input('table', false);

// Set an arbitrary number of msg# and ExcludeMsg# vars
$msgvarnum=1;
$msgvarname="msg".$msgvarnum;
$excmsgvarname="ExcludeMsg".$msgvarnum;
$regexpmsgvarname="RegExpMsg".$msgvarnum;

while(get_input($msgvarname, false)) {
   	${$msgvarname} = get_input($msgvarname, false);
   	${$excmsgvarname} = get_input($excmsgvarname, false);
   	${$regexpmsgvarname} = get_input($regexpmsgvarname, false);

	$msgvarnum++;
   	$msgvarname="msg".$msgvarnum;
   	$excmsgvarname="ExcludeMsg".$msgvarnum;
   	$regexpmsgvarname="RegExpMsg".$msgvarnum;
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
$_SESSION['dupop'] = (isset($dupop)) ? $dupop : '';
$_SESSION['dupcount'] = (isset($dupcount)) ? $dupcount : '';
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
/* BEGIN: RegExp and Program additions by BPK
   if($host2 && !validate_input($host2, 'host')) {
   array_push($inputValError, "host2");
   }
 */
if($regexpHost && !validate_input($regexpHost, 'regexpX')) {
   	array_push($inputValError, 'regexpHost');
}
if($host2 && (!$regexpHost && !validate_input($host2, 'host'))
	   	|| ($regexpHost && !validate_input($host2, 'hostRegExp'))) {
   	array_push($inputValError, 'host2');
}
if($excludeProgram && !validate_input($excludeProgram, 'excludeX')) {
   	array_push($inputValError, "excludeProgram");
}
if($program && !validate_input($program, 'program')) {
   	array_push($inputValError, "program1");
}
if($regexpProgram && !validate_input($regexpProgram, 'regexpX')) {
   	array_push($inputValError, 'regexpProgram');
}
if($program2 && (!$regexpProgram && !validate_input($program2, 'program'))
	   	|| ($regexpProgram && !validate_input($program2, 'programRegExp'))) {
   	array_push($inputValError, 'program2');
}
/* END: RegExp and Program additions by BPK */
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
if($orderby && !validate_input($orderby, 'orderby')) {
   	array_push($inputValError, "orderby");
}
if($dupop && !validate_input($dupop, 'dupop')) {
   	array_push($inputValError, "dupop");
}
if($dupcount && !validate_input($dupcount, 'dupcount')) {
   	array_push($inputValError, "dupcount");
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
   	require_once 'includes/html_header.php';
   	echo "Input validation error! The following fields had the wrong format:<p>";
   	foreach($inputValError as $value) {
	   	echo $value."<br>";
   	}
   	require_once 'includes/html_footer.php';
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

if($orderby) {
   	$ParamsGET=$ParamsGET."orderby=".$orderby."&";
}
if($dupop) {
   	$ParamsGET=$ParamsGET."dupop=".$dupop."&";
}
if($dupcount) {
   	$ParamsGET=$ParamsGET."dupcount=".$dupcount."&";
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

/* BEGIN: Switched by BPK to allow for host lists and regexp
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
 */
if($host2) {
   	if ($where!="") {
	   	$where=$where." AND ";
   	}
   	$clause = '';
   	if ($regexpHost==1) {
	   	$clause = "host RLIKE '".$host2."'";
   	}
   	else {
	   	$parts = preg_split('/\s*[,;]+\s*/', $host2);
	   	foreach ($parts as $part) {
		   	if (empty($part)) continue;
		   	$clause .= ($clause!='') ? ' OR ' : '';
		   	$clause .= "host LIKE '%${part}%'"; 
		}
   	}
   	$where .= (($excludeHost==1) ? 'NOT ' : '')."($clause)";
   	$ParamsGET=$ParamsGET."host2=".$host2."&excludeHost=".$excludeHost.
	  	"&regexpHost=".$regexpHost."&";
}
/* END: Switched by BPK to allow for host lists, programs, and regexp */

if($host) {
   	$hostGET=implode("&host[]=",$host);
   	$hostSQL=implode("','",$host);
   	if($where!="") {
	   	$where = $where." AND ";
   	}
   	if($excludeHost==1) {
	   	$where = $where." host NOT IN ('".$hostSQL."') ";
   	}
   	else {
	   	$where = $where." host IN ('".$hostSQL."') ";
   	}
   	$ParamsGET=$ParamsGET."host[]=".$hostGET."&excludeHost=".$excludeHost."&";	
}
/* BEGIN: Added by BPK to allow for program lists and regexp */
if($program2) {
   	if ($where!="") {
	   	$where=$where." AND ";
   	}
   	$clause = '';
   	if ($regexpProgram==1) {
	   	$clause = "program RLIKE '".$program2."'";
   	}
   	else {
	   	$parts = preg_split('/\s*[,;]+\s*/', $program2);
	   	foreach ($parts as $part) {
		   	if (empty($part)) continue;
		   	$clause .= ($clause!='') ? ' OR ' : '';
		   	$clause .= "program LIKE '%${part}%'";
	   	}
   	}
   	$where .= (($excludeProgram==1) ? 'NOT ' : '')."($clause)";
   	$ParamsGET=$ParamsGET."program2=".$program2."&excludeProgram=".$excludeProgram.
	  	"&regexpProgram=".$regexpProgram."&";
}

if($program) {
   	$programGET=implode("&program[]=",$program);
   	$programSQL=implode("','",$program);
   	if($where!="") {
	   	$where = $where." AND ";
   	}
   	if($excludeProgram==1) {
	   	$where = $where." program NOT IN ('".$programSQL."') ";
   	}
   	else {
	   	$where = $where." program IN ('".$programSQL."') ";
   	}
   	$ParamsGET=$ParamsGET."program[]=".$programGET."&excludeProgram=".$excludeProgram."&";
}
/* END: Added by BPK to allow for program lists and regexp */

if($facility) {
   	$facilityGET=implode("&facility[]=",$facility);
   	$facilitySQL=implode("','",$facility);
   	if($where!="") {
	   	$where = $where." AND ";
   	}
   	if($excludeFacility==1) {
	   	$where = $where." facility NOT IN ('".$facilitySQL."') ";
   	}
   	else {
	   	$where = $where." facility IN ('".$facilitySQL."') ";
   	}
   	$ParamsGET=$ParamsGET."facility[]=".$facilityGET."&excludeFacility=".$excludeFacility."&";
}

if($priority) {
   	$priorityGET=implode("&priority[]=",$priority);
   	$prioritySQL=implode("','",$priority);
   	if($where!="") {
	   	$where = $where." AND ";
   	}
   	if($excludePriority==1) {
	   	$where = $where." priority NOT IN ('".$prioritySQL."') ";
   	}
   	else {
	   	$where = $where." priority IN ('".$prioritySQL."') ";
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
	list($month, $day, $year) = split('[/.-]', $date);
	if (preg_match("/(\d){4}/i", "$year")) {
   	$datetime = $year ."-" .$month ."-" .$day ." ".$time ;
	} else {
	$datetime = $date." ".$time ;
	}
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
	list($month2, $day2, $year2) = split('[/.-]', $date2);
	if (preg_match("/(\d){4}/i", "$year2")) {
   	$datetime2 = $year2 ."-" .$month2 ."-" .$day2 ." ".$time2 ;
	} else {
	$datetime2 = $date2." ".$time2 ;
	}
}

if($datetime && $datetime2) {
   	if($where != "") {
	   	$where = $where." AND ";
   	}
   	$where = $where." datetime between '".$datetime."' AND '".$datetime2."' ";
}
elseif($datetime) {
   	if($where != "") {
	   	$where = $where." AND ";
   	}
   	$where = $where." datetime > '".$datetime."' ";
}
elseif($datetime2) {
   	if($where != "") {
	   	$where = $where." AND ";
   	}
   	$where = $where." datetime < '".$datetime2."' ";
}
(!$dupcount) ? ($dupcount = 0) : ($dupcount);
if($dupop) {
   	if($where != "") {
	   	$where = $where." AND ";
   	}
   	switch ($dupop) {
	   	case "gt":
		   	$dupop = ">";
	   	break;

		case "lt":
		   	$dupop = "<";
	   	break;

		case "eq":
		   	$dupop = "=";
	   	break;

		case "gte":
		   	$dupop = ">=";
	   	break;

		case "lte":
		   	$dupop = "<=";
	   	break;

		default:
	   	$dupop = ">=";
   	}
   	$where = $where." counter $dupop '".$dupcount."' ";
}

$msgvarnum=1;
$msgvarname="msg".$msgvarnum;
$excmsgvarname="ExcludeMsg".$msgvarnum;
$regexpmsgvarname="RegExpMsg".$msgvarnum;

/** BEGIN: Switched by BPK to allow from regexp
  while(${$msgvarname}) {
  if($where !="") {
  $where = $where." AND ";
  }
  if(${$excmsgvarname} == "on") {
  $where = $where." msg NOT LIKE '%".${$msgvarname}."%' ";
  $ParamsGET=$ParamsGET.$excmsgvarname."=".${$excmsgvarname}."&";
  }
  else {
  $where = $where." msg LIKE '%".${$msgvarname}."%' ";
  }
  $ParamsGET=$ParamsGET.$msgvarname."=".${$msgvarname}."&";
  $msgvarnum++;
  $msgvarname="msg".$msgvarnum;
  $excmsgvarname="ExcludeMsg".$msgvarnum;
  }
 */
while(isset(${$msgvarname})) {
   	if($where !="") {
	   	$where = $where." AND ";
   	}
   	$where .= 'msg ';
   	if(${$excmsgvarname} == "on") {
	   	$where .= 'NOT ';
	   	$ParamsGET = $ParamsGET.$excmsgvarname."=".${$excmsgvarname}."&";
   	}
   	if(${$regexpmsgvarname} == "on") {
	   	$where .= "RLIKE '".${$msgvarname}."' ";
	   	$ParamsGET = $ParamsGET.$regexpmsgvarname."=".${$regexpmsgvarname}."&";
   	}
   	else {
	   	$where .= "LIKE '%".${$msgvarname}."%' ";
   	}
   	$ParamsGET=$ParamsGET.$msgvarname."=".${$msgvarname}."&";
   	$msgvarnum++;
   	$msgvarname="msg".$msgvarnum;
   	$excmsgvarname="ExcludeMsg".$msgvarnum;
   	$regexpmsgvarname = "RegExpMsg".$msgvarnum;
}
/** END: Switched by BPK to allow from regexp */

//------------------------------------------------------------------------
// Create the GET string without host variables
//------------------------------------------------------------------------
$pieces = explode("&", $ParamsGET);
$hostParamsGET = "";
foreach($pieces as $value) {
   	// if(!strstr($value, "host[]=") && !strstr($value, 'excludeHost=') && !strstr($value, 'offset=') && $value) {
   	if(!strstr($value, "host[]=") && !strstr($value, 'excludeHost=') && !strstr($value, 'regexpHost=') && !strstr($value, 'offset=') && $value) {
	   	$hostParamsGET = $hostParamsGET.$value."&";
   	}
}

/* BEGIN: added by BPK to create GET string without facility, program, order* variables */
$pieces = explode("&", $ParamsGET);
$programParamsGET = "";
foreach($pieces as $value) {
   	if(!strstr($value, "program[]=") && !strstr($value, 'excludeProgram=') && !strstr($value, 'regexpProgram=') && !strstr($value, 'offset=') && $value) {
	   	$programParamsGET = $programParamsGET.$value."&";
   	}
}

$pieces = explode("&", $ParamsGET);
$facilityParamsGET = "";
foreach($pieces as $value) {
   	if(!strstr($value, "facility[]=") && !strstr($value, 'excludeFacility=') && !strstr($value, 'offset=') && $value) {
	   	$facilityParamsGET = $facilityParamsGET.$value."&";
   	}
}

$pieces = explode("&", $ParamsGET);
$orderParamsGET = "";
foreach($pieces as $value) {
   	if(!strstr($value, "order=") && !strstr($value, 'orderby=') && !strstr($value, 'offset=') && $value) {
	   	$orderParamsGET = $orderParamsGET.$value."&";
   	}
}
/* END: added by BPK to create GET string without facility, program, order* variables */

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

if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE) {
   	$query = "SELECT SQL_CALC_FOUND_ROWS * FROM ".$srcTable." ";
}
else {
   	$query = "SELECT * FROM ".$srcTable." ";
}

if($where) {
   	$query = $query."WHERE ".$where." ORDER BY ".$orderby." ".$order." LIMIT ".$offset.", ".$limit;
}
else {
   	$query = $query."ORDER BY ".$orderby." ".$order." LIMIT ".$offset.", ".$limit;
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
if($collapse == 1) {
   	$n = 0;
   	while($row = fetch_array($results)) {
	   	//		if($n>0 && $row['msg'] == $result_array[$n-1]['msg'] 
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
//========================================================================
// END: PREPARE RESULT ARRAY
//========================================================================

//========================================================================
// BEGIN: BUILDING THE HTML PAGE
//========================================================================
// Print result sub-header
require_once 'includes/html_result_subheader.php';

// If there is a result list then print it
if (count($result_array)){

	//------------------------------------------------------------------------
   	// If the query returned some results then start the table with the
   	// results
   	//------------------------------------------------------------------------
   	?>
	   	<table width=100%>
	   	<tr class="resultsheader">
	   	<!-- BEGIN: Switched by BPK to allow for sorting via links
	   	<td>SEQ</td>
	   	<td>HOST</td>
	   	<td>FACILITY</td>
	   	<td>DATE TIME</td>
	   	<td>MESSAGE</td>
	   	-->
	   	<?php
	   	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?orderby=id&order='
	   	.(($orderby=='id' && $order=='DESC') ? 'ASC' : 'DESC').'&'.$orderParamsGET.'">SEQ</a></td>';
   	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?orderby=host&order='
	   	.(($orderby=='host' && $order=='DESC') ? 'ASC' : 'DESC').'&'.$orderParamsGET.'">HOST</a></td>';
   	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?orderby=facility&order='
	   	.(($orderby=='facility' && $order=='DESC') ? 'ASC' : 'DESC').'&'.$orderParamsGET.'">FACILITY</a></td>';
   	if(defined('SQZ_ENABLED') && SQZ_ENABLED == TRUE) {
	   	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?orderby=fo&order='
		   	.(($orderby=='fo' && $order=='DESC') ? 'ASC' : 'DESC').'&'.$orderParamsGET.'">FO</a></td>';
	   	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?orderby=lo&order='
		   	.(($orderby=='lo' && $order=='DESC') ? 'ASC' : 'DESC').'&'.$orderParamsGET.'">LO</a></td>';
	   	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?orderby=counter&order='
		   	.(($orderby=='counter' && $order=='DESC') ? 'ASC' : 'DESC').'&'.$orderParamsGET.'">COUNT</a></td>';
   	}
   	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?orderby=datetime&order='
	   	.(($orderby=='datetime' && $order=='DESC') ? 'ASC' : 'DESC').'&'.$orderParamsGET.'">DATE TIME</a></td>';
   	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?orderby=program&order='
	   	.(($orderby=='program' && $order=='DESC') ? 'ASC' : 'DESC').'&'.$orderParamsGET.'">PROGRAM</a></td>';
   	echo '<td><a href="'.$_SERVER['PHP_SELF'].'?orderby=msg&order='
	   	.(($orderby=='msg' && $order=='DESC') ? 'ASC' : 'DESC').'&'.$orderParamsGET.'">MESSAGE</a></td>';
   	?>
	   	<!-- END: Switched by BPK to allow for sorting via links -->
	   	</tr>
	   	<?php
	   	//------------------------------------------------------------------------
	   	// Output the table with the results
	   	// Use an alternating background and color code the priority column
	   	//------------------------------------------------------------------------
	   	if(CEMDB == "ON") {
		   	require_once 'includes/CEMDB.class.php';
		   	$cemdb = new CEMDB($dbLink);
	   	}

	$color = "lighter";
   	$today = date('Y-m-d');
   	for($i=0; $i < count($result_array); $i++) {
	   	$row = $result_array[$i];
	   	if($color == "darker") {
		   	$color = "lighter";
	   	}
	   	else {
		   	$color = "darker";
	   	}
	   	echo "<tr class=\"$color\">";
		list($id) = split(':', $row['msg']);
	   	if(is_numeric($id)) {
	   	echo "<td>".$id."</td>";
		} else {
	   	echo "<td>N/A</td>";
		}

		echo "<td><a href=\"".$_SERVER["PHP_SELF"]."?excludeHost=0&host[]=".$row['host']."&".$hostParamsGET."\">";
	   	echo $row['host']."</a></td>";

		// 3rd column: Spit out the colour-coded FACILITY/PRIORITY fields.
	  	/* BEGIN: Switched by BPK to allow filtering based on facility
		   if($row['priority'] == "debug") {
		   echo "<td class=\"sev0\">".$row['facility']."-".$row['priority']."</td>";
		   }
		   elseif($row['priority'] == "info") {
		   echo "<td class=\"sev1\">".$row['facility']."-".$row['priority']."</td>";
		   }
		   elseif ($row['priority'] == "notice") {
		   echo "<td class=\"sev2\">".$row['facility']."-".$row['priority']."</td>";
		   }
		   elseif ($row['priority'] == "warning") {
		   echo "<td class=\"sev3\">".$row['facility']."-".$row['priority']."</td>";
		   }
		   elseif ($row['priority'] == "err") {
		   echo "<td class=\"sev4\">".$row['facility']."-".$row['priority']."</td>";
		   }
		   elseif ($row['priority'] == "crit") {
		   echo "<td class=\"sev5\">".$row['facility']."-".$row['priority']."</td>";
		   }
		   elseif ($row['priority'] == "alert") {
		   echo "<td class=\"sev6\">".$row['facility']."-".$row['priority']."</td>";
		   }
		   elseif ($row['priority'] == "emerg") {
		   echo "<td class=\"sev7\">".$row['facility']."-".$row['priority']."</td>";
		   }
		 */

		echo "<td class=\"";
	   	switch ($row['priority']) {
		   	case 'debug':
			   	echo 'sev0';
			   	break;
		   	case 'info':
			   	echo 'sev1';
			   	break;
		   	case 'notice':
			   	echo 'sev2';
			   	break;
		   	case 'warning':
			   	echo 'sev3';
			   	break;
		   	case 'err':
			   	echo 'sev4';
			   	break;
		   	case 'crit':
			   	echo 'sev5';
			   	break;
		   	case 'alert':
			   	echo 'sev6';
			   	break;
		   	case 'emerg':
			   	echo 'sev7';
			   	break;
	   	}
	   	echo "\"><a href=\"".$_SERVER['PHP_SELF']."?excludeFacility=0&facility[]=".$row['facility']."&".$facilityParamsGET."\">";
	   	echo $row['facility']."</a></td>\n";

		// echo "<td>".$row['fo']."</td>";
	   	if(defined('SQZ_ENABLED') && SQZ_ENABLED == TRUE) {
		   	$pieces = explode(' ', $row['fo']);
		   	echo '<td>';
		   	if ($pieces[0]!=$today) {
			   	echo $pieces[0]."&nbsp;";
		   	}
		   	echo $pieces[1];
		   	echo "</td>\n";
		   	// echo "<td>".$row['fo']."</td>";
		   	$pieces = explode(' ', $row['lo']);
		   	echo '<td>';
		   	if ($pieces[0]!=$today) {
			   	echo $pieces[0]."&nbsp;";
		   	}
		   	echo $pieces[1];
		   	echo "</td>\n";
		   	// Counter row follows
		   	echo "<td>".$row['counter']."</td>";
	   	}
	   	/* END: Switched by BPK to allow filtering based on facility */


		/* BEGIN: Added by BPK to hide the date if it's the same as today
		   echo "<td>".$row['datetime']."</td>";
		 */
	   	$pieces = explode(' ', $row['datetime']);
	   	echo '<td>';
	   	if ($pieces[0]!=$today) {
		   	echo $pieces[0]."&nbsp;";
	   	}
	   	echo $pieces[1];
	   	echo "</td>\n";
	   	echo '<td>';
	   	$program = htmlspecialchars($row['program']);
	   	if (!empty($program)) {
		   	$pattern = '/^'.addcslashes($program, '.()[]/\\').'/';
		   	$replacement = '<a href="'.$_SERVER['PHP_SELF'].'?excludeProgram=0&program[]='.$program.'&'.$programParamsGET.'">'.$program.'</a>';
		   	$program = preg_replace($pattern, $replacement, $program);
	   	}
	   	if($row['program'] == $id) {
		   	$program = "Syslog";
	   	}
	   	echo $program;
	   	echo "</td>\n";
	   	/* END: Added by BPK to hide the date if it's the same as today */

		/* BEGIN: Switched by BPK to show count along with extras like
		   filtering by program, and disabled popups with no information
		   if($row['count'] > 1) {
		   echo "<td><b>".$row['count']." *</b> ".htmlentities($row['msg'])."</td>";
		   }
		   else {
		 */
	   	/* BEGIN: CEMDB Mod */
	   	/* CONTINUE: Switched by BPK ... (cdukes - note - added close comment to end of this line due to no match found) 
		   if(CEMDB == "ON") {

		// Added below to remove whitespace between delimiters (% and :)
	   	// example:
	   	// "%SYS-5-CONFIG :" is now
	   	// "%SYS-5-CONFIG:"
	   	$row['msg'] = preg_replace('/\s:/', ':', $row['msg']);

		// Grab Mnemonic name and Message and leave out the stuff at the front
	   	$row['msg'] = preg_replace('/.*(%.*?:.*)/', '$1', $row['msg']);
	   	// Original message:
	   	// 3852752: DRP/0/0/CPU0:Feb 4 20:12:36.098 EST5: SSHD_[65697]: %SECURITY-SSHD-3-ERR_GENERAL: Failed to get DSA public key
	   	// New message using regex above: .*(%.*?:.*):
	   	// SSHD_[65697]: %SECURITY-SSHD-3-ERR_GENERAL: Failed to get DSA public key
	   	$data = $cemdb->lookup($row['msg']);
	   	if($data !== false) {
	   	$info  =     "<b>Name:</b>"                    . $data[0];
	   	$info .= "<br><b>Message:</b> "                . $data[1];
	   	$info .= "<br><b>Explanation:</b> "            . $data[2];
	   	$info .= "<br><b>Action:</b> "                 . $data[3];
	   	$info .= "<br><b>Record last updated on:</b> " . $data[4];
	   	$info = str_replace("\n", "", $info);
	   	$info = htmlentities($info);
	   	if (!get_magic_quotes_gpc()) {
	   	$info = stripslashes($info);
	   	}
	   	}
	   	else {
	   	//if (preg_match("/%[:alpha:].*:/", $row['msg']) == 0) {
	   	if (preg_match("/%[[:alpha:]]+$/", $row['msg']) == 0) {

		$info = "";
	   	} else {
	   	$info = "No Data available for this message.";
	   	}
	   	}
	   	?>				<th  align="left"><A href="#" onmouseover="overlib('<TABLE border=1 cellspacing=0 cellpadding=0 width=100%><TR><TD class=tooltip><?php echo $info?></TD></TR></TABLE>');" onmouseout=nd(); name ="spacer" ><?php echo htmlspecialchars($row['msg']);?></A></th>
	   	<?			}
	   	else {
	   	echo "<td>".htmlspecialchars($row['msg'])."</td>";
	   	}
	   	/* END: CEMDB Mod */
	   	/* CONTINUE: Switched by BPK ...
		   }
		 */
	   	// Grab Mnemonic name and Message and leave out the stuff at the front
        if (CISCO_TAG_PARSE ) {
            $row['msg'] = preg_replace('/\s:/', ':', $row['msg']);
            $row['msg'] = preg_replace('/.*(%.*?:.*)/', '$1', $row['msg']);
        }
	   	// Original message:
	   	// 3852752: DRP/0/0/CPU0:Feb 4 20:12:36.098 EST5: SSHD_[65697]: %SECURITY-SSHD-3-ERR_GENERAL: Failed to get DSA public key
	   	// New message using regex above: .*(%.*?:.*):
	   	// SSHD_[65697]: %SECURITY-SSHD-3-ERR_GENERAL: Failed to get DSA public key
	   	if(CEMDB == "ON") {
	   	$data = $cemdb->lookup($row['msg']);
		}
	   	// BPK - this is where the revised version begins
	   	// CDUKES - BETA - FIX THIS
	   	$printed = false;
	   	if (CEMDB == "ON") {
		   	$data = $cemdb->lookup($row['msg']);
		   	if($data !== false) {
			   	$info  =     "<b>Name:</b>"                    . $data[0];
			   	$info .= "<br><b>Message:</b> "                . $data[1];
			   	$info .= "<br><b>Explanation:</b> "            . $data[2];
			   	$info .= "<br><b>Action:</b> "                 . $data[3];
			   	$info .= "<br><b>Record last updated on:</b> " . $data[4];
			   	$info = str_replace("\n", "", $info);
			   	$info = htmlentities($info);
			   	$msg = (($row['count'] > 1) ? '<b>'.$row['count'].' *</b> ' : '') . htmlspecialchars($row['msg']);
			   	?>				<th  align="left"><A href="#" onmouseover="overlib('<TABLE border=1 cellspacing=0 cellpadding=0 width=100%><TR><TD class=tooltip><?php echo $info?></TD></TR></TABLE>');" onmouseout=nd(); name ="spacer" ><?php echo $msg;?></A></th>
				   	<?php			$printed = true;
		   	}

		}
	   	// if CEMDB off or row wasn't found, print it
	   	// this will prevent unnecessary popups and allow filtering via a link
	   	if (!$printed) { 
			$msg = htmlspecialchars($row['msg']);
		   	echo "<td>";
		   	if ($row['count'] > 1) echo '<b>'.$row['count'].' *</b> ';
		   	echo "$msg</td>\n";
	   	}
	   	echo "</tr>\n";
   	}
   	echo "</table>\n";

	//------------------------------------------------------------------------
   	// Create the list with links to other results.
  	// The list will show a maximum of 11 pages + first and last
   	//------------------------------------------------------------------------
   	echo "Result Page: ";

	if($num_results) {

		// If you are not on the first page then show the FIRST link
	   	if($offset>0) {
		   	echo "<a href=\"".$_SERVER["PHP_SELF"]."?offset=0".$ParamsGET."\"><BIG>FIRST</BIG></a> ";
	   	}

		// If you are not on one of the first two pages then also show the PREV link
	   	if($offset>$limit+1) {
		   	$prevoffset=$offset-$limit ;
		   	echo "<a href=\"".$_SERVER["PHP_SELF"]."?offset=".$prevoffset.$ParamsGET."\"><BIG>PREV</BIG></a>\n";
	   	}

		// Calculate the total number of pages in the query
	   	$totalpages=intval($num_results/$limit);
	   	if($num_results%$limit) {
		   	$totalpages++;
	   	}

		// Calculate the current page
	   	$curpage=floor($offset/$limit);

		// Figure out what the first page on the list should be
	   	if($curpage<5) {
		   	$firstpage = 0;
	   	}
	   	else {
		   	$firstpage = $curpage - 5;
	   	}
	   	if($curpage>$totalpages - 6) {
		   	$firstpage = $totalpages - 11;
	   	}
	   	if($firstpage<5) {
		   	$firstpage = 0;
	   	}
	   	if($totalpages < 11) {
		   	$listpages = $totalpages;
	   	}
	   	else {
		   	$listpages = 11;
	   	}

		// Determine what the last page on the list should be
	   	$lastpage = $firstpage + $listpages;

		// Output the list of numbered links to the 11 closest pages.
	  	// The current page is high-lighted and the other are created as links
	   	for($i=$firstpage;$i<$lastpage;$i++) {
		   	$pageoffset=$i*$limit;
		   	$pagenum = $i + 1;
		   	if($curpage==$i) {
			   	echo "<font size=+1>[$pagenum]</font>\n";
		   	}
		   	else {
			   	echo "<a href=\"".$_SERVER["PHP_SELF"]."?offset=".$pageoffset.$ParamsGET."\">$pagenum</a>\n";
		   	}
	   	}

		// If there's a page with a higher offset and that page is not the last
	   	// on the list then create a NEXT link.
	  	if((intval($offset/$limit)+2)<$totalpages) {
		   	$nextoffset=$offset+$limit;
		   	echo "<a href=\"".$_SERVER["PHP_SELF"]."?offset=".$nextoffset.$ParamsGET."\"><BIG>NEXT</BIG></a> \n";
	   	}

		// If you are not currently on the last page then create a LAST link.
	  	if($totalpages>1 && (intval($offset/$limit)+1)!=$totalpages) {
		   	$lastoffset=($totalpages-1)*$limit;
		   	echo "<a href=\"".$_SERVER["PHP_SELF"]."?offset=".$lastoffset.$ParamsGET."\"><BIG>LAST</BIG></a>";
	   	}
   	}
   	// This for backwards when total row count is not calculated.
  	else {
	   	if($offset>0) {
		   	echo "<a href=\"".$_SERVER["PHP_SELF"]."?offset=0".$ParamsGET."\"><BIG>FIRST</BIG></a> ";
	   	}

		// If you are not on one of the first two pages then also show the PREV link
	   	if($offset>$limit+1) {
		   	$prevoffset=$offset-$limit ;
		   	echo "<a href=\"".$_SERVER["PHP_SELF"]."?offset=".$prevoffset.$ParamsGET."\"><BIG>PREV</BIG></a>\n";
	   	}
	   	$nextoffset=$offset+$limit;
	   	echo "<a href=\"".$_SERVER["PHP_SELF"]."?offset=".$nextoffset.$ParamsGET."\"><BIG>NEXT</BIG></a> \n";
   	}
   	}

	//------------------------------------------------------------------------
   	// Else just direct the user back to the form
   	//------------------------------------------------------------------------
	   	else {
		   	echo "No results found.<br><a href=\"index.php?pageId=searchform\">BACK TO SEARCH</a>";
	   	}

	//========================================================================
   	// END: BUILDING THE HTML PAGE
   	//========================================================================
?>

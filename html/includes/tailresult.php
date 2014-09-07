<?php
// Copyright (C) 2001-2004 by Michael Earls, michael@michaelearls.com
// Copyright (C) 2004-2005 Jason Taylor, j@jtaylor.ca
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com

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
$limit = get_input('limit', false);
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
$_SESSION['topx'] = (isset($topx)) ? $topx : '';
for ($i=1; $i<=3; $i++) {
   	$_SESSION['msg'.$i] = (isset(${'msg'.$i})?${'msg'.$i}:'');
   	$_SESSION['ExcludeMsg'.$i] = (isset(${'ExcludeMsg'.$i})?${'ExcludeMsg'.$i}:'');
   	$_SESSION['RegExpMsg'.$i] = (isset(${'RegExpMsg'.$i})?${'RegExpMsg'.$i}:'');
}
/* END: Added by BPK to save search form variables info the session. */

// Cdukes: 3/20/08 - Added below to make sure people are only tailing the default log table
// Ref: http://code.google.com/p/php-syslog-ng/issues/detail?id=57 
if (!$table) { $table = DEFAULTLOGTABLE; }
if ($table != DEFAULTLOGTABLE) {
   	echo "<br>
	   	Why would you tail $table? <br>
	   	The only table with \"live\" data in it is \"" .DEFAULTLOGTABLE ."\"<br>
	   	Using tail on any other table would not yeild anything useful (and would probably take a really long time). <br>
	   	<a href=\"index.php?pageId=searchform\">BACK TO SEARCH</a>";
   	die();
}

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
if($limit && !validate_input($limit, 'limit')) {
   	array_push($inputValError, "limit");
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
// AND BUILD PARAMETER LIST TO SAVE RESULT
//========================================================================
//------------------------------------------------------------------------
// Create WHERE statement and GET parameter list
//------------------------------------------------------------------------
$where = "";
$ParamsGET="&";

if($limit) {
   	$ParamsGET=$ParamsGET."limit=".$limit."&";
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

$msgvarnum=1;
$msgvarname="msg".$msgvarnum;
$excmsgvarname="ExcludeMsg".$msgvarnum;
$regexpmsgvarname="RegExpMsg".$msgvarnum;

/** BEGIN: Switched by BPK to allow from regexp
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
   	if(!strstr($value, "host[]=") && !strstr($value, 'excludeHost=') && !strstr($value, 'regexpHost=') && !strstr($value, 'offset=') && $value) {
	   	$hostParamsGET = $hostParamsGET.$value."&";
   	}
}

/* BEGIN: added by BPK to create GET string without facility, program, variables */
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
/* END: added by BPK to create GET string without facility, program, variables variables */

//------------------------------------------------------------------------
// Create the complete SQL statement
//------------------------------------------------------------------------
if($table) {
   	$srcTable = $table;
}
else {
   	$srcTable = DEFAULTLOGTABLE;
}

$query = "SELECT * FROM ".$srcTable." JOIN (select id from ".$srcTable." FORCE INDEX(PRIMARY) ";

if($where !="") {
   	$query = $query."WHERE ".$where." ORDER BY id DESC LIMIT ".$limit;
}
else {
   	$query = $query."ORDER BY id DESC LIMIT ".$limit;
}

$query = $query.") as sub USING(id)";


//------------------------------------------------------------------------
// Execute the query
//------------------------------------------------------------------------
$results = perform_query($query, $dbLink);

//========================================================================
// END: BUILD AND EXECUTE SQL STATEMENT
// AND BUILD PARAMETER LIST TO SAVE RESULT
//========================================================================


//========================================================================
// BEGIN: BUILDING THE HTML PAGE
//========================================================================
// Print result sub-header
require_once 'includes/html_result_subheader.php';

//------------------------------------------------------------------------
// Refresh time = 3 times exe time to this point + 15 seconds
//------------------------------------------------------------------------
$curExeTime = get_microtime() - $time_start;
$refreshTime = round(3*$curExeTime) + TAIL_REFRESH_SECONDS;
echo "<META HTTP-EQUIV=REFRESH CONTENT=".$refreshTime.">";

?>
<table>
<tr>
<td class="resultsheader">HOST</td>
<td class="resultsheader">FACILITY</td>
<td class="resultsheader">TIME</td>
<td class="resultsheader">PROGRAM</td>
<td class="resultsheader">MESSAGE</td>
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
while($row = fetch_array($results)) {
   	if($color == "darker") {
	   	$color = "lighter";
   	}
   	else {
	   	$color = "darker";
   	}
   	echo "<tr class=\"$color\">";
   	echo "<td><a href=\"".$_SERVER["PHP_SELF"]."?excludeHost=0&host[]=".$row['host']."&".$hostParamsGET."\">";
   	echo $row['host']."</a></td>";

	/* BEGIN: Switched by BPK to allow filtering based on facility
	   if($row['priority'] == "debug") {
	   echo "<td class=\"sev0\">".$row['facility']."</td>";
	   }
	   elseif($row['priority'] == "info") {
	   echo "<td class=\"sev1\">".$row['facility']."</td>";
	   }
	   elseif($row['priority'] == "notice") {
	   echo "<td class=\"sev2\">".$row['facility']."</td>";
	   }
	   elseif($row['priority'] == "warning") {
	   echo "<td class=\"sev3\">".$row['facility']."</td>";
	   }
	   elseif ($row['priority'] == "err") {
	   echo "<td class=\"sev4\">".$row['facility']."</td>";
	   }
	   elseif ($row['priority'] == "crit") {
	   echo "<td class=\"sev5\">".$row['facility']."</td>";
	   }
	   elseif ($row['priority'] == "alert") {
	   echo "<td class=\"sev6\">".$row['facility']."</td>";
	   }
	   elseif ($row['priority'] == "emerg") {
	   echo "<td class=\"sev7\">".$row['facility']."</td>";
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
   	/* END: Switched by BPK to allow filtering based on facility */

	// Extract just the time from the timestamp
   	$pieces = explode(" ", $row['datetime']);
   	/* BEGIN: BPK - Some queries can extend multiple days, display the one's not equal to today
	   echo "<td>$pieces[1]</td>";
	 */
   	echo '<td>';
   	if ($pieces[0]!=$today) {
	   	echo $pieces[0]."&nbsp;";
	   	// die("Piece 0: " .$pieces[0] ."Today: $today");
   	}
   	echo $pieces[1];
   	echo "</td>\n";
   	/* END: BPK - Some queries can extend multiple days, display the one's not equal to today */
   	echo '<td>';
   	$program = htmlspecialchars($row['program']);
   	if (!empty($program)) {
	   	$pattern = '/^'.addcslashes($program, '.()[]/\\').'/';
	   	$replacement = '<a href="'.$_SERVER['PHP_SELF'].'?excludeProgram=0&program[]='.$program.'&'.$programParamsGET.'">'.$program.'</a>';
	   	$program = preg_replace($pattern, $replacement, $program);
   	}
   	echo $program;
   	echo "</td>\n";

	/* BEGIN: CEMDB Mod */
   	// Added below to remove whitespace between delimiters (% and :)
   	// example:
   	// "%SYS-5-CONFIG :" is now
   	// "%SYS-5-CONFIG:"
   	// Grab Mnemonic name and Message and leave out the stuff at the front
    
    if (CISCO_TAG_PARSE ) {
        $row['msg'] = preg_replace('/\s:/', ':', $row['msg']);
        $row['msg'] = preg_replace('/.*(%.*?:.*)/', '$1', $row['msg']);
    }

   	// Original message:
   	// 3852752: DRP/0/0/CPU0:Feb 4 20:12:36.098 EST5: SSHD_[65697]: %SECURITY-SSHD-3-ERR_GENERAL: Failed to get DSA public key
   	// New message using regex above: .*(%.*?:.*):
   	// SSHD_[65697]: %SECURITY-SSHD-3-ERR_GENERAL: Failed to get DSA public key
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
	// Moved this to else outside the CEMDB loop so it would print when CEMDB was disabled
   	// REF: http://code.google.com/p/php-syslog-ng/issues/detail?id=68
   	if (!$printed) { 
		// if CEMDB off or row wasn't found, print it
	   	// this will prevent unnecessary popups and allow filtering via a link
	   	$msg = htmlspecialchars($row['msg']);
	   	echo "<td>";
	   	if ($row['count'] > 1) echo '<b>'.$row['count'].' *</b> ';
	   	echo "$msg</td>\n";
   	}
   	/* END: CEMDB Mod */

	echo    "</tr>\n";
}
echo "</table>\n";

//========================================================================
// END: BUILDING THE HTML PAGE
//========================================================================
?>

<?php
// Copyright (C) 2001-2004 by Michael Earls, michael@michaelearls.com
// Copyright (C) 2004-2005 Jason Taylor, j@jtaylor.ca
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com
// Copyright (C) 2006 Clayton Dukes, cdukes@cdukes.com

$basePath = dirname( __FILE__ );
require_once ($basePath . "/html_header.php");

//------------------------------------------------------------------------
// CDUKES - BEGIN Calendar Addon
//------------------------------------------------------------------------

$todayYear = date("Y");
$todayMonth = date("m");
$todayMMonth = date("M");
$todayDay = date("d");
$today = "$todayMMonth $todayDay";
$caltoday = "$todayYear-$todayMonth-$todayDay";
$thismonth = "$todayYear-$todayMonth-01";
$twomonths = date("m") + 2;
$endDay = "$todayYear-$twomonths-$todayDay";
$weekArr = get_weekdates($todayYear,$todayMonth,$todayDay);
// die (print_r($weekArr));

//------------------------------------------------------------------------
// END - BEGIN Calendar Addon
//------------------------------------------------------------------------


//------------------------------------------------------------------------
// See if the MERGELOGTABLE is configured and available
//------------------------------------------------------------------------
$mergelogtable = FALSE;
if(defined('MERGELOGTABLE') && MERGELOGTABLE) {
   	if(table_exists(MERGELOGTABLE, $dbLink)) {
	   	$mergelogtable = TRUE;
   	}
}

// Get list of log tables
$logTableArray = get_logtables($dbLink);

//------------------------------------------------------------------------
// Print the top of the form and the table SELECTion if there are multiple
// log tables.
//------------------------------------------------------------------------
$table = get_input('table');
if($table && !validate_input($table, 'table')) {
   	require_once 'includes/html_header.php';
   	echo "The table has the wrong format.<p>";
   	require_once 'includes/html_footer.php';
   	exit;
}

?>
<table class="pagecontent">
<tr><td>
<form action="<?php echo $_SERVER["PHP_SELF"]; ?>" method="GET" name="results">
<table><tr><td>
<?php
if(count($logTableArray) > 1) { // && !$table) { 
	// BPK this doesn't play nice with SESSION being grabed by get_input
   	// instead, we always default to DEFAULTLOGTABLE, and let the js at the bottom 
	// set it the the last selection
   	$selected = '';
   	if (!$table) {
	   	$table = DEFAULTLOGTABLE;
	   	$selected = 'selected';
   	}
   	?>
	   	<table class="searchform">
	   	<tr class="lighter"><td>
	   	<b>SELECT TABLE:</b>
	   	<SELECT name="table" id="table">
	   	<?php
	   	echo "<OPTION $selected>".DEFAULTLOGTABLE."</OPTION>";
   	if($mergelogtable) {
	   	echo "<OPTION>".MERGELOGTABLE."</OPTION>";
   	}
   	rsort($logTableArray);
   	foreach($logTableArray as $value) {
	   	if($value != DEFAULTLOGTABLE && $value != MERGELOGTABLE) {
		   	echo "<OPTION>".htmlentities($value)."</OPTION>";
	   	}
   	}
   	?>
	   	</SELECT>
	   	</td></tr></table>
	   	<?php
}
else {
   	if(!$table) {
	   	$table = DEFAULTLOGTABLE;
   	}
   	echo "<table class=\"searchform\">";
   	echo "<tr class=\"lighter\"><td>";
   	echo "<b>USING TABLE: ".$table."</b>";
   	echo "</td></tr></table>";
   	echo "<input type=\"hidden\" name=\"table\" value=\"".$table."\">";
}


//------------------------------------------------------------------------
// If MERGELOGTABLE is not used and there are multiple log tables
// then make the user pick a table to search before showing the rest of
// the search form.
//------------------------------------------------------------------------
if(!$mergelogtable && count($logTableArray) > 0 && !$table) {
   	?>
	   	</td></tr>
	   	<table class="searchform">
	   	<tr><td class="darker">
	   	<input type="hidden" name="pageId" value="searchform">
	   	<input type="submit" value="Select database">
	   	</td></tr></table>
	   	</td></tr></table>
	   	</form>
	   	</td></tr></table>
	   	<?php

}
//------------------------------------------------------------------------
// Else show the rest of the form.
//------------------------------------------------------------------------
else {
   	$hostarray = array();
   	$facilityarray = array();
   	$programarray = array();

	// What table to use to fill in the HOST and FACILITY fields?
  	if($mergelogtable) {
	   	// $useTable = MERGELOGTABLE;
	   	$useTable = USETABLE;
   	}
   	elseif($table) {
	   	$useTable = $table;
   	}
   	else {
	   	$useTable = DEFAULTLOGTABLE;
   	}

	//------------------------------------------------------------------------
   	// Use cache?
  	//------------------------------------------------------------------------
   	if(USE_CACHE && table_exists(CACHETABLENAME, $dbLink)) {
	   	// If the cache table is empty then reload it.
	  	$sql = "SELECT * FROM ".CACHETABLENAME." WHERE tablename='".$useTable."'";
	   	$queryresult = perform_query($sql, $dbLink);
	   	if(num_rows($queryresult) == 0) {
		   	reload_cache($useTable, $dbLink);
	   	}

		// Get the timestamp for the last update.
	  	$sql = "SELECT updatetime FROM ".CACHETABLENAME." WHERE tablename='"
		   	.$useTable."'";
	   	$queryresult = perform_query($sql, $dbLink);
	   	$row = fetch_array($queryresult);
	   	$cacheUpdate = $row['updatetime'];

		// Print info about the cache.
	  	echo "<table class=\"searchform\">";
	   	echo "<tr class=\"lighter\"><td>";
	   	echo "<b>USING CACHE TO POPULATE HOST, FACILITY, AND PROGRAM FIELDS.</b>";
	   	echo "<br />Cache last updated on ".$cacheUpdate.".";
	   	echo "</td></tr></table>";

		// Get the HOST list
	   	$sql = "SELECT DISTINCT value FROM ".CACHETABLENAME." WHERE type='HOST'
		   	AND tablename='".$useTable."'";
	   	$queryresult = perform_query($sql, $dbLink);
	   	while ($line = fetch_array($queryresult)) {
		   	array_push($hostarray, $line['value']);
	   	}
	   	sort($hostarray);
	   	/* START - Added by CDUKES for host count */
	   	$hostcount = count($hostarray);
	   	/* END - Added by CDUKES for host count */

		// Get the FACILITY list
	   	$sql = "SELECT DISTINCT value FROM ".CACHETABLENAME." WHERE type='FACILITY'
		   	AND tablename='".$useTable."'";
	   	$queryresult = perform_query($sql, $dbLink);
	   	while ($line = fetch_array($queryresult)) {
		   	array_push($facilityarray, $line['value']);
	   	}
	   	sort($facilityarray);

		/* BEGIN: Added by BPK for program list */
	   	// Get the PROGRAM list
	   	$sql = "SELECT DISTINCT value FROM ".CACHETABLENAME." WHERE type='PROGRAM'
		   	AND tablename='".$useTable."'";
		// die($sql);
	   	$queryresult = perform_query($sql, $dbLink);
	   	while ($line = fetch_array($queryresult)) {
		   	array_push($programarray, $line['value']);
	   	}
	   	sort($programarray);
	   	$programcount = count($programarray);
	   	/* END: Added by BPK for program list */
   	}
   	else {
	   	//------------------------------------------------------------------------
	   	// If no cache then get possible values for facility and host from table.
	  	//------------------------------------------------------------------------
	   	$sql = "SELECT DISTINCT host FROM ".$useTable;
	   	$queryresult = perform_query($sql, $dbLink);
	   	while ($line = fetch_array($queryresult)) {
		   	array_push($hostarray, $line['host']);
	   	}
	   	sort($hostarray);
	   	/* START - Added by CDUKES for host count */
	   	$hostcount = count($hostarray);
	   	/* END - Added by CDUKES for host count */

		$sql = "SELECT DISTINCT facility FROM ".$useTable;
	   	$queryresult = perform_query($sql, $dbLink);
	   	while ($line = fetch_array($queryresult)) {
		   	array_push($facilityarray, $line['facility']);
	   	}
	   	sort($facilityarray);
   	}


	if(defined('SQZ_ENABLED') && SQZ_ENABLED == TRUE) {
	   	// Get the SQZ Savings by finding the hosts with more than 1 counter
	   	// and converting the ratio of hosts to hosts with > 1 to a percentage
	   	$sql = "SELECT SUM(counter), count(host) from ".DEFAULTLOGTABLE." WHERE counter>1";
	   	$query = perform_query($sql, $dbLink);
	   	$array = fetch_array($query);
		$sumcnt = $array[0];
	   	$hosts = $array[1];
		// below is a simple test for new (or empty) databases
		if (empty($sumcnt)) {
			$sumcnt = 1;
		}
		if (empty($hosts)) {
			$hosts = 1;
		}
	   	$mph = ($sumcnt/$hosts);
	   	// subtract 100 from the total below to get the opposite effect (savings = 90% rather than 10%)
	   	// Calculation is to get the percentage of hosts to messages_per_host (convert a ratio to percentage)
	   	$tot = (100 - (round(100/($mph * 100),4)) * 100);
	   	$SQZ_SAVINGS = $tot;
   	}

//------------------------------------------------------------------------
   	// Print the rest of the form.
  	//------------------------------------------------------------------------
   	?>
	   	<table class="searchform">
	   	<tr class="lighter"><td>
	   	<!--  START - Added by CDUKES for host count -->
	   	<b>HOSTS: <?php echo $hostcount?></b>
	   	<!--  END - Added by CDUKES for host count -->
	   	<table align="center" class="formentry"><tr><td>
	   	Include
	   	</td><td>
	   	<input name="excludeHost" id="excludeHost_0" value="0" type="radio">
	   	</td></tr>
	   	<tr><td>
	   	Exclude
	   	</td><td>
	   	<input name="excludeHost" id="excludeHost_1" value="1" type="radio" checked>
	   	</td></tr>
	   	<!-- START: Added by BPK for RegExp option -->
	   	<tr><td>
	   	RegExp Matching?
	  	</td><td>
	   	<input name="regexpHost" id="regexpHost" value="1" type="checkbox">
	   	</td></tr>
	   	<!-- END: Added by BPK for RegExp option -->
	   	<tr><td>
	   	Hostname match
	   	</td><td>
	   	<input type=text name=host2 id="host2" size=20>
	   	</td></tr>
	   	<tr><td valign="top">
	   	=====AND=====
	   	</td><td>
	   	<SELECT name="host[]" id="host" multiple size=6>
	   	<?php
	   	foreach($hostarray as $value) {
		   	echo "<OPTION>".htmlentities($value)."</OPTION>";
	   	}
   	?>
	   	</SELECT>
	   	</td></tr></table>
	   	</td><td>
	   	<!--  START: Added by BPK for program list -->
	   	<b>PROGRAMS: <?php echo $programcount?></b>
	   	<table align="center" class="formentry"><tr><td>
	   	Include
	   	</td><td>
	   	<input name="excludeProgram" id="excludeProgram_0" value="0" type="radio">
	   	</td></tr>
	   	<tr><td>
	   	Exclude
	   	</td><td>
	   	<input name="excludeProgram" id="excludeProgram_1" value="1" type="radio" checked>
	   	</td></tr>
	   	<!-- START: Added by BPK for RegExp option -->
	   	<tr><td>
	   	RegExp Matching?
	  	</td><td>
	   	<input name="regexpProgram" id="regexpProgram" value="1" type="checkbox">
	   	</td></tr>
	   	<!-- END: Added by BPK for RegExp option -->
	   	<tr><td>
	   	Program match
	   	</td><td>
	   	<input type=text name=program2 id="program2" size=20>
	   	</td></tr>
	   	<tr><td valign="top">
	   	=====AND=====
	   	</td><td>
	   	<SELECT name="program[]" id="program" multiple size=6>
	   	<?php
	   	foreach($programarray as $value) {
		   	echo "<OPTION>".htmlentities($value)."</OPTION>";
	   	}
   	?>
	   	</SELECT>
	   	</td></tr></table>
	   	</td><td>
	   	<b> SYSLOG FACILITY:</b>
	   	<table align="center" class="formentry"><tr><td>
	   	Include
	   	</td><td>
	   	<input name="excludeFacility" id="excludeFacility_0" value="0" type="radio">
	   	</td></tr>
	   	<tr><td>
	   	Exclude
	   	</td><td>
	   	<input name="excludeFacility" id="excludeFacility_1" value="1" type="radio" checked>
	   	</td></tr>
	   	<tr><td colspan=2>
	   	<SELECT name="facility[]" id="facility" multiple size=8>
	   	<?php
	   	foreach($facilityarray as $value) {
		   	echo "<OPTION>".htmlentities($value)."</OPTION>";
	   	}
   	?>
	   	</SELECT>
	   	</td></tr></table>
	   	</td><td>
	   	<!--  END: Added by BPK for program list -->
	   	<b> SYSLOG PRIORITY:</b>
	   	<table align="center" class="formentry"><tr><td>
	   	Include
	   	</td><td>
	   	<input name="excludePriority" id="excludePriority_0" value="0" type="radio">
	   	</td></tr>
	   	<tr><td>
	   	Exclude
	   	</td><td>
	   	<input name="excludePriority" id="excludePriority_1" value="1" type="radio" checked>
	   	</td></tr>
	   	<tr><td colspan=2>
	   	<SELECT name="priority[]" id="priority" multiple size=8>
	   	<OPTION>debug</OPTION>
	   	<OPTION>info</OPTION>
	   	<OPTION>notice</OPTION>
	   	<OPTION>warning</OPTION>
	   	<OPTION>err</OPTION>
	   	<OPTION>crit</OPTION>
	   	<OPTION>alert</OPTION>
	   	<OPTION>emerg</OPTION>
	   	</SELECT>
	   	</td></tr></table>
	   	</td></tr></table>
	   	<table class="searchform">
	   	<tr class="lighter"><td>
	   	<table align="center" class="formentry">
	   	<tr><td></td><td>
	   	DATE
	   	</td><td>
	   	TIME
	   	</td></tr>
	   	<tr><td>
	   	<b>From:</b>
	   	</td><td>
	   	<script type="text/javascript" src="includes/js/datetimepicker.js?v2.0.502"></script>
	   	<input type="text" class="text" size="10" name="date" id="date"/>
	   	<a href="javascript:NewCal('date','yyyymmdd',false,12)">
	   	<img src="images/cal.gif"border="0" alt="Pick a date" /></a>

		<!--
	   	<div id="myDatePickerDiv" width="100">
	   	<input type="text" id="date" name="date" onMouseover="return overlib('<TABLE border=1 cellspacing=0 cellpadding=0 width=100%><TR><TD class=tooltip>Click on the Calendar to SELECT the date.<br>The date format is YYYY-MM-DD and the time format is HH:MM:SS.<br>Yesterday, today and now are also valid dates and now is also valid as a time.<br>If you do not get a calendar popup, then manually enter the date as<br>YYYY-MM-DD<br>eg: <?php echo $caltoday?></TD></TR></TABLE>');" onMouseout="nd();" size="12">
	   	</td>
		-->
		<td>
	   	<input type="text" size=8 maxlength=8 name="time" id="time">
	   	</td></tr><tr><td>
	   	<b>To:</b>
	   	</td>
		<td>
	   	<script type="text/javascript" src="includes/js/datetimepicker.js?v2.0.502"></script>
	   	<input type="text" class="text" size="10" name="date2" id="date2"/>
	   	<a href="javascript:NewCal('date2','yyyymmdd',false,12)">
	   	<img src="images/cal.gif"border="0" alt="Pick a date" /></a>
		<!--
	   	<div id="myDatePickerDivR">
	   	<input type="text" id="date2" name="date2" onMouseover="return overlib('<TABLE border=1 cellspacing=0 cellpadding=0 width=100%><TR><TD class=tooltip>Click on the Calendar to SELECT the date.<br>The date format is YYYY-MM-DD and the time format is HH:MM:SS.<br>Yesterday, today and now are also valid dates and now is also valid as a time.<br>If you do not get a calendar popup, then manually enter the date as<br>YYYY-MM-DD<br>eg: <?php echo $caltoday?></TD></TR></TABLE>');" onMouseout="nd();" size="12">
	   	<img src="images/buttons/bs_calendar.gif" onClick="todate();"></div>
	   	</td>
		-->
		<td>
	   	<input type="text" size=8 maxlength=8 name="time2" id="time2">
	   	</td></tr></table>
	   	<!-- Middle -->
	   	<?php
		// Included below as a block instead of coding directly in here.
		// This is more in preparation for the upcoming v3.0 model
	   	if(defined('GRAPH_LPD') && GRAPH_LPD == TRUE) {
	   	echo "</td><td>\n";

		include("blocks/graph-logs_per_day.php");

	   	echo "</td>\n";
	   	echo "<!-- End Middle -->\n";
		}
	   	?>
	   	<td>
	   	<table align="center" class="formentry">
	   	<tr>
	   	<td><b>RECORDS PER PAGE</b></td>
	   	<td>
	   	<SELECT name="limit" id="limit">
	   	<OPTION>10</OPTION>
	   	<OPTION>25</OPTION>
	   	<OPTION selected>50</OPTION>
	   	<OPTION>100</OPTION>
	   	<OPTION>200</OPTION>
	   	<OPTION>500</OPTION>
	   	<OPTION>1000</OPTION>
	   	</SELECT>
	   	<?php  if ( JPG_GRAPHS == "ON" ) { ?>
		   	<tr>
			   	<td><b>TopX</b></td>
			   	<td>
			   	<SELECT name="topx" id="topx">
			   	<OPTION selected>10</OPTION>
			   	<OPTION>20</OPTION>
			   	<OPTION>25</OPTION>
			   	<OPTION>30</OPTION>
			   	<OPTION>35</OPTION>
			   	<OPTION>40</OPTION>
			   	<OPTION>50</OPTION>
			   	<OPTION>100</OPTION>
			   	</SELECT>
			   	</td></tr>
			   	<?php  } ?>
	   	<?php  if ( SQZ_ENABLED == TRUE ) { ?>
		   	<tr>
			   	<td><b>Duplicates (<?php echo $SQZ_SAVINGS?> %)</b></td>
			   	<td>
			   	<SELECT name="dupop" id="dupop">
			   	<OPTION selected value=""></OPTION>
			   	<OPTION value="gt">></OPTION>
			   	<OPTION value="lt"><</OPTION>
			   	<OPTION value="eq">=</OPTION>
			   	<OPTION value="gte">>=</OPTION>
			   	<OPTION value="lte"><=</OPTION>
			   	</SELECT>
			   	<input type=text name="dupcount" id="dupcount" value="0" size="3" />
			   	</td></tr>
			   	<?php  } ?>
			   	<tr>
			   	<td><b>ORDER BY</b></td>
			   	<td>
			   	<SELECT name="orderby" id="orderby">
			   	<OPTION>id</OPTION>
			   	<OPTION>seq</OPTION>
			   	<OPTION>host</OPTION>
			   	<OPTION>program</OPTION>
			   	<OPTION>facility</OPTION>
			   	<OPTION>priority</OPTION>
			   	<OPTION selected>datetime</OPTION>
			   	</SELECT>
			   	</td></tr>
			   	<tr>
			   	<td><b>SEARCH ORDER</b></td>
			   	<td>
			   	<SELECT name="order" id="order">
			   	<OPTION>ASC</OPTION>
			   	<OPTION selected>DESC</OPTION>
			   	</SELECT>
			   	</td></tr></table>
			   	</td></tr></table>
			   	<table class="searchform">
			   	<tr class="lighter"><td>
			   	<b>SEARCH MESSAGE:</b><br>
			   	<!-- START: Switched by BPK
			   	Exclude <input type=checkbox name="ExcludeMsg1"> <input type=text name="msg1" size=75%> <b>AND</b><br>
			   	Exclude <input type=checkbox name="ExcludeMsg2"> <input type=text name="msg2" size=75%> <b>AND</b><br>
			   	Exclude <input type=checkbox name="ExcludeMsg3"> <input type=text name="msg3" size=75%>
			   	-->
			   	<table class="msgentry">
			   	<tr><td>
			   	Exclude <input type="checkbox" name="ExcludeMsg1" id="ExcludeMsg1" />
			   	</td><td>
			   	RegExp <input type="checkbox" name="RegExpMsg1" id="RegExpMsg1" />
			   	</td><td>
			   	<input type=text name="msg1" id="msg1" size="75%" />&nbsp;&nbsp;<b>AND</b>
			   	</td></tr>
			   	<tr><td>
			   	Exclude <input type="checkbox" name="ExcludeMsg2" id="ExcludeMsg2" />
			   	</td><td>
			   	RegExp <input type="checkbox" name="RegExpMsg2" id="RegExpMsg2" />
			   	</td><td>
			   	<input type=text name="msg2" id="msg2" size="75%" />&nbsp;&nbsp;<b>AND</b>
			   	</td></tr>
			   	<tr><td>
			   	Exclude <input type="checkbox" name="ExcludeMsg3" id="ExcludeMsg3" />
			   	</td><td>
			   	RegExp <input type="checkbox" name="RegExpMsg3" id="RegExpMsg3" />
			   	</td><td>
			   	<input type=text name="msg3" id="msg3" size="75%" />
			   	</td></tr>
			   	</table>
			   	<!-- END: Switch by BPK -->
			   	</td></tr>
			   	<table class="searchform">
			   	<tr><td class="darker">
			   	<input type="submit" name="pageId" value="Search">
			   	<input type="submit" name="pageId" value="Tail">
			   	<?php  if ( JPG_GRAPHS == "ON" ) { ?>
				   	<input type="submit" name="pageId" value="Graph" onMouseover="return overlib('<TABLE border=1 cellspacing=0 cellpadding=0 width=100%><TR><TD class=tooltip>Please be aware that some graph building searches can take a very long time.<br>Try to make searches as finite as possible (ie, do not try to build a graph for everything, if you do, it will probably just timeout on large databases...)</TD></TR></TABLE>');" onMouseout="nd();" >
					   	<?php } ?>
					   	<input type="reset" value="Reset">
					   	</td></tr></table>
					   	</td></tr></table>
					   	</form>
					   	</td></tr></table>
					   	<FORM name="dd"> 
						<?php  if ( JPG_GRAPHS == "ON" ) { ?>
						   	<b>Common Graphs:</b><br>
							   	<SELECT name="graph" onChange="window.location=document.dd.graph.options[document.dd.graph.selectedIndex].value"> 
								<OPTION selected value="#">Select a Graph Type<?php  if ( JPG_DAILY == "ON" ) { ?>
								   	<OPTION value="<?php echo $_SERVER["PHP_SELF"]; ?>?table=<?php echo DEFAULTLOGTABLE?>&excludeHost=1&host2=&date=yesterday&time=&date2=today&topx=10&pageId=Graph">Today</OPTION>
									   	<?php  } if ( JPG_WEEKLY == "ON" ) { ?>
										   	<OPTION value="<?php echo $_SERVER["PHP_SELF"]; ?>?table=<?php echo MERGELOGTABLE?>&excludeHost=1&host2=&date=<?php echo $weekArr[1]['sqldate'];?>&time=&date2=today&topx=10&pageId=Graph">This Week</OPTION>
											   	<?php  } if ( JPG_MONTHLY == "ON" ) { ?>
												   	<OPTION value="<?php echo $_SERVER["PHP_SELF"]; ?>?table=<?php echo MERGELOGTABLE?>&excludeHost=1&host2=&date=<?php echo $thismonth?>&time=&date2=today&topx=10&pageId=Graph">This Month</OPTION>
													   	<?php  } ?>
													   	</SELECT> 
														</FORM> 
														<?php
													  	if ( JPG_MAIN == "NOTWORKING YET" ) {
														   	// Set up Graph variables
														   	$slice = 0;
														   	$host = "";
														   	$date = "yesterday";
														   	$date2 = "today";
														   	$str = $str;
														   	$limit = 10; // Equals top 10 percent of hosts
														   	$type = "pie3d";
														   	$mapName = 'Top10'; 
															$fileName = IMG_CACHE_DIR . "Top10.png";

															$imgMap = graph($slice, $host, $date, $date2, $str, $limit, $type, $mapName, $fileName); 
															echo "$imgMap <img src=\"$fileName\" alt=\"graph\" ismap usemap=\"#$mapName\" border=\"0\">";
													   	}

						}
}
?>
<?php
/* BEGIN: Added by BPK to automatically repopulate the search form selections from $_SESSION using JavaScript */
// Note: we use javascript so the Reset button will still work.
echo "<script type=\"text/javascript\" language=\"javascript\">\n";
// begin table restore
if(isset($_SESSION['table'])) $table = $_SESSION['table'];
if (!empty($table)) {
?> 	
	select = document.getElementById('table');
	for (i=0; i<select.length; i++) {
		if (select.options[i].text == '<?php echo $table; ?>') {
			select.selectedIndex = i;
			break;
		}
	}
<?php
} // end table restore

// begin restoring host selections
if (isset($_SESSION['excludeHost']) && is_numeric($_SESSION['excludeHost']) && $_SESSION['excludeHost'] == 0) {
   	echo "document.getElementById('excludeHost_0').checked = true;\n";
}
if (isset($_SESSION['regexpHost']) && $_SESSION['regexpHost'] == 1) {
   	echo "document.getElementById('regexpHost').checked = true;\n";
}
if(isset($_SESSION['host2'])) $host2 = $_SESSION['host2'];
if (!empty($host2)) {
   	echo "document.getElementById('host2').value = '$host2';\n";
}
if(isset($_SESSION['host'])) $host = $_SESSION['host'];
if (!empty($host)) {
   	$regexp = '/^('.
		  	addcslashes(join($host, '|'), '.()[]/\\')
		   	.')$/';
   	?>
	   	select = document.getElementById('host');
   	for (i=0; i<select.length; i++) {
	   	if (select.options[i].text.match(<?php echo $regexp; ?>)) {
		   	select.options[i].selected = true;
	   	}
   	}
   	<?php
} // end host restore

// begin restoring program selections
if (isset($_SESSION['excludeProgram']) && is_numeric($_SESSION['excludeProgram']) && $_SESSION['excludeProgram'] == 0) {
   	echo "document.getElementById('excludeProgram_0').checked = true;\n";
}
if (isset($_SESSION['regexpProgram']) && $_SESSION['regexpProgram'] == 1) {
   	echo "document.getElementById('regexpProgram').checked = true;\n";
}
if(isset($_SESSION['program2'])) $program2 = $_SESSION['program2'];
if (!empty($program2)) {
   	echo "document.getElementById('program2').value = '$program2';\n";
}
if(isset($_SESSION['program'])) $program = $_SESSION['program'];
if (!empty($program)) {
   	$regexp = '/^('.
		  	addcslashes(join($program, '|'), '.()[]/\\')
		   	.')$/';
   	?>
	   	select = document.getElementById('program');
   	for (i=0; i<select.length; i++) {
	   	if (select.options[i].text.match(<?php echo $regexp; ?>)) {
		   	select.options[i].selected = true;
	   	}
   	}
   	<?php
} // end program restore

// begin facility restore
if (isset($_SESSION['excludeFacility']) && is_numeric($_SESSION['excludeFacility']) && $_SESSION['excludeFacility'] == 0) {
   	echo "document.getElementById('excludeFacility_0').checked = true;\n";
}
if(isset($_SESSION['facility'])) $facility = $_SESSION['facility'];
if (!empty($facility)) {
   	$regexp = '/^('.
		  	addcslashes(join($facility, '|'), '.()[]/\\')
		   	.')$/';
   	?>
	   	select = document.getElementById('facility');
   	for (i=0; i<select.length; i++) {
	   	if (select.options[i].text.match(<?php echo $regexp; ?>)) {
		   	select.options[i].selected = true;
	   	}
   	}
   	<?php
} // end facility restore

// begin priority restore
if (isset($_SESSION['excludePriority']) && is_numeric($_SESSION['excludePriority']) && $_SESSION['excludePriority'] == 0) {
   	echo "document.getElementById('excludePriority_0').checked = true;\n";
}
if(isset($_SESSION['priority'])) $priority = $_SESSION['priority'];
if (!empty($priority)) {
   	$regexp = '/^('.
		  	addcslashes(join($priority, '|'), '.()[]/\\')
		   	.')$/';
   	?>
	   	select = document.getElementById('priority');
   	for (i=0; i<select.length; i++) {
	   	if (select.options[i].text.match(<?php echo $regexp; ?>)) {
		   	select.options[i].selected = true;
	   	}
   	}
   	<?php
} // end priority restore

// begin date/time restore
echo "document.getElementById('date').value = '".$_SESSION['date']."';\n";
echo "document.getElementById('time').value = '".$_SESSION['time']."';\n";
echo "document.getElementById('date2').value = '".$_SESSION['date2']."';\n";
echo "document.getElementById('time2').value = '".$_SESSION['time2']."';\n";
// end date/time restore

// begin limit,topx,orderby,order restore
foreach (array('limit', 'topx', 'orderby', 'order','dupop') as $var) {
	// We have to convert the comparison symbols back to their original state for the javascipt to restore
   	switch ($_SESSION[$var]) {
	   	case gt:
		   	$_SESSION[$var] = ">";
		   	break;
	   	case lt:
		   	$_SESSION[$var] = "<";
		   	break;
	   	case eq:
		   	$_SESSION[$var] = "=";
		   	break;
	   	case gte:
		   	$_SESSION[$var] = ">=";
		   	break;
	   	case lte:
		   	$_SESSION[$var] = "<=";
		   	break;
   	}
   	?>
	   	select = document.getElementById('<?php echo $var; ?>');
   	for (i=0; i<select.length; i++) {
	   	if (select.options[i].text == '<?php echo $_SESSION[$var]; ?>') {
		   	select.selectedIndex = i;
		   	break;
	   	}
   	}
   	<?php
} // end limit,topx,orderby,order restore
echo "document.getElementById('dupcount').value = '".$_SESSION['dupcount']."';\n";

// begin msg restore
for ($i=1; $i<=3; $i++) {
   	if (isset($_SESSION['ExcludeMsg'.$i]) && $_SESSION['ExcludeMsg'.$i]=='on') {
	   	echo "document.getElementById('ExcludeMsg${i}').checked = true;\n";
   	}
   	if (isset($_SESSION['RegExpMsg'.$i]) && $_SESSION['RegExpMsg'.$i]=='on') {
	   	echo "document.getElementById('RegExpMsg${i}').checked = true;\n";
   	}
   	echo "document.getElementById('msg${i}').value = '".$_SESSION['msg'.$i]."';\n";
}
// end msg restore

echo "</script>\n";
/* END: Added by BPK to automatically repopulate the search form selections from $_SESSION using JavaScript */
?>

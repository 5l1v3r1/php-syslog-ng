<?php
// sleep(1);
require_once ("../config/config.php");
require_once ("../includes/common_funcs.php");

function echoPage($page, $limit) {
   	$dbLink = db_connect_syslog(DBUSER, DBUSERPW);
	   	if ($limit <= 10) {
	   	echo " <a href='wres.php?page=$page&limit=" . ($limit + 10) . "' class='load_more'></a	";
	   	$sqllimit = "0,$limit";
		} else {
	   	$sqllimit = "10,$limit";
		}
   	echo "<div style=\"width: 310px; margin-bottom: 5px; margin-left: 5px; text-align: center; font-size: 5px;\">";
   	echo " <table> ";
   	if ($page == "tail"){
	   	if ($limit <= 10) {
		   	echo "
			   	<td class=\"wapresultsheader\">HOST</td>
			   	<td class=\"wapresultsheader\">TIME</td>
			   	<td class=\"wapresultsheader\">MESSAGE</td>
			   	";
	   	}
	   	$query = "SELECT * FROM logs JOIN (select seq from logs FORCE INDEX(PRIMARY) ORDER BY seq DESC LIMIT $sqllimit) as sub USING(seq)";
   	}
   	if ($page == "topx" ){
	   	if ($limit <= 10) {
		   	echo "
			   	<td class=\"wapresultsheader\">HOST</td>
			   	<td class=\"wapresultsheader\">COUNT</td>
			   	<td class=\"wapresultsheader\">MESSAGE</td>
			   	";
	   	}
	   	if(defined('SQZ_ENABLED') && SQZ_ENABLED == TRUE) {
		   	$query = "SELECT host,counter as count,msg from logs WHERE counter>1 ORDER BY counter DESC LIMIT $sqllimit";
	   	} else {
		   	$query = "SELECT host,count(*) as count,msg from logs GROUP BY host ORDER BY count DESC LIMIT $sqllimit";
	   	}
   	}
   	$results = perform_query($query, $dbLink);
   	$color = "waplighter";
   	$today = date('Y-m-d');
   	while($row = fetch_array($results)) {
	   	if($color == "wapdarker") {
		   	$color = "waplighter";
	   	}
	   	else {
		   	$color = "wapdarker";
	   	}
	   	echo "<tr class=\"$color\">";
	   	echo "<td>".$row['host']."</td>";
	   	if ($page == "tail") {
		   	$pieces = explode(" ", $row['datetime']);
		   	echo '<td>';
		   	if ($pieces[0]!=$today) {
			   	echo $pieces[0]."&nbsp;";
		   	}
		   	echo $pieces[1];
	   	}
	   	if ($page == "topx") {
		   	echo '<td>';
		   	echo $row['count']."&nbsp;";
	   	}
	   	echo "</td>\n";
	   	$row['msg'] = preg_replace('/\s:/', ':', $row['msg']);
	   	$row['msg'] = preg_replace('/.*(%.*?:.*)/', '$1', $row['msg']);
	   	$msg = htmlspecialchars($row['msg']);
	   	echo "<td>";
	   	echo "$msg</td>\n";
	   	echo    "</tr>\n";
   	}
	   	echo    "</div>\n";
}

echoPage($_GET['page'],$_GET['limit']);

?>

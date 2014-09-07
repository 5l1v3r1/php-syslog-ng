<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com
?>
<table>
<!-- cdukes - removed below to make things easier to read -->
<!-- http://code.google.com/p/php-syslog-ng/issues/detail?id=25 -->
<!--
<tr><td><center>
<i>Use this link to reference this query directly: </i>
<a href="<?php echo $_SERVER["PHP_SELF"]."?offset=".$offset.$ParamsGET; ?>">QUERY</a><br>
</center></td></tr></table>
-->
<table>
<tr><td>
<a href="index.php?pageId=searchform">BACK TO SEARCH</a><br>
<?php
/* cdukes - 2-28-08: Added !stristr($_SERVER[REQUEST_URI],"graph") below so that people didn't get confused by the results
   displayed on a Top 10 search (since below would show TOTAL results, not selected results, ie. Selected = 10 for Top 10)
   */
if(defined('COUNT_ROWS') && COUNT_ROWS == TRUE && $num_results && !stristr($_SERVER[REQUEST_URI],"graph")) {
        echo "<i>Number of Entries Found</i>: <b>".commify($num_results)."</b>";
}
?>
</td><td>
<div class="sevlegend">
SEVERITY LEGEND<br>
<?php	/*<table>
	<tr>
	<td class="sev0">DEBUG</td>
	<td class="sev1">INFO</td>
	<td class="sev2">NOTICE</td>
	<td class="sev3">WARNING</td>
	<td class="sev4">ERROR</td>
	<td class="sev5">CRIT</td>
	<td class="sev6">ALERT</td>
	<td class="sev7">EMERG</td>
	</tr></table> */
?>
	<span class="sev0">DEBUG</span>
	<span class="sev1">INFO</span>
	<span class="sev2">NOTICE</span>
	<span class="sev3">WARNING</span>
	<span class="sev4">ERROR</span>
	<span class="sev5">CRIT</span>
	<span class="sev6">ALERT</span>
	<span class="sev7">EMERG</span>
</div>
</td></tr></table>
<!-- cdukes - removed below to make things easier to read -->
<!-- http://code.google.com/p/php-syslog-ng/issues/detail?id=25 -->
<?php
if(defined('DEBUG') && DEBUG == TRUE) {
   	?>
	   	<table class="query">
	   	<tr><td >
	   	The SQL query:
	   	<input type=text size=85 value="<?php echo $query; ?>">
	   	<?php
} 
?>
</td></tr></table>

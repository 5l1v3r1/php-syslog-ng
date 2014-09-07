<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com
// Copyright (C) 2006 Clayton Dukes, cdukes@cdukes.com

$basePath = dirname( __FILE__ );
require_once ($basePath . "/common_funcs.php");

?>
<html>
<head>
<?php 
// custom title for tail queries 
// Ref: http://code.google.com/p/php-syslog-ng/issues/detail?id=33
// echo "<title>".PAGETITLE." ".VERSION.": ".$addTitle."</title>"; 
echo "<title>".$addTitle.": ".PAGETITLE." ".VERSION."</title>"; 
?>
<link rel=stylesheet type=text/css href='css/default.css'>
<link rel="icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" type="image/x-icon" />
<link rel="shortcut icon" href="favicon.ico" />
<META HTTP-EQUIV='Pragma' CONTENT='no-cache'>
 <!-- Begin Calendar -->
 <script type="text/javascript" src="includes/cal/Bs_Misc.lib.js"></script>
 <script type="text/javascript" src="includes/cal/Bs_Button.class.js"></script>
 <script type="text/javascript" src="includes/cal/Bs_DatePicker.class.js"></script>
 <script type="text/javascript" src="includes/cal/Bs_FormFieldSelect.class.js"></script>
 <script type="text/javascript">
 if (moz) {
	 document.writeln("<link rel='stylesheet' href='css/win2k_mz.css'>");
 } else {
	 document.writeln("<link rel='stylesheet' href='css/win2k_ie.css'>");
 }

function fromdate() {
	myDatePicker = new Bs_DatePicker();
	myDatePicker.jsBaseDir = '/';
	myDatePicker.toggleButton.imgPath = 'images/buttons/';
	myDatePicker.fieldName                  = 'date';
	myDatePicker.openByInit                 = true;
	myDatePicker.dateFormat                 = 'ISO';
	myDatePicker.useSpinEditForYear         = false;

	myDatePicker.dateInputClassName         = 'datePickerDate';
	myDatePicker.monthSelectClassName       = 'datePickerMonth';
	myDatePicker.yearInputClassName         = 'datePickerYear';
	myDatePicker.dayTableClassName          = 'datePickerTable';
	myDatePicker.dayHeaderClassName         = 'datePickerDayHeader';
	myDatePicker.dayClassName               = 'datePickerDay';
	myDatePicker.dayClassNameByWeekday['7'] = 'datePickerDaySunday';
	myDatePicker.dayTableAttributeString    = 'width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="white"';

	myDatePicker.dayHeaderFontColor         = 'yellow';
	myDatePicker.dayHeaderBgColor           = 'green';
	myDatePicker.dayFontColor               = 'brown';
	myDatePicker.dayBgColor                 = 'antiquewhite';
	myDatePicker.dayFontColorActive         = 'red';
	myDatePicker.dayBgColorActive           = 'white';
	myDatePicker.dayTableBgColor            = 'silver';
	myDatePicker.dayBgColorOver             = 'yellow';

	myDatePicker.width                      = 115;
	myDatePicker.daysNumChars               = 1;
	myDatePicker.drawInto('myDatePickerDiv');
}
function todate() {
	myDatePicker = new Bs_DatePicker();
	myDatePicker.jsBaseDir = '/';
	myDatePicker.toggleButton.imgPath = 'images/buttons/';
	myDatePicker.fieldName                  = 'date2';
	myDatePicker.openByInit                 = true;
	myDatePicker.dateFormat                 = 'ISO';
	myDatePicker.useSpinEditForYear         = false;

	myDatePicker.dateInputClassName         = 'datePickerDate';
	myDatePicker.monthSelectClassName       = 'datePickerMonth';
	myDatePicker.yearInputClassName         = 'datePickerYear';
	myDatePicker.dayTableClassName          = 'datePickerTable';
	myDatePicker.dayHeaderClassName         = 'datePickerDayHeader';
	myDatePicker.dayClassName               = 'datePickerDay';
	myDatePicker.dayClassNameByWeekday['7'] = 'datePickerDaySunday';
	myDatePicker.dayTableAttributeString    = 'width="100%" border="1" cellspacing="0" cellpadding="0" bordercolor="white"';

	myDatePicker.dayHeaderFontColor         = 'yellow';
	myDatePicker.dayHeaderBgColor           = 'green';
	myDatePicker.dayFontColor               = 'brown';
	myDatePicker.dayBgColor                 = 'antiquewhite';
	myDatePicker.dayFontColorActive         = 'red';
	myDatePicker.dayBgColorActive           = 'white';
	myDatePicker.dayTableBgColor            = 'silver';
	myDatePicker.dayBgColorOver             = 'yellow';

	myDatePicker.width                      = 115;
	myDatePicker.daysNumChars               = 1;
	myDatePicker.drawInto('myDatePickerDivR');
}
</script>
<!-- End Calendar -->
<!-- Begin Overlib -->
<script src="includes/js/overlib.js" language="Javascript" type="text/javascript"></script>
<DIV id=overDiv	style="Z-INDEX: 1000; VISIBILITY: hidden; POSITION: absolute"></DIV>
<!-- End Overlib -->

<!-- Begin Google Code -->
<?php
if (is_file("includes/google-analytics.html")) {
include "includes/google-analytics.html";
}
?>
<!-- End Google Code -->

</head>
<body>
<table class="header">
<!-- cdukes - Removed below per request at http://code.google.com/p/php-syslog-ng/issues/detail?id=25
<tr><td>
	<a href="index.php"><h2 class="logo"><?php echo $version?></h2></a>
	Network Syslog Monitor
</td><td class="headerright">
	<?php echo date("l F dS, Y - H:i:s"); ?><br>
	Your IP: <?php echo $_SERVER['REMOTE_ADDR']; ?>
</td></tr>
-->
</table>
<table class="headerbottom"><tr><td>
<?php
if((secure() == $_SESSION["member_id"]) && (USE_ACL == 'TRUE' || USE_ACL == 'YES' ))  {
	echo "<a class=\"vertmenu\" href=\"index.php?pageId=logout\">Logout</a>";
	echo "<a class=\"vertmenu\" href=\"index.php?pageId=searchform\">Search</a>";
	echo "<a class=\"vertmenu\" href=\"index.php?pageId=config\">Config</a>";
   	echo "<a class=\"vertmenu\" href=\"index.php?pageId=help\">Help</a>\n";
   	echo "<a class=\"vertmenu\" href=\"index.php?pageId=about\">About</a>\n";
	// Begin Paypal Addition
	if (PAYPAL_ENABLE == "YES") {
   	echo "&nbsp;<td align=\"right\">";
	?>
		<!-- Begin Paypal link -->
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_donations">
		<input type="hidden" name="business" value="cdukes@cdukes.com">
		<input type="hidden" name="item_name" value="PHP-Syslog-NG">
		<input type="hidden" name="no_shipping" value="0">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="tax" value="0">
		<input type="hidden" name="lc" value="US">
		<input type="hidden" name="bn" value="PP-DonationsBF">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="Donate to a good cause :-)">
		<div align="right">
		The code you support today may<br>
		turn out to be <a href="http://en.wikipedia.org/wiki/Skynet_%28Terminator%29">SkyNet</a> tomorrow...
		</div>
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		<!-- END Paypal link -->
		<?php
	}
	// End Paypal Addition
   	echo "&nbsp;</td></tr>";
   	echo "</table><center>\n";

// cdukes - Added below for non ACL systems (open access)
} elseif(!defined('USE_ACL') || !USE_ACL || !defined('REQUIRE_AUTH') || !REQUIRE_AUTH) {
	echo "<a class=\"vertmenu\" href=\"index.php?pageId=searchform\">Search</a>";
   	echo "<a class=\"vertmenu\" href=\"index.php?pageId=help\">Help</a>\n";
   	echo "<a class=\"vertmenu\" href=\"index.php?pageId=about\">About</a>\n";
	// Begin Paypal Addition
	if (PAYPAL_ENABLE == "YES") {
   	echo "&nbsp;<td align=\"right\">";
	?>
		<!-- Begin Paypal link -->
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_donations">
		<input type="hidden" name="business" value="cdukes@cdukes.com">
		<input type="hidden" name="item_name" value="PHP-Syslog-NG">
		<input type="hidden" name="no_shipping" value="0">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="tax" value="0">
		<input type="hidden" name="lc" value="US">
		<input type="hidden" name="bn" value="PP-DonationsBF">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="Donate to a good cause :-)">
		<div align="right">
		Every time you donate...<br>
		God decides not to kill a kitten :-)
		</div>
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		<!-- END Paypal link -->
		<?php
	}
	// End Paypal Addition
   	echo "&nbsp;</td></tr>";
   	echo "</table><center>\n";

} else { // basic user (using authentication, but not logged in)
	echo "<a class=\"vertmenu\" href=\"index.php?pageId=login\">Login</a>";
   	echo "<a class=\"vertmenu\" href=\"index.php?pageId=help\">Help</a>\n";
   	echo "<a class=\"vertmenu\" href=\"index.php?pageId=about\">About</a>\n";
	// Begin Paypal Addition
	if (PAYPAL_ENABLE == "YES") {
   	echo "&nbsp;<td align=\"right\">";
	?>
		<!-- Begin Paypal link -->
	<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
		<input type="hidden" name="cmd" value="_donations">
		<input type="hidden" name="business" value="cdukes@cdukes.com">
		<input type="hidden" name="item_name" value="PHP-Syslog-NG">
		<input type="hidden" name="no_shipping" value="0">
		<input type="hidden" name="no_note" value="1">
		<input type="hidden" name="currency_code" value="USD">
		<input type="hidden" name="tax" value="0">
		<input type="hidden" name="lc" value="US">
		<input type="hidden" name="bn" value="PP-DonationsBF">
		<input type="image" src="https://www.paypal.com/en_US/i/btn/btn_donate_SM.gif" border="0" name="submit" alt="Donate to a good cause :-)">
		<img alt="" border="0" src="https://www.paypal.com/en_US/i/scr/pixel.gif" width="1" height="1">
		</form>
		<!-- END Paypal link -->
		<?php
	}
	// End Paypal Addition
   	echo "&nbsp;</td></tr></table><center>\n";
}
?>

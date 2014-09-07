<?php require_once ("../config/config.php"); 
$url ="";
if (preg_match("/\w+/", SITEURL)) {
	$url = SITEURL;
}
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">
<html><head>
<img src="<?php echo $url ?>/images/php-syslog-ng.png" width="35" height="35" align="left">
<title>
Log Browser
</title>
    <meta name="viewport" content="width=320"/>
    <meta name="viewport" content="initial-scale=1.0, user-scalable=no"/>
    <link rel="stylesheet" type="text/css" href="<?php echo $url ?>/wap/wap.css" />
    <script src="<?php echo $url ?>/wap/wap.js" type="text/javascript"></script>
</head>

<body>
    <div id="iphone_header">
        <div id="iphone_backbutton">
            <img src="<?php echo $url ?>/images/wap/back-button-tip.png" border="0" align="left" />
            <a id="iphone_backbutton_text" href="#" class="go_back">Back</a>
        </div>
    	<div id="iphone_title"></div>
	</div>

	<div id="iphone_body" style="clear:both;">
		<ul class="menu">
			<!-- <li><a href="wform.php?demo=1" class="go_forward" title="Search">Search</a></li>-->
			<!-- <li><a href="wap_form.php?demo=2" class="go_forward" title="Form Demo 2">Form Demo 2</a></li> -->
			<li><a href="wres.php?page=tail&limit=10" class="go_forward" title="Latest Events">Latest Events</a></li>
			<li><a href="wres.php?page=topx&limit=10" class="go_forward" title="Top Talkers">Top Talkers</a></li>
			<!--<li><a href="wap_page.php?page=2" class="go_forward" title="Item 2">Item 2</a></li> -->
			<!--<li><a href="wap_page.php?page=3" class="go_forward" title="Item 3">Item 3</a></li> -->
		</ul>
	</div>

	<div id="iphone_footer">PHP-Sysog-NG</div>

	<div id="iphone_loading_page">
		<div id='loading' class="info_msg">
			<img src="<?php echo $url ?>/images/wap/loading.gif" /><br />
			loading...
		</div>
	</div>
</body>
</html>

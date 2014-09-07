<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com
// Copyright (C) 2006 Clayton Dukes, cdukes@cdukes.com

// Check to see if config.php is set, if not we need to run the installer.
$chk_config = file_get_contents("config/config.php");
if (strlen($chk_config) < 10) {
   	header('Location: install/');
} else {
   	require_once ("config/config.php");
   	require_once 'includes/common_funcs.php';
}



// Added below for v2.9.4 (LDAP)
// session handler
session_start();
// Secure this page
secure();

$time_start = get_microtime();

//------------------------------------------------------------------------
// Determine what page is being requested
//------------------------------------------------------------------------
$pageId = get_input('pageId');
if (!$pageId) { $pageId = "login"; }
if(!validate_input($pageId, 'pageId')) {
	echo "Error on pageId validation! <br>Check your regExpArray in config.php!\n";
   	$pageId = "login";
}

//------------------------------------------------------------------------
// Connect to database. If connection fails then set the pageId for the
// help page.
//------------------------------------------------------------------------
$dbProblem = FALSE;
if(!$dbLink = db_connect_syslog(DBUSER, DBUSERPW)) {
   	$pageId = "help";
   	$dbProblem = TRUE;
}


//------------------------------------------------------------------------
// Load page
//------------------------------------------------------------------------
if(strcasecmp($pageId, "searchform") == 0) {
	$addTitle = "SEARCH";
	require 'includes/search.php';
}
elseif(strcasecmp($pageId, "login") == 0) {
	$addTitle = "LOGIN";
	require 'login.php';
}
elseif(strcasecmp($pageId, "logout") == 0) {
	$addTitle = "LOGOUT";
	require 'logout.php';
}
elseif(strcasecmp($pageId, "about") == 0) {
	$addTitle = "ABOUT";
	require 'includes/about.php';
}
elseif(strcasecmp($pageId, "help") == 0) {
	$addTitle = "HELP";
	require 'includes/help.php';
}
elseif(strcasecmp($pageId, "config") == 0) {
	$addTitle = "CONFIG";
	require 'includes/configure.php';
}
elseif(strcasecmp($pageId, "Tail") == 0) {
	// custom title for tail queries 
	// Ref: http://code.google.com/p/php-syslog-ng/issues/detail?id=33
   	if ($_REQUEST['title'] == '') {
	   	$addTitle = "TAIL RESULTS";
   	}
   	else {
	   	$addTitle = $_REQUEST['title'];
   	}
	require 'includes/tailresult.php';
}
elseif(strcasecmp($pageId, "Graph") == 0) {
	$addTitle = "GRAPH RESULTS";
	require 'includes/graphit.php';
}
elseif(strcasecmp($pageId, "search") == 0) {
	$addTitle = "REGULAR RESULTS";
	require 'includes/regularresult.php';
}
else {
	$addTitle = "SEARCH";
	require 'includes/search.php';
}
require_once 'includes/html_footer.php';
?>

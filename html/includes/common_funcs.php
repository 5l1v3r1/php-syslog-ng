<?php
// Copyright (C) 2001-2004 by Michael Earls, michael@michaelearls.com
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com
// Copyright (C) 2006 Clayton Dukes, cdukes@cdukes.com

$basePath = dirname( __FILE__ );
include_once ($basePath ."/../config/config.php");

error_reporting(E_ALL & ~E_NOTICE);
//------------------------------------------------------------------------
// This function returns the current microtime.
//------------------------------------------------------------------------
function get_microtime() {
	list($usec, $sec) = explode(" ", microtime());
	return ((float)$usec + (float)$sec);
}


//------------------------------------------------------------------------
// This functions verifies a username/password combination. If the
// combination exists then the function returns TRUE. If not then it
// returns FALSE.
//------------------------------------------------------------------------
function verify_login($username, $password, $link) {
	// If the username or password is blank then return FALSE.
	if(!$username || !$password) {
		return FALSE;
	}

	// Get the md5 hash of the password and query the database.
	$pwHash = md5($password);
	$query = "SELECT * FROM ".AUTHTABLENAME." WHERE username='".$username."' AND pwhash='".$pwHash."'";
	// die($query);
	$result = perform_query($query, $link);

	// If the query returns one result row then return TRUE.
	if(num_rows($result) == 1) {
		return TRUE;
	}
	else {
		return FALSE;
	}
}

//------------------------------------------------------------------------
// This functions verifies a username only. If the username
// exists then the function returns TRUE. If not then it
// returns FALSE. Implemented for Web Basic auth.
// Ref: http://code.google.com/p/php-syslog-ng/issues/detail?id=62
//------------------------------------------------------------------------
function verify_user($username, $link) {
       // If the username is blank, then return FALSE.
       if (!$username) {
               return FALSE;
       }

       // Query the database
       $query = "SELECT * FROM ".AUTHTABLENAME." WHERE username='".$username."'";
       $result = perform_query($query, $link);

       // If the query returns one result row then return TRUE.
       if(num_rows($result) == 1) {
               return TRUE;
       } else {
               return FALSE;
       }
}


//------------------------------------------------------------------------
// This function verifies a username/sessionId combination. If the
// combination exists then the function returns TRUE. If not then it
// returns FALSE. If the RENEW_SESSION_ON_EACH_PAGE parameter is set then
// the functions also updates the timestamp for the session after it is
// verified.
//------------------------------------------------------------------------
function verify_session($username, $sessionId, $link) {
	// If the username or sessionId is blank then return FALSE.
	if(!$username || !$sessionId) {
		return FALSE;
	}

	// Query the database.
	$query = "SELECT * FROM ".AUTHTABLENAME." WHERE username='".$username."' 
		AND sessionid='".$sessionId."' AND exptime>now()";
	$result = perform_query($query, $link);

	// If the query returns one result row then the session is verified.
	if(num_rows($result) == 1) {
		//If RENEW_SESSION_ON_EACH_PAGE is set then update the
		// session timestamp in the database.
		if(defined('RENEW_SESSION_ON_EACH_PAGE') && RENEW_SESSION_ON_EACH_PAGE == TRUE) {
			$expTime = time()+SESSION_EXP_TIME;
			$expTimeDB = date('Y-m-d H:i:s', $expTime);
			$query = "UPDATE ".AUTHTABLENAME." SET exptime='".$expTimeDB."'
				WHERE username='".$username."'";
			perform_query($query, $link);
		}
		return TRUE;
	}
	else {
		return FALSE;
	}
}


//------------------------------------------------------------------------
// Function used to retrieve input values and if neccessary add slashes.
//------------------------------------------------------------------------
function get_input($varName, $check_session=true) {
   	$value="";
   	if(isset($_COOKIE[$varName])) {
	   	$value = $_COOKIE[$varName];
   	} elseif(isset($_GET[$varName])) {
	   	$value = $_GET[$varName];
   	} elseif(isset($_POST[$varName])) {
	   	$value = $_POST[$varName];
	/** 
	 * BPK: we can't always use this, else checkboxes never get unset, 
	 * rather let js reload the form at the end of search.php
	 */
   	} elseif($check_session && isset($_SESSION[$varName])) {
	   	$value = $_SESSION[$varName];
   	} 
	if($value && !get_magic_quotes_gpc()) {
	   	if(!is_array($value)) {
		   	$value = addslashes($value);
	   	}
	   	else {
		   	foreach($value as $key => $arrValue) {
			   	$value[$key] = addslashes($arrValue);
		   	}
	   	}
   	}
   	return $value;
}


//------------------------------------------------------------------------
// Function used to validate user supplied variables.
//------------------------------------------------------------------------
function validate_input($value, $regExpName) {
	global $regExpArray;

	if(!$regExpArray[$regExpName]) {
		return FALSE;
	}

	if(is_array($value)) {
		foreach($value as $arrval) {
			if(!preg_match("$regExpArray[$regExpName]", $arrval)) {
				return FALSE;
			}
		}
		return TRUE;
	}
	elseif(preg_match("$regExpArray[$regExpName]", $value)) {
		return TRUE;
	}
	else {
		return FALSE;
	}
}


//------------------------------------------------------------------------
// This function reloads the cache with data from $table.
//------------------------------------------------------------------------
function reload_cache($table, $link) {
	$cacheHostValues = array();
	$cacheFacilityValues = array();
	// Start - http://code.google.com/p/php-syslog-ng/issues/detail?id=31
	// $add = "";

	/* cdukes - 2-28-08: Added a default of "NONE" below so that the array would not be empty which causes the INSERT to fail if no results are returned) */
	$cacheProgramValues = array("NONE");
	// End - http://code.google.com/p/php-syslog-ng/issues/detail?id=31

	// Get new cache values from $table

	$sql = "SELECT DISTINCT host FROM ".$table;
	$result = perform_query($sql, $link);
	while($row = fetch_array($result, 'ASSOC')) {
		array_push($cacheHostValues, $row['host']);
	} 

	$sql = "SELECT DISTINCT facility FROM ".$table;
	$result = perform_query($sql, $link);
	while($row = fetch_array($result, 'ASSOC')) {
		array_push($cacheFacilityValues, $row['facility']);
	}

   	// Start - http://code.google.com/p/php-syslog-ng/issues/detail?id=31
   	// $sql = "SELECT DISTINCT program FROM ".$table." WHERE LENGTH(program)<80 AND program RLIKE '^[a-zA-Z]+[a-zA-Z0-9/()._\-]+$' limit 50";
   	// removed DISTINCT and LENGTH keywords in order to speed up searching on large databases
	// Instead, we'll use php's array_unique function instead of relying on mysql to do it (which is too slow!)
   	// Ref: http://code.google.com/p/php-syslog-ng/issues/detail?id=73
   	$sql = "SELECT program FROM ".$table." WHERE program RLIKE '^[a-zA-Z]+[a-zA-Z0-9/()._\-]+$'";
   	$result = perform_query($sql, $link);
   	while($row = fetch_array($result, 'ASSOC')) {
	     if (!in_array($row['program'],$cacheProgramValues))	
                array_push($cacheProgramValues, $row['program']);
   	}
   
	$cacheProgramValues=array_unique($cacheProgramValues);
   	// End - http://code.google.com/p/php-syslog-ng/issues/detail?id=31

	// Prepare INSERT statements
	$updateTime = date("Y-m-d H:i:s");
	$insertHost = "INSERT INTO ".CACHETABLENAME." (tablename, type, value, updatetime) VALUES ";
	foreach($cacheHostValues as $value) {
		// cdukes: 4/29/08 - Added 'addslashes' below to quote any odd sql inserts
		// Ref: http://groups.google.com/group/php-syslog-ng-support/browse_thread/thread/eda3c96999889b47
		// $add = "('".$table."', 'HOST', '".$value."', '".$updateTime."'),";
		$add = "('".$table."', 'HOST', '".addslashes($value)."', '".$updateTime."'),";
		$insertHost .= $add;
	}
	$insertHost = rtrim($insertHost, ',');
	if (!$add) {
		die("<center><em>There appear to be no hosts in the Database yet<br>
				You can generate fake ones using scripts/dbgen.pl<br>
				</em></center>
				");
	}

	$insertFacility = "INSERT INTO ".CACHETABLENAME." (tablename, type, value, updatetime) VALUES ";
	foreach($cacheFacilityValues as $value) {
		$add = "('".$table."', 'FACILITY', '".$value."', '".$updateTime."'),";
		$insertFacility .= $add;
	}
	$insertFacility = rtrim($insertFacility, ',');

	// Start - http://code.google.com/p/php-syslog-ng/issues/detail?id=31
	$insertProgram = "INSERT INTO ".CACHETABLENAME." (tablename, type, value, updatetime) VALUES ";
	foreach($cacheProgramValues as $value) {
		$add = "('".$table."', 'PROGRAM', '".$value."', '".$updateTime."'),";
		$insertProgram .= $add;
	}
	$insertProgram = rtrim($insertProgram, ',');
	// End - http://code.google.com/p/php-syslog-ng/issues/detail?id=31


	// Insert new cache values for $table
	perform_query($insertHost, $link);
	perform_query($insertFacility, $link);
	// Start - http://code.google.com/p/php-syslog-ng/issues/detail?id=31
	perform_query($insertProgram, $link);
	// End - http://code.google.com/p/php-syslog-ng/issues/detail?id=31

	// Drop old cache values for $table
	$sql = "DELETE FROM ".CACHETABLENAME." WHERE tablename='".$table.
		"' AND updatetime<'".$updateTime."'";
	perform_query($sql, $link);
}


//========================================================================
// BEGIN DATABASE FUNCTIONS
//========================================================================
//------------------------------------------------------------------------
// This function connects to the MySQL server and selects the database
// specified in the DBNAME parameter. If an error occurs then return
// FALSE.
//------------------------------------------------------------------------
function db_connect_syslog($dbUser, $dbPassword, $connType = 'P') {
	$server_string = DBHOST.":".DBPORT;
	$link = "";
	if(function_exists('mysql_pconnect') && $connType == 'P') {
		$link = @mysql_pconnect($server_string, $dbUser, $dbPassword);
	}
	elseif(function_exists('mysql_connect')) {
		$link = @mysql_connect($server_string, $dbUser, $dbPassword);
	}
	if(!$link) {
		return FALSE;
	}

	$result = mysql_select_db(DBNAME, $link);
	if(!$result) {
		return FALSE;
	}

	return $link;
}


//------------------------------------------------------------------------
// This functions performs the SQL query and returns a result resource. If
// an error occurs then execution is halted an the MySQL error is
// displayed.
//------------------------------------------------------------------------
function perform_query($query, $link) {
	if($link) {
		$result = mysql_query($query, $link); 
			if (!$result) {
			print ("Error in \"function perform_query()\" <br>Mysql_error: " .mysql_error() ."<br>Query was: $query<br>"); 
			return ("Error in \"function perform_query()\" <br>Mysql_error: " .mysql_error()); 
			}
	}
	else {
		die("Error in perform_query function<br> No DB link for query: $query<br>Mysql_error: " .mysql_error());
	}

	return $result;
}
//------------------------------------------------------------------------
// This functions performs the SQL query and returns a result resource. If
// an error occurs then nothing happens. This is ONLY used in logrotate.php
// to check for row data of non-existent tables. 
// In other words, we are SUCCESSFUL if there is an error.
//------------------------------------------------------------------------

function perform_query_quiet($query, $link) {
	if($link) {
		$result = @mysql_query($query, $link); 
	}
	else {
		die("Error in perform_query function<br> No DB link for query: $query<br>Mysql_error: " .mysql_error());
	}

	return $result;
}


//------------------------------------------------------------------------
// This functions returns a result row as an array.
// The type can be BOTH, ASSOC or NUM.
//------------------------------------------------------------------------
function fetch_array($result, $type = 'BOTH') {
	if($type == 'BOTH') {
		return mysql_fetch_array($result);
	}
	elseif($type == 'ASSOC') {
		return mysql_fetch_assoc($result);
	}
	elseif($type == 'NUM') {
		return mysql_fetch_row($result);
	}
	else {
		die('Wrong type for fetch_array()');
	}
}


//------------------------------------------------------------------------
// This functions sets the row offset for a result resource
//------------------------------------------------------------------------
function result_seek($result, $rowNumber) {
	mysql_data_seek($result, $rowNumber);
}


//------------------------------------------------------------------------
// This functions returns a result row as an array
//------------------------------------------------------------------------
function num_rows($result) {
	return mysql_num_rows($result);
}


//------------------------------------------------------------------------
// This function checks if a particular table exists.
//------------------------------------------------------------------------
function table_exists($tableName, $link) {
	$tables = get_tables($link);
	if(array_search($tableName, $tables) !== FALSE) {
		return TRUE;
	}
	else {
		return FALSE;
	}
}


//------------------------------------------------------------------------
// This function returns an array of the names of all tables in the
// database.
//------------------------------------------------------------------------
function get_tables($link) {
	$tableList = array();
	$query = "SHOW TABLES";
	$result = perform_query($query, $link);
	while($row = fetch_array($result)) {
		array_push($tableList, $row[0]);
	}

	return $tableList;
}


//------------------------------------------------------------------------
// This function returns an array with the names of tables with log data.
//------------------------------------------------------------------------
function get_logtables($link) {
	// Create an array of the column names in the default table
	$query = "DESCRIBE ".DEFAULTLOGTABLE;
	$result = perform_query($query, $link);
	$defaultFieldArray = array();
	while($row = mysql_fetch_array($result)) {
		array_push($defaultFieldArray, $row['Field']);
	}

	// Create an array with the names of all the log tables
	$logTableArray = array();
	$allTablesArray = get_tables($link);
	foreach($allTablesArray as $value) {
		// Create an array of the column names in the current table
		$query = "DESCRIBE ".$value;
		$result = perform_query($query, $link);
		// Get the names of columns in current table
		$fieldArray = array();
		while ($row = mysql_fetch_array($result)) {
			array_push($fieldArray, $row['Field']);
		}

		// If the current array is identical to the one from the
		// DEFAULTLOGTABLE then the name is added to the result
		// array.
		$diffArray = array_diff_assoc($defaultFieldArray, $fieldArray);
		if(!$diffArray) {
			array_push($logTableArray, $value);
		}
	}
	return $logTableArray;
}
//========================================================================
// END DATABASE FUNCTIONS
//========================================================================

//========================================================================
// BEGIN ACCESS CONTROL FUNCTIONS
//========================================================================
//------------------------------------------------------------------------
// This function verifies that the user has access to a particular part
// of php-syslog-ng.
// Inputs are:
// username
// actionName
// dbLink
//
// Outputs TRUE or FALSE
//------------------------------------------------------------------------
function grant_access($userName, $actionName, $link) {
	// If ACL is not used then always return TRUE
	if(!defined('USE_ACL') || !USE_ACL || !defined('REQUIRE_AUTH') || !REQUIRE_AUTH) {
		return TRUE;
	}

	// Get user access
	$sql = "SELECT access FROM ".USER_ACCESS_TABLE." WHERE username='".$userName."' 
			AND actionname='".$actionName."'";
	$result = perform_query($sql, $link);
	$row = fetch_array($result);
	if(num_rows($result) && $row['access'] == 'TRUE') {
		return TRUE;
	}
	// Get default access
	else {
		$sql = "SELECT defaultaccess FROM ".ACTION_TABLE." WHERE actionname='".$actionName."'";
		$result = perform_query($sql, $link);
		$row = fetch_array($result);
		if($row['defaultaccess'] == 'TRUE') {
			return TRUE;
		}
		else {
			return FALSE;
		}
	}
}
//========================================================================
// END ACCESS CONTROL FUNCTIONS
//========================================================================

//========================================================================
// BEGIN REDIRECT FUNCTION
//========================================================================

function g_redirect($url,$mode)
/*  It redirects to a page specified by "$url".
 *  $mode can be:
 *    LOCATION:  Redirect via Header "Location".
 *    REFRESH:  Redirect via Header "Refresh".
 *    META:      Redirect via HTML META tag
 *    JS:        Redirect via JavaScript command
 */
{
  if (strncmp('http:',$url,5) && strncmp('https:',$url,6)) {

     $starturl = ($_SERVER["HTTPS"] == 'on' ? 'https' : 'http') . '://'.
                 (empty($_SERVER['HTTP_HOST'])? $_SERVER['SERVER_NAME'] :
                 $_SERVER['HTTP_HOST']);

     if ($url[0] != '/') $starturl .= dirname($_SERVER['PHP_SELF']).'/';

     $url = "$starturl$url";
  }

  switch($mode) {

     case 'LOCATION': 

       if (headers_sent()) exit("Headers already sent. Can not redirect to $url");

       header("Location: $url");
       exit;

     case 'REFRESH': 

       if (headers_sent()) exit("Headers already sent. Can not redirect to $url");

       header("Refresh: 0; URL=\"$url\""); 
       exit;

     case 'META': 

       ?><meta http-equiv="refresh" content="0;url=<?php echo $url?>" /><?php
       exit;

     case 'JS': 

       ?><script type="text/javascript">
       window.location.href='<?php echo $url?>';
       </script><?php
       exit;

     default: /* -- Java Script */

       ?><script type="text/javascript">
       window.location.href='<?php echo $url?>';
       </script><?php
  }
  exit;
}

//========================================================================
// END REDIRECT FUNCTION
//========================================================================

/*  Adds commas to a string of numbers
*/
function commify ($str) { 
        $n = strlen($str); 
        if ($n <= 3) { 
                $return=$str;
        } 
        else { 
                $pre=substr($str,0,$n-3); 
                $post=substr($str,$n-3,3); 
                $pre=commify($pre); 
                $return="$pre,$post"; 
        }
        return($return); 
}

/* Usage:

   $week = get_weekdates($year,$month,$day);

   for($i = 1; $i<=7 ; $i++) {

   echo 'Year: ' . $week[$i]['year'] . '<br>';
   echo 'Month: ' . $week[$i]['month'] . '<br>';
   echo 'Day: ' . $week[$i]['day'] . '<br>';
   echo 'Longname: ' . $week[$i]['dayname'] . '<br>';
   echo 'Shortname: ' . $week[$i]['shortdayname'] . '<br>';
   echo 'Sqldate: ' . $week[$i]['sqldate'] . '<br>';
   echo '<br>';

   }
 */

function get_weekdates($year, $month, $day){
	setlocale(LC_ALL, "C");
	//echo "Year $year<br>";
	//echo "Month $month<br>";
	//echo "Day $day<br>";

	// make unix time
	$searchdate = mktime(0,0,0,$month,$day,$year);
	//echo "Searchdate: $searchdate<br>";

	// let's get the day of week                //    on solaris <8 the first day of week is sunday, not monday
	$day_of_week = strftime("%u", $searchdate);  
	//echo "Debug: $day_of_week <br><br>";

	$days_to_firstday = ($day_of_week - 1);        //    on solaris <8 this may not work
	//echo "Debug: $days_to_firstday <br>";

	$days_to_lastday = (7 - $day_of_week);        //    on solaris <8 this may not work
	//echo "Debug: $days_to_lastday <br>";

	$date_firstday = strtotime("-".$days_to_firstday." days", $searchdate);
	//echo "Debug: $date_firstday <br>";

	$date_lastday = strtotime("+".$days_to_lastday. " days", $searchdate);
	//echo "Debug: $date_lastday <br>";

	$d_result = "";                    // array to return

	// write an array of all dates of this week 
	for($i=0; $i<=6; $i++) {
		$y = $i + 1;
		$d_date = strtotime("+".$i." days", $date_firstday);

		// feel free to add more values to these hashes
		$result[$y]['year'] = strftime("%Y", $d_date);
		$result[$y]['month'] = strftime("%m", $d_date);
		$result[$y]['day'] = strftime("%d", $d_date);
		$result[$y]['dayname'] = strftime("%A", $d_date);
		$result[$y]['shortdayname'] = strftime("%a", $d_date);
		$result[$y]['sqldate'] = strftime("%Y-%m-%d", $d_date);
	}

	return $result;                    // return the array
}

// Use this instead of count(*), it's faster (supposedly)
function get_total_rows ($table) {
	$temp = mysql_query("SELECT SQL_CALC_FOUND_ROWS * FROM $table LIMIT 1");
	$result = mysql_query("SELECT FOUND_ROWS()");
	$total = mysql_fetch_row($result);
	return $total[0];
}

// Added for better cookie handling
function getDomain() {
	if ( isset($_SERVER['HTTP_HOST']) ) {
		// Get domain
		$dom = $_SERVER['HTTP_HOST'];
		// Strip www from the domain
		if (strtolower(substr($dom, 0, 4)) == 'syslog.') { $dom = substr($dom, 4); }
		// Check if a port is used, and if it is, strip that info
		$uses_port = strpos($dom, ':');
		if ($uses_port) { $dom = substr($dom, 0, $uses_port); }
		// Add period to Domain (to work with or without www and on subdomains)
		$dom = '.' . $dom;
	} else {
		$dom = false;
	}
	return $dom;
}

# cdukes - Added below for 2.9.4
function secure () {
   	if (!($_SESSION["member_id"]) || ($_SESSION["member_id"] == "")) {
	   	// Cdukes: 3/20/08: Carry post variables through login
	   	// Ref: http://code.google.com/p/php-syslog-ng/issues/detail?id=35
	   	// Header("Location:" . LOGIN_URL);
		$destination = LOGIN_URL;
		// Remember search query across login
			if (!empty($_SERVER['QUERY_STRING']))
			{
				$destination .= '?' . $_SERVER['QUERY_STRING'];
				}
				Header("Location:" . $destination);
	   	exit();
	} else {
		return $_SESSION["member_id"];
   	}
}
function login_check ($forms) {
   	$error = "";
   	$username = $forms["username"];
   	$password = $forms["password"];
   	if (trim($username) == "") $error .= "<li>Your username is empty.</li>";
   	if (trim($password) == "") $error .= "<li>Your password is empty.</li>";
}

function login ($forms) {
   	$error = "";
   	$username = $forms["username"];
   	$password = $forms["password"];
   	if (!$password) { $password = "NoAnonymous_somebadtokenstring"; }
		// die("$password");

   	if ($forms["authtype"] == "ldap") {
	   	//define an appropriate ldap search filter to find your users, and filter out accounts such as administrator(administrator should be renamed anyway!).
	  	$filter="(&(|(!(displayname=Administrator*))(!(displayname=Admin*)))(" .LDAP_CN. "=$username))";
	   	$dn = LDAP_CN . "=$username, ";
	   	if (!($connect = @ldap_connect(LDAP_SRV))) {
		   	$error .= "Could not connect to LDAP server:" . LDAP_SRV;
	   	}

		switch (LDAP_MSAD) {
		
			case "YES":
				
				ldap_set_option($connect, LDAP_OPT_PROTOCOL_VERSION,3);
	                        ldap_set_option($connect, LDAP_OPT_REFERRALS,0);

				if (!($bind = @ldap_bind($connect, "$username@" . LDAP_DOMAIN, $password))) {
		                        $error .= " Unable to bind to LDAP Server: <b>" . LDAP_SRV . "</b><br> <li>DN: $dn<br> <li>BaseDN: " . LDAP_BASE_DN . "<br>";
                		}

				break;

			default:
		
				if (!($bind = @ldap_bind($connect, "$dn" . LDAP_BASE_DN, $password))) {
				   	$error .= " Unable to bind to LDAP Server: <b>" . LDAP_SRV . "</b><br> <li>DN: $dn<br> <li>BaseDN: " . LDAP_BASE_DN . "<br>";
	   			}
				
		}

	   	if (!($sr = @ldap_search($connect, LDAP_BASE_DN, $filter))) { #search for user
		   	$error .= " Unable to search: <b>" . LDAP_SRV . "</b><br> <li>DN: $dn<br> <li>BaseDN: " . LDAP_BASE_DN . "<br>";
	   	}

	   	$info = @ldap_get_entries($connect, $sr);
	   	// print  "Number of entries returned is " .ldap_count_entries($connect, $sr)."<p>";

	   	if (LDAP_USEPRIV == "ON") {
		   	if (in_array(LDAP_RW_GROUP, $info[0]["groupmembership"])) {
			   	$_SESSION["userpriv"] = "rw";
		   	} elseif (in_array(LDAP_RO_GROUP, $info[0]["groupmembership"])) {
			   	$_SESSION["userpriv"] = "ro";
		   	} else {
			   	$_SESSION["userpriv"] = "disabled";
			   	// echo "User privileges are " . $_SESSION["userpriv"] . "<br>";
		   	} 
		}


                if ( trim($error) != "" ) {
                         return $error;
                } else {

			$fullname=$info[0]["cn"][0];
        	        $fqdn=$info[0]["dn"];

	   		$_SESSION["username"] = $username;
		   	$_SESSION["groups"] = $info[0]["groupmembership"];
		   	$_SESSION["token"] = $password;
		   	$_SESSION["fullname"] = $fullname;
		   	$_SESSION["fqdn"] = $fqdn;
	   		$flname = explode(" ", $fullname);
		   	$_SESSION["firstname"] = $flname[0];
		   	$_SESSION["lastname"] = $flname[1];
	   		$_SESSION["pageId"] = "searchform" ;
		   	// die(phpinfo());
		   	// die(print_r($info[0]));
	   		// die(print_r($_SESSION));
		}

		/* from here, do your sql query to query the database to search for existing record with correct username and password */
   	} elseif ($forms["authtype"] == "basic") {
	   	// Using Web basic authentication. Check to see if $_SERVER['REMOTE_USER'] has access, and act accordingly.
	  	$username = $_SERVER['REMOTE_USER'];
	  	if ($username == "") {
			$username = "that user";
		}
	   	$dbLink = db_connect_syslog(DBUSER, DBUSERPW);
	   	if ($username && verify_user($username, $dbLink)) {
		   	$sessionId = md5(mt_rand());
		   	$_SESSION["pageId"] = "searchform";
		   	$expTime = time()+SESSION_EXP_TIME;
		   	$expTimeDB = date('Y-m-d H:i:s', $expTime);
		   	// Update sessionId and exptime in database
		   	$query = "UPDATE ".AUTHTABLENAME." SET sessionid='".$sessionId."', 
				exptime='".$expTimeDB."' WHERE username='".$username."'";
		   	$result = perform_query($query, $dbLink);
	   	} else {
		   	$error .= " Sorry, $username does not have access to this service.";
		   	$_SESSION["error"] = "$error";
	   	}
    	} elseif ($forms["authtype"] == "cert") {
           // Using Cert basic authentication.Check certificate SerialNumber first, Subject DN if SerialNumber fails
           $dbLink = db_connect_syslog(DBUSER, DBUSERPW);
           if (verify_user($_SERVER['SSL_CLIENT_M_SERIAL'], $dbLink) || verify_user($_SERVER['SSL_CLIENT_S_DN'], $dbLink)) {
             $sessionId = md5(mt_rand());
             $_SESSION["pageId"] = "searchform";
             $expTime = time()+SESSION_EXP_TIME;
             $expTimeDB = date('Y-m-d H:i:s', $expTime);
             // Update sessionId and exptime in database
             $query = "UPDATE ".AUTHTABLENAME." SET sessionid='".$sessionId."', 
             exptime='".$expTimeDB."' WHERE username='".$username."'";
             $result = perform_query($query, $dbLink);
           } else {
             $error .= " Sorry, $username does not have access to this service.";
             $_SESSION["error"] = "$error";
           }
   	} else {
	   	// Not using LDAP or WebBasic, revert to local db authentication
	   	if ($_POST["username"]) {
		   	$username = $_POST["username"];
		   	$password = $_POST["password"];
		   	// die("Info: $username, $password");
		   	$dbLink = db_connect_syslog(DBUSER, DBUSERPW);
		   	if ($username && $password && verify_login($username, $password, $dbLink)) {
			   	$sessionId = md5(mt_rand());
			   	$_SESSION["pageId"] = "searchform" ;
			   	// Calculate the expiration time
			   	$expTime = time()+SESSION_EXP_TIME;
			   	$expTimeDB = date('Y-m-d H:i:s', $expTime);
			   	// Update sessionId and exptime in database
			   	$query = "UPDATE ".AUTHTABLENAME." SET sessionid='".$sessionId."', 
					exptime='".$expTimeDB."' WHERE username='".$username."'";
			   	$result = perform_query($query, $dbLink);
		   	} else {
			   	$error .= " Invalid password for user $username";
			   	$_SESSION["error"] = "$error";
		   	}
	   	} else {
		   	$error .= " Missing POST variables";
			   	$_SESSION["error"] = "$error";
	   	}
   	}
   	if (trim($error)!="") {
	   	return $error;
	} else {
	   	$_SESSION["username"] = $username;
	   	return $username;
   	}
}
?>

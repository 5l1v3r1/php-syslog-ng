<?php
/**
* @version $Id: install2.php,v 1.0 2006/06/16 09:00:00 cdukes Exp $
* @package PHP-Syslog-NG
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* PHP-Syslog-NG is Free Software
*/

// Set flag that this is a parent file
define( "_VALID_MOS", 1 );

// Include common.php
require_once( 'common.php' );
require_once( '../includes/database.php' );
require_once( '../includes/version.php' );

$DBhostname = mosGetParam( $_POST, 'DBhostname', '' );
$DBuserName = mosGetParam( $_POST, 'DBuserName', '' );
$DBpassword = mosGetParam( $_POST, 'DBpassword', '' );
$DBverifypassword = mosGetParam( $_POST, 'DBverifypassword', '' );
$SLuserName = mosGetParam( $_POST, 'SLuserName', '' );
$SLUpassword = mosGetParam( $_POST, 'SLUpassword', '' );
$SLUverifypassword = mosGetParam( $_POST, 'SLUverifypassword', '' );
$SLAuserName = mosGetParam( $_POST, 'SLAuserName', '' );
$SLApassword = mosGetParam( $_POST, 'SLApassword', '' );
$SLAverifypassword = mosGetParam( $_POST, 'SLAverifypassword', '' );
$DBname  	= mosGetParam( $_POST, 'DBname', '' );
$DBPort     = mosGetParam( $_POST, 'DBPort', '' );
$DBPrefix  	= mosGetParam( $_POST, 'DBPrefix', '' );
$DBDel  	= intval( mosGetParam( $_POST, 'DBDel', 0 ) );
$DBBackup  	= intval( mosGetParam( $_POST, 'DBBackup', 0 ) );
$DBSample	= intval( mosGetParam( $_POST, 'DBSample', 0 ) );
$CEMDB	= intval( mosGetParam( $_POST, 'CEMDB', 0 ) );
$DBcreated	= intval( mosGetParam( $_POST, 'DBcreated', 0 ) );
$BUPrefix = 'old_';
$configArray['sitename'] = trim( mosGetParam( $_POST, 'sitename', '' ) );

$database = null;

$errors = array();
if (!$DBcreated){
	if (!$DBhostname || !$DBuserName || !$DBname) {
		db_err ("stepBack3","The database details provided are incorrect and/or empty.");
	}
	
	if ($DBpassword !== $DBverifypassword) {
		db_err ("stepBack3","The database passwords provided do not match.  Please try again.");
	}

	/*
	   if ($DBPort) {
	   $DBserver = "$DBhostname:$DBPort";
	   } else {
	   $DBserver = $DBhostname;
	   }	
	 */
	$DBserver = ($DBPort) ? "$DBhostname:$DBPort" : "$DBhostname";
	if (!($mysql_link = @mysql_connect( $DBserver, $DBuserName,
					$DBpassword ))) {
		db_err ("stepBack2","MySQL Error: " . mysql_error());
	}
	if($DBname == "") {
		db_err ("stepBack","The database name provided is empty.");
	}

	$configArray['DBhostname'] = $DBhostname;
	$configArray['DBuserName'] = $DBuserName;
	$configArray['DBpassword'] = $DBpassword;
	$configArray['DBname']	 = $DBname;
	$configArray['DBPort']	 = $DBPort;
	$configArray['DBPrefix']   = $DBPrefix;

	$sql = "CREATE DATABASE `$DBname`";
	$mysql_result = mysql_query( $sql );
	$test = mysql_errno();

	if ($test <> 0 && $test <> 1007) {
		db_err( "stepBack", "A database error occurred: " . (mysql_error()) );
	}

	// db is now new or existing, create the db object connector to do the serious work
    $DBserver = ($DBPort) ? "$DBhostname:$DBPort" : "$DBhostname";
	$database = new database( $DBserver, $DBuserName, $DBpassword, $DBname, $DBPrefix );

	// delete existing mos table if requested
	if ($DBDel) {
		$database->setQuery( "SHOW TABLES FROM `$DBname`" );
		$errors = array();
		if ($tables = $database->loadResultArray()) {
			foreach ($tables as $table) {
				if ($DBPrefix) {
					if (strpos( $table, $DBPrefix ) === 0) {
						if ($DBBackup) {
							$butable = str_replace( $DBPrefix, $BUPrefix, $table );
							$database->setQuery( "DROP TABLE IF EXISTS `$butable`" );
							$database->query();
							if ($database->getErrorNum()) {
								$errors[$database->getQuery()] = $database->getErrorMsg();
							}
							$database->setQuery( "RENAME TABLE `$table` TO `$butable`" );
							$database->query();
							if ($database->getErrorNum()) {
								$errors[$database->getQuery()] = $database->getErrorMsg();
							}
						}
					}
				} else {
					if ($DBBackup) {
						$butable = $BUPrefix .$table;
						$database->setQuery( "DROP TABLE IF EXISTS `$butable`" );
						$database->query();
						if ($database->getErrorNum()) {
							$errors[$database->getQuery()] = $database->getErrorMsg();
						}
						$database->setQuery( "RENAME TABLE `$table` TO `$butable`" );
						$database->query();
						if ($database->getErrorNum()) {
							$errors[$database->getQuery()] = $database->getErrorMsg();
						}
					}
				}
				$database->setQuery( "DROP TABLE IF EXISTS `$table`" );
				$database->query();
				if ($database->getErrorNum()) {
					$errors[$database->getQuery()] = $database->getErrorMsg();
				}
			}
		}
	}

	populate_db($DBname,$DBPrefix,'dbsetup.sql');
	if ($DBSample) {
		populate_db($DBname,$DBPrefix,'sample_data.sql');
	}
	if ($CEMDB) {
		// Removed for now - database is too large to import here
		// it must be done manually from the command line
		// populate_db($DBname,$DBPrefix,'cemdb.sql');
	}
	$DBcreated = 1;
}

function db_err($step, $alert) {
	global $DBhostname,$DBuserName,$DBpassword,$DBDel,$DBname,$DBPort;
	echo "<form name=\"$step\" method=\"post\" action=\"install1.php\">
	<input type=\"hidden\" name=\"DBhostname\" value=\"$DBhostname\">
	<input type=\"hidden\" name=\"DBuserName\" value=\"$DBuserName\">
	<input type=\"hidden\" name=\"DBpassword\" value=\"$DBpassword\">
	<input type=\"hidden\" name=\"DBDel\" value=\"$DBDel\">
	<input type=\"hidden\" name=\"DBname\" value=\"$DBname\">
	</form>\n";
	//echo "<script>alert(\"$alert\"); document.$step.submit();</script>";
	echo "<script>alert(\"$alert\"); window.history.go(-1);</script>";  //this wasn't working
	exit();
}

function populate_db($DBname, $DBPrefix, $sqlfile='dbsetup.sql') {
	global $errors;

	mysql_select_db($DBname);
	$mqr = @get_magic_quotes_runtime();
	@set_magic_quotes_runtime(0);
	$query = fread(fopen("sql/".$sqlfile, "r"), filesize("sql/".$sqlfile));
	@set_magic_quotes_runtime($mqr);
	$pieces  = split_sql($query);

	for ($i=0; $i<count($pieces); $i++) {
		$pieces[$i] = trim($pieces[$i]);
		if(!empty($pieces[$i]) && $pieces[$i] != "#") {
			$pieces[$i] = str_replace( "#__", $DBPrefix, $pieces[$i]);
			if (!$result = mysql_query ($pieces[$i])) {
				$errors[] = array ( mysql_error(), $pieces[$i] );
			}
		}
	}
}

function split_sql($sql) {
	$sql = trim($sql);
	$sql = ereg_replace("\n#[^\n]*\n", "\n", $sql);

	$buffer = array();
	$ret = array();
	$in_string = false;

	for($i=0; $i<strlen($sql)-1; $i++) {
		if($sql[$i] == ";" && !$in_string) {
			$ret[] = substr($sql, 0, $i);
			$sql = substr($sql, $i + 1);
			$i = 0;
		}

		if($in_string && ($sql[$i] == $in_string) && $buffer[1] != "\\") {
			$in_string = false;
		}
		elseif(!$in_string && ($sql[$i] == '"' || $sql[$i] == "'") && (!isset($buffer[0]) || $buffer[0] != "\\")) {
			$in_string = $sql[$i];
		}
		if(isset($buffer[1])) {
			$buffer[0] = $buffer[1];
		}
		$buffer[1] = $sql[$i];
	}

	if(!empty($sql)) {
		$ret[] = $sql;
	}
	return($ret);
}

$isErr = intval( count( $errors ) );

echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $_VERSION->PRODUCT; ?> - Web Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="shortcut icon" href="../../images/favicon.ico" />
<link rel="stylesheet" href="install.css" type="text/css" />
<script type="text/javascript">
<!--
function check() {
	// form validation check
	var formValid = true;
	var f = document.form;
	if ( f.sitename.value == '' ) {
		alert('Please enter a Site Name');
		f.sitename.focus();
		formValid = false
	}
	return formValid;
}
//-->
</script>
</head>
<body onload="document.form.sitename.focus();">
<div id="wrapper">
	<div id="header">
	  <div id="psng"><img src="header_install.png" alt="<?php echo $_VERSION->PRODUCT; ?> Installation" /></div>
	</div>
</div>

<div id="ctr" align="center">
	<form action="install3.php" method="post" name="form" id="form" onsubmit="return check();">
	<input type="hidden" name="DBhostname" value="<?php echo "$DBhostname"; ?>" />
	<input type="hidden" name="DBuserName" value="<?php echo "$DBuserName"; ?>" />
	<input type="hidden" name="DBpassword" value="<?php echo "$DBpassword"; ?>" />
	<input type="hidden" name="DBname" value="<?php echo "$DBname"; ?>" />
	<input type="hidden" name="DBPort" value="<?php echo "$DBPort"; ?>" />
	<input type="hidden" name="SLuserName" value="<?php echo "$SLuserName"; ?>" />
	<input type="hidden" name="SLUpassword" value="<?php echo "$SLUpassword"; ?>" />
	<input type="hidden" name="SLAuserName" value="<?php echo "$SLAuserName"; ?>" />
	<input type="hidden" name="SLApassword" value="<?php echo "$SLApassword"; ?>" />
	<input type="hidden" name="DBPrefix" value="<?php echo "$DBPrefix"; ?>" />
	<input type="hidden" name="DBcreated" value="<?php echo "$DBcreated"; ?>" />
	<div class="install">
		<div id="stepbar">
		  	<div class="step-off">pre-installation check</div>
	  		<div class="step-off">license</div>
		  	<div class="step-off">step 1</div>
		  	<div class="step-on">step 2</div>
	  		<div class="step-off">step 3</div>
		  	<div class="step-off">step 4</div>
		</div>
		<div id="right">
  			<div class="far-right">
<?php if (!$isErr) { ?>
  		  		<input class="button" type="submit" name="next" value="Next >>"/>
<?php } ?>
  			</div>
	  		<div id="step">step 2</div>
  			<div class="clr"></div>

  			<h1>Enter the name of your <?php echo $_VERSION->PRODUCT; ?> site:</h1>
			<div class="install-text">
<?php if ($isErr) { ?>
			Looks like there have been some errors with inserting data into your database!<br />
  			You cannot continue.
<?php } else { ?>
			SUCCESS!
			<br/>
			<br/>
  			Type in the name for your <?php echo $_VERSION->PRODUCT; ?> site. This
			name is used in email messages so make it something meaningful.
<?php } ?>
  		</div>
  		<div class="install-form">
  			<div class="form-block">
  				<table class="content2">
<?php
			if ($isErr) {
				echo '<tr><td colspan="2">';
				echo '<b></b>';
				echo "<br/><br />Error log:<br />\n";
				// abrupt failure
				echo '<textarea rows="10" cols="50">';
				foreach($errors as $error) {
					echo "SQL=$error[0]:\n- - - - - - - - - -\n$error[1]\n= = = = = = = = = =\n\n";
				}
				echo '</textarea>';
				echo "</td></tr>\n";
  			} else {
?>
  				<tr>
  					<td width="100">Site name</td>
  					<td align="center"><input class="inputbox" type="text" name="sitename" size="50" value="<?php echo "{$configArray['sitename']}"; ?>" /></td>
  				</tr>
  				<tr>
  					<td width="100">&nbsp;</td>
  					<td align="center" class="small">e.g. The Home of <?php echo $_VERSION->PRODUCT; ?></td>
  				</tr>
  				</table>
<?php
  			} // if
?>
  			</div>
  		</div>
  		<div class="clr"></div>
  		<div id="break"></div>
	</div>
	<div class="clr"></div>
	</form>
</div>
<div class="clr"></div>
</div>
</body>
</html>

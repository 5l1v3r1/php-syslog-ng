<?php
/**
* @version $Id: install1.php,v 1.0 2006/06/16 09:00:00 cdukes Exp $
* @package PHP-Syslog-NG
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* PHP-Syslog-NG is Free Software
*/

/** Include common.php */
include_once( 'common.php' );
include_once( '../includes/version.php' );

$DBhostname = mosGetParam( $_POST, 'DBhostname', 'localhost' );
$DBuserName = mosGetParam( $_POST, 'DBuserName', 'root' );
$DBpassword = mosGetParam( $_POST, 'DBpassword', '' );
$DBverifypassword = mosGetParam( $_POST, 'DBverifypassword', '' );
$SLuserName = mosGetParam( $_POST, 'SLuserName', 'sysloguser' );
$SLUpassword = mosGetParam( $_POST, 'SLUpassword', 'sysloguser' );
$SLUverifypassword = mosGetParam( $_POST, 'SLUverifypassword', 'sysloguser' );
$SLAuserName = mosGetParam( $_POST, 'SLAuserName', 'syslogadmin' );
$SLApassword = mosGetParam( $_POST, 'SLApassword', 'syslogadmin' );
$SLAverifypassword = mosGetParam( $_POST, 'SLAverifypassword', 'syslogadmin' );
$DBname  	= mosGetParam( $_POST, 'DBname', 'syslog' );
$DBPort     = mosGetParam( $_POST, 'DBPort', '3306' );
$DBPrefix  	= mosGetParam( $_POST, 'DBPrefix', '' );
$DBDel  	= intval( mosGetParam( $_POST, 'DBDel', 0 ) );
$DBBackup  	= intval( mosGetParam( $_POST, 'DBBackup', 0 ) );
$DBSample  	= intval( mosGetParam( $_POST, 'DBSample', 1 ) );
$CEMDB  	= intval( mosGetParam( $_POST, 'CEMDB', 1 ) );

echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $_VERSION->PRODUCT; ?> - Web Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="shortcut icon" href="../../images/favicon.ico" />
<link rel="stylesheet" href="install.css" type="text/css" />
<script  type="text/javascript">
<!--
function check()
{
	// form validation check
	var formValid=false;
	var f = document.form;
	if ( f.DBhostname.value == '' ) {
		alert('Please enter a Host name');
		f.DBhostname.focus();
		formValid=false;
	} else if ( f.DBuserName.value == '' ) {
		alert('Please enter a Database User Name');
		f.DBuserName.focus();
		formValid=false;	
	} else if ( f.DBname.value == '' ) {
		alert('Please enter a Name for your new Database');
		f.DBname.focus();
		formValid=false;
	} else if ( confirm('Are you sure these settings are correct? \n<?php echo $_VERSION->PRODUCT; ?> will now attempt to populate a Database with the settings you have supplied\n')) {
		formValid=true;
	} 
	return formValid;
}
//-->
</script>
</head>
<body onload="document.form.DBhostname.focus();">
<div id="wrapper">
	<div id="header">
		<div id="psng"><img src="header_install.png" alt="<?php echo $_VERSION->PRODUCT; ?> Installation" /></div>
	</div>
</div>
<div id="ctr" align="center">
	<form action="install2.php" method="post" name="form" id="form" onsubmit="return check();">
	<div class="install">
		<div id="stepbar">
			<div class="step-off">pre-installation check</div>
			<div class="step-off">license</div>
			<div class="step-on">step 1</div>
			<div class="step-off">step 2</div>
			<div class="step-off">step 3</div>
			<div class="step-off">step 4</div>
		</div>
		<div id="right">
			<div class="far-right">
				<input class="button" type="submit" name="next" value="Next >>"/>
  			</div>
	  		<div id="step">step 1</div>
  			<div class="clr"></div>
  			<h1>MySQL database configuration:</h1>
	  		<div class="install-text">
  	   			<p>Setting up <?php echo $_VERSION->PRODUCT; ?> to run on your server involves 4 simple steps...</p>
  	   			<p>Please enter the hostname of the server <?php echo $_VERSION->PRODUCT; ?> is to be installed on.</p>
				<p>Enter the MySQL username, password and database name you wish to use with <?php echo $_VERSION->PRODUCT; ?>.</p>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				<p>Enter the table name prefix (if any) to be used by this <?php echo $_VERSION->PRODUCT; ?> instance and select what
				   to do in case there are existing tables from former installations.</p>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				   <br>
				<p>Install the samples unless you want to start with a completely empty site.<br>
  			</div>
			<div class="install-form">
  	   			<div class="form-block">
  		 			<table class="content2">
  		  			<tr>
  						<td></td>
  						<td></td>
  						<td></td>
  					</tr>
  		  			<tr>
  						<td colspan="2">Host Name<br/><input class="inputbox" type="text" name="DBhostname" value="<?php echo "$DBhostname"; ?>" /></td>
			  			<td><em>This is usually 'localhost'</em></td>
  					</tr>
					<tr>
			  			<td colspan="2">MySQL User Name<br/><input class="inputbox" type="text" name="DBuserName" value="<?php echo "$DBuserName"; ?>" /></td>
			  			<td><em>Enter a valid username such as 'root' or a username given by your server administrator.</em></td>
  					</tr>
			  		<tr>
			  			<td colspan="2">MySQL Password<br/><input class="inputbox" type="password" name="DBpassword" value="<?php echo "$DBpassword"; ?>" /></td>
			  			<td><em>For site security using a password for the mysql account is mandatory</em></td>
					</tr>
					<tr>
			  			<td colspan="2">Verify MySQL Password<br/><input class="inputbox" type="password" name="DBverifypassword" value="<?php echo "$DBverifypassword"; ?>" /></td>
			  			<td><em>Retype password for verification</em></td>
					</tr>
  		  			<tr>
  						<td colspan="2">MySQL Database Name<br/><input class="inputbox" type="text" name="DBname" value="<?php echo "$DBname"; ?>" /></td>
			  			<td><em>Some hosts only allow a certain DB name per site. If this is the case, set that name here and use the Prefix option below. </em></td>
  					</tr>
  		  			<tr>
  						<td colspan="2">MySQL Port<br/><input class="inputbox" type="text" name="DBPort" value="<?php echo "$DBPort"; ?>" /></td>
			  			<td><em>Specify the port which MySQL is running on (Default is 3306)</em></td>
  					</tr>
  		  			<tr>
  						<td colspan="2">MySQL Table Prefix<br/><input class="inputbox" type="text" name="DBPrefix" value="<?php echo "$DBPrefix"; ?>" /></td>
			  			<td><em>Do NOT use 'old_' since this is used for backup tables</em></td>
  					</tr>
					<tr>
			  			<td colspan="2">Syslog User Name<br/><input class="inputbox" type="text" name="SLuserName" value="<?php echo "$SLuserName"; ?>" /></td>
			  			<td><em>This user is used to access the SQL (read) data on the backend, there's probably no need to change it from the default (sysloguser) </em></td>
  					</tr>
			  		<tr>
			  			<td colspan="2">Syslog User Password<br/><input class="inputbox" type="password" name="SLUpassword" value="<?php echo "$SLUpassword"; ?>" /></td>
			  			<td><em>For site security using a password for the mysql account is mandatory</em></td>
					</tr>
					<tr>
			  			<td colspan="2">Verify Password<br/><input class="inputbox" type="password" name="SLUverifypassword" value="<?php echo "$SLUverifypassword"; ?>" /></td>
			  			<td><em>Retype password for verification</em></td>
					</tr>
					<tr>
			  			<td colspan="2">Syslog Admin Name<br/><input class="inputbox" type="text" name="SLAuserName" value="<?php echo "$SLAuserName"; ?>" /></td>
			  			<td><em>This user is used to access the SQL (write) data on the backend, there's probably no need to change it from the default (syslogadmin) </em></td>
  					</tr>
			  		<tr>
			  			<td colspan="2">Syslog Admin Password<br/><input class="inputbox" type="password" name="SLApassword" value="<?php echo "$SLApassword"; ?>" /></td>
			  			<td><em>For site security using a password for the mysql account is mandatory</em></td>
					</tr>
					<tr>
			  			<td colspan="2">Verify Password<br/><input class="inputbox" type="password" name="SLAverifypassword" value="<?php echo "$SLAverifypassword"; ?>" /></td>
			  			<td><em>Retype password for verification</em></td>
					</tr>
  		  			<tr>
			  			<td><input type="checkbox" name="DBDel" id="DBDel" value="1" <?php if ($DBDel) echo 'checked="checked"'; ?> /></td>
						<td><label for="DBDel">Drop Existing Tables</label></td>
  						<td>&nbsp;</td>
			  		</tr>
  		  			<tr>
			  			<td><input type="checkbox" name="DBBackup" id="DBBackup" value="1" <?php if ($DBBackup) echo 'checked="checked"'; ?> /></td>
						<td><label for="DBBackup">Backup Old Tables</label></td>
  						<td><em>Any existing backup tables from former installations will be replaced</em></td>
			  		</tr>
					<!--
  		  			<tr>
			  			<td><input type="checkbox" name="DBSample" id="DBSample" value="1" <?php if ($DBSample) echo 'checked="checked"'; ?> /></td>
						<td><label for="DBSample">Install Sample Data</label></td>
			  			<td><em>Checking this option will install sample data</em></td>
			  		</tr>
					-->
  		  			<tr>
			  			<td><input type="checkbox" name="CEMDB" id="CEMDB" value="1" <?php if ($CEMDB) echo 'checked="checked"'; ?> /></td>
						<td><label for="CEMDB">Install CEMDB Data</label></td>
			  			<td><em>Checking this option will install data for the Cisco Error Message Database</em></td>
			  		</tr>
		  		 	</table>
  				</div>
			</div>
		</div>
		<div class="clr"></div>
	</div>
	</form>
</div>
<div class="clr"></div>
</body>
</html>

<?php
/**
* @version $Id: install4.php,v 1.0 2006/06/16 09:00:00 cdukes Exp $
* @package PHP-Syslog-NG
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* PHP-Syslog-NG is Free Software
*/

// Set flag that this is a parent file
define( "_VALID_MOS", 1 );

// Include common.php
$basePath = dirname( __FILE__ );
require_once($basePath . '/common.php' );
require_once($basePath . '/../includes/version.php' );

$DBhostname = mosGetParam( $_POST, 'DBhostname', '' );
$DBuserName = mosGetParam( $_POST, 'DBuserName', '' );
$DBpassword = mosGetParam( $_POST, 'DBpassword', '' );
$SLuserName = mosGetParam( $_POST, 'SLuserName', '' );
$SLUpassword = mosGetParam( $_POST, 'SLUpassword', '' );
$SLAuserName = mosGetParam( $_POST, 'SLAuserName', '' );
$SLApassword = mosGetParam( $_POST, 'SLApassword', '' );
$DBname  	= mosGetParam( $_POST, 'DBname', '' );
$DBPort  	= mosGetParam( $_POST, 'DBPort', '' );
$DBPrefix  	= mosGetParam( $_POST, 'DBPrefix', '' );
$sitename  	= mosGetParam( $_POST, 'sitename', '' );
$SITEURL  	= mosGetParam( $_POST, 'SITEURL', '' );
$adminEmail = mosGetParam( $_POST, 'adminEmail', '');
$siteUrl  	= mosGetParam( $_POST, 'siteUrl', '' );
$absolutePath = mosGetParam( $_POST, 'absolutePath', '' );
$adminPassword = mosGetParam( $_POST, 'adminPassword', '');
$CEMDB	= intval( mosGetParam( $_POST, 'CEMDB', 1 ) );

$filePerms = '';
if (mosGetParam($_POST,'filePermsMode',0))
	$filePerms = '0'.
		(mosGetParam($_POST,'filePermsUserRead',0) * 4 +
		 mosGetParam($_POST,'filePermsUserWrite',0) * 2 +
		 mosGetParam($_POST,'filePermsUserExecute',0)).
		(mosGetParam($_POST,'filePermsGroupRead',0) * 4 +
		 mosGetParam($_POST,'filePermsGroupWrite',0) * 2 +
		 mosGetParam($_POST,'filePermsGroupExecute',0)).
		(mosGetParam($_POST,'filePermsWorldRead',0) * 4 +
		 mosGetParam($_POST,'filePermsWorldWrite',0) * 2 +
		 mosGetParam($_POST,'filePermsWorldExecute',0));

$dirPerms = '';
if (mosGetParam($_POST,'dirPermsMode',0))
	$dirPerms = '0'.
		(mosGetParam($_POST,'dirPermsUserRead',0) * 4 +
		 mosGetParam($_POST,'dirPermsUserWrite',0) * 2 +
		 mosGetParam($_POST,'dirPermsUserSearch',0)).
		(mosGetParam($_POST,'dirPermsGroupRead',0) * 4 +
		 mosGetParam($_POST,'dirPermsGroupWrite',0) * 2 +
		 mosGetParam($_POST,'dirPermsGroupSearch',0)).
		(mosGetParam($_POST,'dirPermsWorldRead',0) * 4 +
		 mosGetParam($_POST,'dirPermsWorldWrite',0) * 2 +
		 mosGetParam($_POST,'dirPermsWorldSearch',0));

if ((trim($adminEmail== "")) || (preg_match("/[\w\.\-]+@\w+[\w\.\-]*?\.\w{1,4}/", $adminEmail )==false)) {
	echo "<form name=\"stepBack\" method=\"post\" action=\"install3.php\">
		<input type=\"hidden\" name=\"DBhostname\" value=\"$DBhostname\" />
		<input type=\"hidden\" name=\"DBuserName\" value=\"$DBuserName\" />
		<input type=\"hidden\" name=\"DBpassword\" value=\"$DBpassword\" />
		<input type=\"hidden\" name=\"DBname\" value=\"$DBname\" />
		<input type=\"hidden\" name=\"DBPort\" value=\"$DBPort\" />
		<input type=\"hidden\" name=\"SLuserName\" value=\"$SLuserName\">
		<input type=\"hidden\" name=\"SLUpassword\" value=\"$SLUpassword\">
		<input type=\"hidden\" name=\"SLAuserName\" value=\"$SLAuserName\">
		<input type=\"hidden\" name=\"SLApassword\" value=\"$SLApassword\">
		<input type=\"hidden\" name=\"DBPrefix\" value=\"$DBPrefix\" />
		<input type=\"hidden\" name=\"DBcreated\" value=\"1\" />
		<input type=\"hidden\" name=\"sitename\" value=\"$sitename\" />
		<input type=\"hidden\" name=\"SITEURL\" value=\"$SITEURL\" />
		<input type=\"hidden\" name=\"adminEmail\" value=\"$adminEmail\" />
		<input type=\"hidden\" name=\"siteUrl\" value=\"$siteUrl\" />
		<input type=\"hidden\" name=\"absolutePath\" value=\"$absolutePath\" />
		<input type=\"hidden\" name=\"filePerms\" value=\"$filePerms\" />
		<input type=\"hidden\" name=\"dirPerms\" value=\"$dirPerms\" />
		</form>";
	echo "<script>alert('You must provide a valid admin email address.'); document.stepBack.submit(); </script>";
	return;
}

if($DBhostname && $DBuserName && $DBname) {
	$configArray['DBhostname'] = $DBhostname;
	$configArray['DBuserName'] = $DBuserName;
	$configArray['DBpassword'] = $DBpassword;
	$configArray['DBname']	 = $DBname;
	$configArray['DBPort']	 = $DBPort;
	$configArray['DBPrefix']   = $DBPrefix;
} else {
	echo "<form name=\"stepBack\" method=\"post\" action=\"install3.php\">
		<input type=\"hidden\" name=\"DBhostname\" value=\"$DBhostname\" />
		<input type=\"hidden\" name=\"DBuserName\" value=\"$DBuserName\" />
		<input type=\"hidden\" name=\"DBpassword\" value=\"$DBpassword\" />
		<input type=\"hidden\" name=\"DBname\" value=\"$DBname\" />
		<input type=\"hidden\" name=\"DBPort\" value=\"$DBPort\" />
		<input type=\"hidden\" name=\"DBPrefix\" value=\"$DBPrefix\" />
		<input type=\"hidden\" name=\"DBcreated\" value=\"1\" />
		<input type=\"hidden\" name=\"sitename\" value=\"$sitename\" />
		<input type=\"hidden\" name=\"SITEURL\" value=\"$SITEURL\" />
		<input type=\"hidden\" name=\"adminEmail\" value=\"$adminEmail\" />
		<input type=\"hidden\" name=\"siteUrl\" value=\"$siteUrl\" />
		<input type=\"hidden\" name=\"absolutePath\" value=\"$absolutePath\" />
		<input type=\"hidden\" name=\"filePerms\" value=\"$filePerms\" />
		<input type=\"hidden\" name=\"dirPerms\" value=\"$dirPerms\" />
		</form>";

	echo "<script>alert('The database details provided are incorrect and/or empty'); document.stepBack.submit(); </script>";
	return;
}

if ($sitename) {
	if (!get_magic_quotes_gpc()) {
		$configArray['sitename'] = addslashes($sitename);
	} else {
		$configArray['sitename'] = $sitename;
	}
} else {
	echo "<form name=\"stepBack\" method=\"post\" action=\"install3.php\">
		<input type=\"hidden\" name=\"DBhostname\" value=\"$DBhostname\" />
		<input type=\"hidden\" name=\"DBuserName\" value=\"$DBuserName\" />
		<input type=\"hidden\" name=\"DBpassword\" value=\"$DBpassword\" />
		<input type=\"hidden\" name=\"DBname\" value=\"$DBname\" />
		<input type=\"hidden\" name=\"DBPort\" value=\"$DBPort\" />
		<input type=\"hidden\" name=\"DBPrefix\" value=\"$DBPrefix\" />
		<input type=\"hidden\" name=\"DBcreated\" value=\"1\" />
		<input type=\"hidden\" name=\"sitename\" value=\"$sitename\" />
		<input type=\"hidden\" name=\"SITEURL\" value=\"$SITEURL\" />
		<input type=\"hidden\" name=\"adminEmail\" value=\"$adminEmail\" />
		<input type=\"hidden\" name=\"siteUrl\" value=\"$siteUrl\" />
		<input type=\"hidden\" name=\"absolutePath\" value=\"$absolutePath\" />
		<input type=\"hidden\" name=\"filePerms\" value=\"$filePerms\" />
		<input type=\"hidden\" name=\"dirPerms\" value=\"$dirPerms\" />
		</form>";

	echo "<script>alert('The sitename has not been provided'); document.stepBack2.submit();</script>";
	return;
}

if (file_exists( '../config/config.php' )) {
	$canWrite = is_writable( '../config/config.php' );
} else {
	$canWrite = is_writable( '..' );
}
 
if ($siteUrl) {
	$configArray['siteUrl']=$siteUrl;
	// Fix for Windows
	$absolutePath= str_replace("\\","/", $absolutePath);
	$absolutePath= str_replace("//","/", $absolutePath);
	$configArray['absolutePath']=$absolutePath;
	$configArray['filePerms']=$filePerms;
	$configArray['dirPerms']=$dirPerms;

	$config = "<?php\n";
	$config .= "define('PAGETITLE', '$_VERSION->PRODUCT' );\n";
	$config .= "define('VERSION', '$_VERSION->RELEASE.".$_VERSION->DEV_LEVEL."');\n";
	$config .= "define('COUNT_ROWS', TRUE);\n";
	$config .= "define('DEFAULTLOGTABLE', '{$DBPrefix}logs');\n";
	$config .= "define('MERGELOGTABLE', '{$DBPrefix}all_logs');\n";
	$config .= "define('USETABLE', DEFAULTLOGTABLE); // This tells the main page to calculate hostcount based on \"all_logs\" or \"logs\"\n";
	$config .= "define('LOGROTATERETENTION', 30);\n";
	$config .= "define('DBUSER', '$SLuserName');\n";
	$config .= "define('DBUSERPW', '$SLUpassword');\n";
	$config .= "define('DBADMIN', '$SLAuserName');\n";
	$config .= "define('DBADMINPW', '$SLApassword');\n";
	$config .= "define('DBNAME', '$DBname');\n";
	$config .= "define('DBHOST', '$DBhostname');\n";
	$config .= "define('DBPORT', '$DBPort');\n";
	$config .= "define('REQUIRE_AUTH', TRUE);\n";
	$config .= "define('AUTHTABLENAME', '{$DBPrefix}users');\n";
	$config .= "define('RENEW_SESSION_ON_EACH_PAGE', TRUE);\n";
	$config .= "define('SESSION_EXP_TIME', '3600');\n";
	$config .= "define('TAIL_REFRESH_SECONDS', '25');\n";
 	$config .= "define('USE_ACL', TRUE);\n";
 	$config .= "define('USER_ACCESS_TABLE', '{$DBPrefix}user_access');\n";
 	$config .= "define('ACTION_TABLE', '{$DBPrefix}actions');\n";
	$config .= "define('USE_CACHE', TRUE);\n";
	$config .= "define('CACHETABLENAME', '{$DBPrefix}search_cache');\n";
 	$config .= "define('SITEADMIN', 'admin');\n";
 	$config .= "define('SITENAME', '$sitename');\n";
 	$config .= "define('ADMINEMAIL', '$adminEmail');\n";
	if ($CEMDB) {
		$config .= "define('CEMDB', 'ON');\n";
		$config .= "define('CISCO_TAG_PARSE', TRUE);\n";
	} else {
		$config .= "define('CEMDB', 'OFF');\n";
		$config .= "define('CISCO_TAG_PARSE', FALSE);\n";
	}
	$config .= "define('CISCO_ERROR_TABLE', 'cemdb');\n";
	$config .= "define('DEBUG', FALSE);\n";
	$config .= "define('SITEURL', '$SITEURL');\n";
   	$config .= '$regExpArray = array(
		   	// "username"=>"(^\w{4,}\$)",
		   	// Cdukes - 05/10/08: Modified username to allow email address as username
		   	// This is an enhancement change for
		   	// http://code.google.com/p/php-syslog-ng/issues/detail?id=62
		   	"username" => "(^[A-Za-z_.@]{4,}\$)",
			"password"=>"(^.{4,}\$)",
			"pageId"=>"(^\w+$)",
			"sessionId"=>"(^\w{32}\$)",
			// "date"=>"/^yesterday$|^today$|^now$|^(\d){4}-([01]*\d)-([0123]*\d)$/i",
			"date"=>"/^yesterday$|^today$|^now$|^([0123]*\d)-([012]*\d)-(\d){4}$/i",
			"time"=>"/^now$|^([012]*\d):([012345]*\d):([012345]*\d)$/i",
			"limit"=>"(^\d+$)",
			"topx"=>"(^\d+$)",
			// BPK added program to orderby filter
			"orderby"=>"/^id$|^seq$|^host$|^program$|^facility$|^priority$|^datetime$|^msg$|^fo$|^lo$|^counter$/i",
			"order"=>"/^asc$|^desc$/i",
			"offset"=>"(^\d+$)",
			"collapse"=>"/^1$/",
			"table"=>"(^\w+$)",
			"excludeX"=>"(^[01]$)",
			/* BEGIN: changes by BPK to allow for regexp matching, lists of hosts, and programs
			"host"=>"(^[\w-.]+$)",
			*/
			"regexpX"=>"(^[01]$)",
			"host"=>"(^([\w_.%-]+[,;]\s*)*[\w_.%-]+$)",
			"program"=>"(^([\w/_.%-]+[,;]\s*)*[\w/_.%-]+$)",
			"hostRegExp"=>"(^\S+$)",
			"programRegExp"=>"(^\S+$)",
			/* END: changes by BPK to allow for regexp matching, lists of hosts, and programs */
			"facility"=>"(^\w+$)",
		   	"priority"=>"/^debug$|^info$|^notice$|^warning$|^err$|^crit$|^alert$|^emerg$/i",
		   	// Cdukes - 05/10/08: Below is an enhancement addition for SqueezeDB duplicate searching
		   	"dupop"=>"(^lt|gt|eq$)",
		   	"dupcount"=>"(^\d+$)",
			);';
	$config .= "\n//------------------------------------------------------------------------\n";
	$config .= "// CDUKES - BEGIN jpgraph Addon\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "\n";
	$config .= "// Enable Graphing\n";
	$config .= "define('JPG_GRAPHS', 'ON');\n";
	$config .= "// Make sure this directory exists and has write permission\n";
	$config .= "define('IMG_CACHE_DIR', 'jpcache/');\n";
	$config .= "\n";
	$config .= "// Enable Daily Graph in dropdown on main page\n";
	$config .= "define('JPG_DAILY', 'ON');\n";
	$config .= "// Enable Weekly Graph in dropdown on main page\n";
	$config .= "define('JPG_WEEKLY', 'ON');\n";
	$config .= "// Enable Monthly Graph in dropdown on main page\n";
	$config .= "define('JPG_MONTHLY', 'ON');\n";
	$config .= "// Enable Overall Statistics Graph on main page (This will slow down main page rendering)\n";
	$config .= "define('JPG_MAIN', 'OFF'); // Not implemented yet\n";
	$config .= "\n";
	$config .= "\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "// CDUKES - END jpgraph Addon\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "// CDUKES - Begin LDAP Addon\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "define('LOGIN_URL', SITEURL . \"login.php\");\n";
	$config .= "define('LOGOUT_URL', SITEURL . \"logout.php\");\n";
	$config .= "define('INDEX_URL', SITEURL . \"index.php\");\n";
	$config .= "define('LDAP_ENABLE', \"NO\");\n";
	$config .= "define('LDAP_SRV', \"ldap.company.com\");\n";
	$config .= "define('LDAP_BASE_DN', \"ou=active, ou=employees, ou=people, o=company.com\");\n";
	$config .= "// variable to search for user (container name) - in my case, this is \"uid\", but normally it is \"cn\"\n";
	$config .= "define('LDAP_CN', \"uid\");\n";
	$config .= "// if using MS Active Directory, put it to \"sAMAccountName\"\n";
	$config .= "// define('LDAP_CN', \"sAMAccountName\");\n";
	$config .= "// Set to Yes if using MS Active Directory for Authentication\n";
	$config .= "define('LDAP_MSAD', \"NO\");\n";
	$config .= "// Required when if LDAP_MSAD set to YES and using MS Active Directory, ex. mydomain.com\n";
	$config .= "define('LDAP_DOMAIN', \"mydomain.com\");\n";
	$config .= "\n";
	$config .= "// privilege levels for editing records \n";
	$config .= "// (not implemented yet - this will be used to define RW and RO groups from LDAP)\n";
	$config .= "// use privilege level authentication for record editing?\n";
	$config .= "define  ('LDAP_USEPRIV', 'OFF');\n";
	$config .= "// if USEPRIV is enabled, what LDAP group name is the \"privileged\" group?\n";
	$config .= "define  ('LDAP_RW_GROUP', 'admin');\n";
	$config .= "define  ('LDAP_RO_GROUP', 'users');\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "// CDUKES - END LDAP Addon\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "// cdukes - I added this just in case someone felt generous\n";
	$config .= "// If you don't want it on the menu bar, just disable it here :-)\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "define  ('PAYPAL_ENABLE', 'YES');\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "// CDUKES - Just a simple addition for my demo site at http://php-syslog-ng.gdd.net\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "define  ('DEMO', FALSE);\n";
	$config .= "// Cdukes - 05/10/08: WEBBASIC_ENABLE\n";
	$config .= "// This is an enhancement change for\n";
	$config .= "// http://code.google.com/p/php-syslog-ng/issues/detail?id=62\n";
	$config .= "define('WEBBASIC_ENABLE', FALSE);\n";
	$config .= "// Cdukes - 05/10/08: SQZ_ENABLED\n";
	$config .= "// This is an enhancement change for SqueezeDB functionality\n";
	$config .= "// http://code.google.com/p/php-syslog-ng/issues/detail?id=47\n";
	$config .= "define('SQZ_ENABLED', FALSE);\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "// CDUKES - Added front page bar graph for daily log count\n";
	$config .= "//------------------------------------------------------------------------\n";
	$config .= "define('GRAPH_LPD', TRUE);\n";
	$config .= "define('LPD_CACHE', TRUE);\n";
	$config .= "\n?>";

	if ($canWrite && ($fp = fopen("../config/config.php", "w"))) {
		fputs( $fp, $config, strlen( $config ) );
		fclose( $fp );
	} else {
		$canWrite = false;
	} // if

	$cryptpass=md5($adminPassword);

    $DBserver = ($DBPort) ? "$DBhostname:$DBPort" : "$DBhostname";
	mysql_connect($DBserver, $DBuserName, $DBpassword);
	mysql_select_db($DBname);

	// create the admin user
	// $installdate = date("Y-m-d H:i:s");
	$query = "INSERT INTO `{$DBPrefix}users` (username, pwhash) VALUES('admin', '$cryptpass');";
	mysql_query( $query );

	// create the mysql users
	mysql_select_db("mysql");

	$query = "DELETE FROM user where User='$SLuserName';";
		mysql_query( $query );
		$query = "INSERT INTO user (Host, User, Password) VALUES ('$DBhostname','$SLuserName', password('$SLUpassword'));";
		mysql_query( $query );
	$query = "INSERT INTO db (Host, Db, User) VALUES ('$DBhostname','$DBname','$SLuserName');";
		mysql_query( $query );

		/* CDUKES - It doesn't appear that feeder is used anymore? */
	$query = "DELETE FROM user where User='syslogfeeder';";
		mysql_query( $query );
		/*
	$query = "INSERT INTO user (Host, User, Password) VALUES ('$DBhostname','syslogfeeder', password('syslogfeeder'));";
		mysql_query( $query );
	$query = "INSERT INTO db (Host, Db, User) VALUES ('$DBhostname','$DBname','syslogfeeder');";
		mysql_query( $query );
		*/

	$query = "DELETE FROM user where User='$SLAuserName';";
		mysql_query( $query );
	$query = "INSERT INTO user (Host, User, Password) VALUES ('$DBhostname','$SLAuserName',password('$SLApassword'));";
		mysql_query( $query );
	// cdukes - added below since, for some reason, Mysql 5.0 doesn't grant when using GRANT ALL
	$query = "UPDATE user SET Drop_priv='Y',Alter_priv='Y' where User='$SLAuserName';";
		mysql_query( $query );
	$query = "INSERT INTO db (Host, Db, User) VALUES ('$DBhostname','$DBname','$SLAuserName');";
		mysql_query( $query );
	$query = "COMMIT;";
		mysql_query( $query );
	$query = "FLUSH PRIVILEGES;";
		mysql_query( $query );

# grant rights to user $SLAuserName for backup purpose
	$query = "GRANT USAGE ON *.* TO $SLAuserName@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT ALL ON $DBname.* TO $SLAuserName@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT RELOAD ON *.* TO $SLAuserName@$DBhostname;";
		mysql_query( $query );
		// cdukes - added below since, for some reason, Mysql 5.0 doesn't grant when using GRANT ALL
	$query = "GRANT DROP ON $DBname.* TO $SLAuserName@$DBhostname;";
		mysql_query( $query );
		// cdukes - added below since, for some reason, Mysql 5.0 doesn't grant when using GRANT ALL
	$query = "GRANT ALTER ON $DBname.* TO $SLAuserName@$DBhostname;";
		mysql_query( $query );

	$query = "REVOKE ALL PRIVILEGES ON $DBname.* FROM $SLuserName@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT USAGE ON *.* TO $SLuserName@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT SELECT ON $DBname.* TO $SLuserName@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT UPDATE ON $DBname.{$DBPrefix}users TO $SLuserName@$DBhostname;";
		mysql_query( $query );

		/* CDUKES - It doesn't appear that feeder is used anymore */
	$query = "REVOKE ALL PRIVILEGES ON $DBname.* FROM syslogfeeder@$DBhostname;";
		mysql_query( $query );
		/*
	$query = "GRANT USAGE ON *.* TO syslogfeeder@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT INSERT ON $DBname.* TO syslogfeeder@$DBhostname;";
		mysql_query( $query );
		*/

	$query = "GRANT ALL ON $DBname.{$DBPrefix}search_cache TO $SLuserName@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT SELECT ON $DBname.{$DBPrefix}user_access TO $SLuserName@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT ALL ON $DBname.{$DBPrefix}user_access TO $SLAuserName@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT SELECT ON $DBname.{$DBPrefix}actions TO $SLuserName@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT ALL ON $DBname.{$DBPrefix}actions TO $SLAuserName@$DBhostname;";
		mysql_query( $query );
	$query = "GRANT ALL ON $DBname.{$DBPrefix}cemdb TO $SLAuserName@$DBhostname;";
		mysql_query( $query );

	$query = "COMMIT;";
		mysql_query( $query );
	$query = "FLUSH PRIVILEGES;";
		mysql_query( $query );


	// chmod files and directories if desired
   	$chmod_report = "Directory and file permissions left unchanged.";
	if ($filePerms != '' || $dirPerms != '') {
		$mosrootfiles = array(
			'config',
			'includes',
			'scripts',
			'css',
			'CHANGELOG',
			'htaccess.txt',
			'index.php',
			'README',
			'LICENSE',
			'robots.txt'
		);
		$filemode = NULL;
		if ($filePerms != '') $filemode = octdec($filePerms);
		$dirmode = NULL;
		if ($dirPerms != '') $dirmode = octdec($dirPerms);
		$chmodOk = TRUE;
		foreach ($mosrootfiles as $file)
			if (!mosChmodRecursive($absolutePath.'/'.$file, $filemode, $dirmode))
				$chmodOk = FALSE;
		if ($chmodOk)
			$chmod_report = 'File and directory permissions successfully changed.';
		else
			$chmod_report = 'File and directory permissions could not be changed.<br/>'.
							'Please CHMOD ' .$_VERSION->PRODUCT .'files and directories manually.';
	} // if chmod wanted
} else {
?>
	<form action="install3.php" method="post" name="stepBack3" id="stepBack3">
	  <input type="hidden" name="DBhostname" value="<?php echo $DBhostname;?>" />
	  <input type="hidden" name="DBusername" value="<?php echo $DBuserName;?>" />
	  <input type="hidden" name="DBpassword" value="<?php echo $DBpassword;?>" />
	  <input type="hidden" name="DBname" value="<?php echo $DBname;?>" />
	  <input type="hidden" name="DBPrefix" value="<?php echo $DBPrefix;?>" />
	  <input type="hidden" name="DBcreated" value="1" />
	  <input type="hidden" name="sitename" value="<?php echo $sitename;?>" />
	  <input type="hidden" name="SITEURL" value="<?php echo $SITEURL;?>" />
	  <input type="hidden" name="adminEmail" value="<?php echo $adminEmail?>" />
	  <input type="hidden" name="siteUrl" value="<?php echo $siteUrl?>" />
	  <input type="hidden" name="absolutePath" value="<?php echo $absolutePath?>" />
	  <input type="hidden" name="filePerms" value="<?php echo $filePerms?>" />
	  <input type="hidden" name="dirPerms" value="<?php echo $dirPerms?>" />
	</form>
	<script>alert('The site url has not been provided'); document.stepBack3.submit();</script>
<?php
}
echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">";
?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $_VERSION->PRODUCT; ?> - Web Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="stylesheet" href="install.css" type="text/css" />
</head>
<body>
<div id="wrapper">
	<div id="header">
		<div id="psng"><img src="header_install.png" alt="<?php echo $_VERSION->PRODUCT; ?> Installation" /></div>
	</div>
</div>
<div id="ctr" align="center">
	<?php if ($CEMDB) { ?>
	<form action="cemdb/bdimport.php" name="form" id="form">
	<div class="install">
		<div id="stepbar">
			<div class="step-off">pre-installation check</div>
			<div class="step-off">license</div>
			<div class="step-off">step 1</div>
			<div class="step-off">step 2</div>
			<div class="step-off">step 3</div>
			<div class="step-on">step 4</div>
		</div>
		<div id="right">
			<div id="step">step 4</div>
			<div class="far-right">
	<?php 
	echo "
		<input type=\"hidden\" name=\"DBhostname\" value=\"$DBhostname\" />
		<input type=\"hidden\" name=\"DBuserName\" value=\"$DBuserName\" />
		<input type=\"hidden\" name=\"DBpassword\" value=\"$DBpassword\" />
		<input type=\"hidden\" name=\"DBname\" value=\"$DBname\" />
		</form>";
			?>
				<input class="button" type="submit" name="cemdb" value="Install CEMDB">
			</div>
			<div class="clr"></div>
			<h1>Final Step: CEMDB Install</h1>
			<?php } else { ?>
	<form action="dummy" name="form" id="form">
	<div class="install">
		<div id="stepbar">
			<div class="step-off">pre-installation check</div>
			<div class="step-off">license</div>
			<div class="step-off">step 1</div>
			<div class="step-off">step 2</div>
			<div class="step-off">step 3</div>
			<div class="step-on">step 4</div>
		</div>
		<div id="right">
			<div id="step">step 4</div>
			<div class="far-right">
			<div class="far-right">
				<input class="button" type="button" name="runSite" value="View Site"
<?php
				if ($siteUrl) {
					echo "onClick='window.location.href=\"$siteUrl"."/index.php\" '";
				} else {
					echo "onClick='window.location.href=\"{$configArray['siteURL']}"."/index.php\" '";
				}
?>/>
			</div>
			<div class="clr"></div>
			<h1>Congratulations! <?php echo $_VERSION->PRODUCT; ?> is installed</h1>

			<?php } ?>
	<?php if ($CEMDB) { ?>
			<div class="install-text">
				<p>Next: Be sure to click the "Install CEMDB" button to start CEMDB import!</p>
			</div>
			<?php } else { ?>
			<div class="install-text">
				<p>Click the "View Site" button to start <?php echo $_VERSION->PRODUCT; ?></p>
			</div>
			<?php } ?>
			<div class="install-form">
				<div class="form-block">
					<table width="100%">
						<tr><td class="error" align="center">PLEASE REMEMBER TO COMPLETELY<br/>REMOVE THE INSTALLATION DIRECTORY</td></tr>
						<tr><td align="center"><h5>Administration Login Details</h5></td></tr>
						<tr><td align="center" class="notice"><b>Username : admin</b></td></tr>
						<tr><td align="center" class="notice"><b>Password : <?php echo $adminPassword; ?></b></td></tr>
						<tr><td>&nbsp;</td></tr>
						<tr><td align="right">&nbsp;</td></tr>
<?php					   if (!$canWrite) { ?>
						<tr>
							<td class="small">
								Your configuration file or directory is not writeable,
								or there was a problem creating the configuration file. You'll have to
								upload the following code by hand. Click in the textarea to highlight
								all of the code.
							</td>
						</tr>
						<tr>
							<td align="center">
								<textarea rows="5" cols="60" name="configcode" onclick="javascript:this.form.configcode.focus();this.form.configcode.select();" ><?php echo htmlspecialchars( $config );?></textarea>
							</td>
						</tr>
<?php					   } ?>
						<tr><td class="small"><?php /*echo $chmod_report*/; ?></td></tr>
					</table>
				</div>
			</div>
			<div id="break"></div>
		</div>
		<div class="clr"></div>
	</div>
	</form>
</div>
<div class="clr"></div>
</html>

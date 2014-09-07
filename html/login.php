<?php

/*
 *
 * Developed by Clayton Dukes <cdukes@cdukes.com>
 * Licensed under terms of GNU General Public License.
 * All rights reserved.
 *
 * Changelog:
 * 2006-12-11 - created
 *
 */

session_start();
include_once ("config/config.php");
include_once ("includes/common_funcs.php");

// If Access system is not used, then skip all this - i.e. Open system
if(!defined('USE_ACL') || !USE_ACL || !defined('REQUIRE_AUTH') || !REQUIRE_AUTH) {
   	$_SESSION["member_id"] = "NoACL_localuser";
	$_SESSION["username"] = "NoACL_localuser";
   	$sessionId = md5(mt_rand());
   	$_SESSION["pageId"] = "searchform" ;
   	Header("Location: " .INDEX_URL); // Redirect authenticated member
   	exit();
}

if ($_POST) {
   	$error = login_check($_POST);
   	if (trim($error)=="") {
	   	$_SESSION["member_id"] = login($_POST);
	   	if ((stristr($_SESSION["member_id"], "Invalid") == TRUE) || 
				(stristr($_SESSION["member_id"], "Sorry") == TRUE)) {
		   	die($_SESSION["member_id"] ."<br><a href=" .INDEX_URL .">Return</a>");
	   	}
		// Cdukes: 3/20/08: Carry post variables through login
		// Ref: http://code.google.com/p/php-syslog-ng/issues/detail?id=35
	   	// Header("Location: " .INDEX_URL); // Redirect authenticated member
		$destination = INDEX_URL;
	   	// Remember search query across login
	   	if (!empty($_POST['searchQuery']))
	   	{
		   	$destination .= '?' . ($_POST['searchQuery']);
	   	}
	   	Header("Location: " . $destination); // Redirect authenticated member
	   	exit();
   	} else {
	   	print "Error:$error";
   	}
} elseif (defined('CERTAUTH_ENABLE') && (CERTAUTH_ENABLE == TRUE)) {
    $_POST['username'] = $_SERVER['SSL_CLIENT_S_DN'];
    $_POST['password'] = "nothing";
    $_POST['authtype'] = "cert";

    $error = login_check($_POST);
    if (trim($error)=="") {
        $_SESSION["member_id"] = login($_POST);
        if ((stristr($_SESSION["member_id"], "Invalid") == TRUE) ||
                (stristr($_SESSION["member_id"], "Sorry") == TRUE)) {
            die($_SESSION["member_id"] ."<br><a href=" .INDEX_URL .">Return</a>");
        }
        // Cdukes: 3/20/08: Carry post variables through login
        // Ref: http://code.google.com/p/php-syslog-ng/issues/detail?id=35
        // Header("Location: " .INDEX_URL); // Redirect authenticated member
        $destination = INDEX_URL;
        // Remember search query across login
        if (!empty($_POST['searchQuery']))
        {
            $destination .= '?' . ($_POST['searchQuery']);
        }
        Header("Location: " . $destination); // Redirect authenticated member
        exit();
    } else {
        print "Error:$error";
    }

} else {
   	?>
	   	<html>
	   	<head>
	   	<title><?php echo PAGETITLE;?> Login</title>
	   	<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1">
	   	<meta http-equiv="expires" content="0">
	   	<meta http-equiv="pragma" content="no-cache">
	   	</head>
	   	<SCRIPT LANGUAGE="JavaScript">
	   	<!--
	   	document.onmousedown=click;
   	function click()
   	{
	   	if (event.button==2) {alert('Right-clicking has been disabled by the administrator.');}
   	}

	//-->
   	</SCRIPT>
	   	<div align="center">
	   	<form method="post" action="<?php echo  $_SERVER['PHP_SELF']; ?>">
		<?php
		// Cdukes: 3/20/08: Carry post variables through login
		// Ref: http://code.google.com/p/php-syslog-ng/issues/detail?id=35
	   	// Remember search query across login
	   	if (!empty($_SERVER['QUERY_STRING']))
	   	{
		   	$queryString = htmlspecialchars($_SERVER['QUERY_STRING']);
		   	echo '<input type="hidden" name="searchQuery" value="' . $queryString . '">';
	   	}
   	?>
	   	<div align="center">

		<br><br><br><br>
	   	<table width="210" border="0" cellspacing="0" cellpadding="0">
	   	<tr>
	   	<td align="center">
	   	<fieldset>
	   	<Legend><font face="Verdana,Tahoma,Arial,sans-serif" size="1"
	   	color="gray">
	   	<image src="images/php-syslog-ng.png" alt="Authorization Check" name="image">
		</font>
		</Legend>
	   	<table border="0" cellspacing="3" cellpadding="0">
	   	<tr>
	   	<td align="right" valign="middle"><b><font
	   	face="Verdana,Tahoma,Arial,sans-
	   	serif" size="1" color="gray">Username:</font></td>
	   	<td align="center" valign="middle">
	   	<?php if(defined('DEMO') && DEMO == TRUE) { ?>
	   	<input class="clear" type="text" size="15" name="username" value="demo">
		   	<?php } elseif(defined('WEBBASIC_ENABLE') && (WEBBASIC_ENABLE == TRUE)) { ?>
			   	<input class="clear" type="text" size="35" name="username" value="<?php print $_SERVER['REMOTE_USER']; ?>">
			<?php } else { ?>
	   	<input class="clear" type="text" size="15" name="username">
			<?php } ?>
	   	</td>
	   	</tr>
	   	<tr>
	   	<td align="right" valign="middle"><b><font
	   	face="Verdana,Tahoma,Arial,sans-
	   	serif" size="1" color="gray">Password:</font></td>
	   	<td align="center" valign="middle">
	   	<?php if(defined('DEMO') && DEMO == TRUE) { ?>
	   	<input class="pass" type="password" size="15" name="password" value="demo">
		   	<?php } elseif(defined('WEBBASIC_ENABLE') && (WEBBASIC_ENABLE == TRUE)) { ?>
			   	<input class="pass" type="password" size="35" name="password">
			<?php } else { ?>
	   	<input class="pass" type="password" size="15" name="password">
			<?php } ?>
	   	</td>
		</tr>
	   	<!-- Begin LDAP Mod -->
        <?php if ((LDAP_ENABLE == "YES") || (WEBBASIC_ENABLE == TRUE)) { ?>
	   	<tr>
	   	<td align="right" valign="middle"><b><font
	   	face="Verdana,Tahoma,Arial,sans-
	   	serif" size="1" color="gray">Auth Type:</font></td>
	   	<td align="center" valign="middle">
	   	<SELECT NAME="authtype" STYLE="width: 115px">  
       <?php if (LDAP_ENABLE == "YES") { ?> <OPTION VALUE="ldap" selected> LDAP <?php } ?>
       <?php if (WEBBASIC_ENABLE == TRUE) { ?> <OPTION VALUE="basic" selected> Web Basic <?php } ?>
		<OPTION VALUE="local"> Local
	   	</SELECT>    
	   	</td>
	   	</tr>
		<?php } ?>
	   	<!-- END LDAP Mod -->
	   	</table>
	   	<input type=image src="images/go.gif" alt="Login" name="image">
	   	<br>
	   	</div>
	   	</td>
	   	</tr>
	   	</fieldset>
	   	</table>
	   	<br>
	   	<table width="640"><tr><td align="center">
	   	<?php if(defined('DEMO') && DEMO == TRUE) { ?>
	   	<font face="Verdana,Tahoma,Arial,sans-serif" size="1"
	   	color="red">This demo system is typically running beta code, so don't be surprised if something doesn't work exactly as you expected. <br>For the latest stable version's changelog, please follow <a href="http://php-syslog-ng.gdd.net/CHANGELOG">this link</a>.
		<?php } else { ?>
	   	<font face="Verdana,Tahoma,Arial,sans-serif" size="1"
	   	color="silver">This System is
	   	for the use of authorized users only. Individuals using this computer system
		   	without
			   	authority, or in excess of their authority, are subject to having their activities
			   	on this system
			   	monitored and recorded by system personnel. In the course of monitoring individuals
			   	improperly using this system, or in the course of system maintenance, the activities
			   	of
			   	authorized users may also be monitored. Anyone using this system expressly consents
			   	to
			   	such monitoring and is advised that if such monitoring reveals possible criminal
			   	activity,
			   	system personnel may provide the evidence of such monitoring to law enforcement
				   	officals.
				  	This warning has been provided by the United States Department of Justice and is
				   	intended to
				   	ensure that monitoring of user activity is not in violation of the Communications
				   	Privacy Act of
				   	1986.
					<?php } ?>
					</font>
				   	</td></tr>
					</table>

					</div>
				   	</form>

					</div>
				   	</body>
				   	</html>
<?php } ?>

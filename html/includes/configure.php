<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com

require_once 'includes/html_header.php';

//========================================================================
// BEGIN: GET THE INPUT VARIABLES
//========================================================================
$configTask = get_input('configTask');
//========================================================================
// END: GET THE INPUT VARIABLES
//========================================================================

//========================================================================
// BEGIN: HANDLE PASSWORD CHANGE REQUEST
//========================================================================
if(strcasecmp($configTask, "chgmypw") == 0) {
	$username = $_SESSION["username"];
	$oldpw = get_input('oldpw');
	$newpw1 = get_input('newpw1');
	$newpw2 = get_input('newpw2');
	$chgPw = TRUE;

	// Make sure newpw1 and newpw2 are identical
	if(strcmp($newpw1, $newpw2) != 0) {
		echo "Typo! The two passwords were not identical.<p>";
		$chgPw = FALSE;
	}

	// Make sure the old oldpw is correct
	$oldpwHash = md5($oldpw);
	$query = "SELECT * FROM ".AUTHTABLENAME." WHERE username='".$username."'
		AND pwhash='".$oldpwHash."'";
	$result = perform_query($query, $dbLink);
	if(num_rows($result) != 1) {
		echo "Old password was not correct.<p>";
		$chgPw = FALSE;
	}

	// Make sure the password is valid
	if(!validate_input($newpw1, 'password')) {
		echo "Invalid password. Passwords have to be at least 4 characters.";
		$chgPw = FALSE;
	}

	if($chgPw) {
		// Change password
		$newpwHash = md5($newpw1);
		$query = "UPDATE ".AUTHTABLENAME." SET pwhash='".$newpwHash."' WHERE
			username='".$username."'";

		perform_query($query, $dbLink);
		echo "Your password was changed!";
	}
}
//========================================================================
// END: HANDLE PASSWORD CHANGE REQUEST
//========================================================================

//========================================================================
// BEGIN: HANDLE NEW USER REQUEST
//========================================================================
if(strcasecmp($configTask, "adduser") == 0) {
	$newuser = get_input('newuser');
	$newuserpw1 = get_input('newuserpw1');
	$newuserpw2 = get_input('newuserpw2');
	$addUser = TRUE;

	// Make sure $newuserpw1 and $newuserpw2 are identical
	if(strcmp($newuserpw1, $newuserpw2) != 0) {
		echo "Typo! The two passwords were not identical.<p>";
		$addUser = FALSE;
        }

	// Make sure the username is valid
	if(!validate_input($newuser, 'username')) {
		echo "Invalid username. Usernames must be at least 4 character and only use alpha-numeric and _ (underscore).";
		$addUser = FALSE;
	}

	// Make sure the password is valid
	if(!validate_input($newuserpw1, 'password')) {
		echo "Invalid password. Passwords have to be at least 4 characters.";
		$addUser = FALSE;
	}

	// Make sure the username isn't already used
	if($addUser) {
		$query = "SELECT * FROM ".AUTHTABLENAME." WHERE username='".$newuser."'";
		$result = perform_query($query, $dbLink);
		if(num_rows($result) != 0) {
			echo "User already exists!<p>";
			$addUser = FALSE;
		}
	}

	// Add user
	if($addUser && grant_access($username, 'add_user', $dbLink)) {
		$newuserpwHash = md5($newuserpw1);
		$admLink = db_connect_syslog(DBADMIN, DBADMINPW, 'C');
		$query = "INSERT INTO ".AUTHTABLENAME." (username, pwhash) VALUES('".
			$newuser."', '".$newuserpwHash."')";
		perform_query($query, $admLink);
		echo "New user added: ".$newuser.".";
		mysql_close($admLink);
	}
}
//========================================================================
// END: HANDLE NEW USER REQUEST
//========================================================================

//========================================================================
// BEGIN: HANDLE CHANGE OTHER USER'S PASSWORD
//========================================================================
if(strcasecmp($configTask, "chguserpw") == 0) {
	$chguser = get_input('chguser');
	$newpw1 = get_input('newpw1');
	$newpw2 = get_input('newpw2');
	$chgUserPw = TRUE;

	// Make sure $newpw1 and $newpw2 are identical
	if(strcmp($newpw1, $newpw2) != 0) {
		echo "Typo! The two passwords were not identical.<p>";
		$chgUserPw = FALSE;
        }

	// Make sure the username is valid
	if(!validate_input($chguser, 'username')) {
		echo "Invalid username. Usernames must be at least 4 character and only use alpha-numeric and _ (underscore).";
		$chgUserPw = FALSE;
	}

	// Make sure the password is valid
	if(!validate_input($newpw1, 'password')) {
		echo "Invalid password. Passwords have to be at least 4 characters.";
		$chgUserPw = FALSE;
	}

	// Make sure the username exists
	if($chgUserPw) {
		$query = "SELECT * FROM ".AUTHTABLENAME." WHERE username='".$chguser."'";
		$result = perform_query($query, $dbLink);
		if(num_rows($result) != 1) {
			echo "User does not exist!<p>";
			$chgUserPw = FALSE;
		}
	}

	// If the input is correct then change the password
	if($chgUserPw && grant_access($username, 'edit_user', $dbLink)) {
		$newpwHash = md5($newpw1);
		$query = "UPDATE ".AUTHTABLENAME." SET pwhash='".$newpwHash."' WHERE username='".$chguser."'";
		perform_query($query, $dbLink);
		echo "Password changed for user: ".$chguser.".";
	}
}
//========================================================================
// END: HANDLE CHANGE OTHER USER'S PASSWORD
//========================================================================

//========================================================================
// BEGIN: HANDLE DELETE USER REQUEST
//========================================================================
if(strcasecmp($configTask, "deluser") == 0) {
	$delusername = get_input('delusername');
	$delUser = TRUE;

	// Make sure there are at least two users
	$query = "SELECT COUNT(*) FROM ".AUTHTABLENAME;
	$result = perform_query($query, $dbLink);
	$rowCount = fetch_array($result);
	if($rowCount[0] < 2) {
		echo "There has to be at least one user.";
		$delUser = FALSE;
	}

	// Make sure the username is valid
	if(!validate_input($delusername, 'username')) {
		echo "Invalid username. Usernames must be at least 4 character and only use alpha-numeric and _ (underscore).";
		$delUser = FALSE;
	}

	// Make sure the username exists
	if($delUser) {
		$query = "SELECT COUNT(*) FROM ".AUTHTABLENAME." WHERE username='".$delusername."'";
		$result = perform_query($query, $dbLink);
		$rowCount = fetch_array($result);
		if($rowCount[0] == 0) {
			echo "Username ".$delusername." does not exist!";
			$delUser = FALSE;
		}
	}

	// If conditions are OK then delete the user
	if($delUser && grant_access($username, 'edit_user', $dbLink)) {
		$admLink = db_connect_syslog(DBADMIN, DBADMINPW, 'C');
		$query = "DELETE FROM ".AUTHTABLENAME." WHERE username='".$delusername."'";
		perform_query($query, $admLink);
		mysql_close($admLink);
	}
}
//========================================================================
// END: HANDLE DELETE USER REQUEST
//========================================================================

//========================================================================
// BEGIN: HANDLE RELOAD CACHE REQUEST
//========================================================================
if(strcasecmp($configTask, "reloadCache") == 0) {
	$reloadCache = TRUE;

	// Make sure caching is enabled
	if(!defined('USE_CACHE') || !USE_CACHE) {
		echo "Caching of the search page is not enabled.";
		$reloadCache = FALSE;
	}

	// Make sure the cache table exists
	if(!table_exists(CACHETABLENAME, $dbLink)) {
		echo "The cache table does not exist.";
		$reloadCache = FALSE;
	}

	// If conditions are OK then reload the cache
	if($reloadCache && grant_access($username, 'reload_cache', $dbLink)) {
		$reloadingWithMergeData = FALSE;
		if(table_exists(MERGELOGTABLE, $dbLink)) {
			$sql = "SELECT * FROM ".MERGELOGTABLE." LIMIT 1";
			$result = perform_query($sql, $dbLink);
			if(num_rows($result)) {
				$reloadingWithMergeData = TRUE;
				reload_cache(MERGELOGTABLE, $dbLink);
				echo "The search cache has been updated.";
			}
		}

		if(!$reloadingWithMergeData) {
			$tableArray = get_logtables($dbLink);
			foreach($tableArray as $table) {
				reload_cache($table, $dbLink);
				echo "The search cache has been updated.";
			}
		}
	}
}
//========================================================================
// END: HANDLE RELOAD CACHE REQUEST
//========================================================================

//========================================================================
// BEGIN: HANDLE SET USER ACCESS REQUEST
//========================================================================
if(strcasecmp($configTask, "updateUserACL") == 0) {
	$selectuser = get_input('selectuser');
	$setUserAccess = TRUE;
	
	// Make sure access controls are enabled
	if(!defined('USE_ACL') || !USE_ACL) {
		echo "Access control is not enabled.";
		$setUserAccess = FALSE;
	}

	// Make sure the username of selectuser is OK
	if(!validate_input($selectuser, 'username')) {
		echo "Invalid username. Usernames must be at least 4 character and only use alpha-numeric and _ (underscore).";
		$setUserAccess = FALSE;
	}
	
	// Make sure the user exists
	if($setUserAccess) {
		$sql = "SELECT * FROM ".AUTHTABLENAME." WHERE username='".$selectuser."'";
		$result = perform_query($sql, $dbLink);
		if(num_rows($result) == 0) {
			echo "Username ".$selectuser." does not exist!";
			$setUserAccess = FALSE;
		}
	}
	
	// If conditions are OK then update the user's access
	if($setUserAccess && grant_access($username, 'edit_acl', $dbLink)) {
		$actionInputs = array();
		$sql = "SELECT * FROM ".ACTION_TABLE;
		$result = perform_query($sql, $dbLink);
		$admLink = db_connect_syslog(DBADMIN, DBADMINPW, 'C');
		while($row = fetch_array($result)) {
			$actionname = $row['actionname'];
			$inputVal = get_input($actionname.'_acl');
			if($inputVal == 1) {
				$inputVal = 'TRUE';
			}
			else {
				$inputVal = 'FALSE';
			}
			$sql = "SELECT * FROM ".USER_ACCESS_TABLE." WHERE username='".$selectuser.
					"' AND actionname='".$actionname."'";
			$innerResult = perform_query($sql, $dbLink);
			if(num_rows($innerResult)) {
				$sql = "UPDATE ".USER_ACCESS_TABLE." SET access='".$inputVal.
					"' WHERE username='".$selectuser."' AND actionname='".$actionname."'";
			}
			else {
				$sql = "INSERT INTO ".USER_ACCESS_TABLE." (username,actionname,access) 
						VALUES('".$selectuser."','".$actionname."','".$inputVal."')";
			}
			perform_query($sql, $admLink);
		}
		mysql_close($admLink);
		echo "The access control settings for ".$selectuser." have been updated.";
	}
}
//========================================================================
// END: HANDLE SET USER ACCESS REQUEST
//========================================================================

//========================================================================
// BEGIN: HANDLE SET DEFAULT ACCESS REQUEST
//========================================================================
if(strcasecmp($configTask, "updateDefaultACL") == 0) {
	$setDefaultAccess = TRUE;

	// Make sure access controls are enabled
	if(!defined('USE_ACL') || !USE_ACL) {
		echo "Access control is not enabled.";
		$setDefaultAccess = FALSE;
	}

	// If conditions are OK then update the default access
	if($setDefaultAccess && grant_access($username, 'edit_acl', $dbLink)) {
		$actionInputs = array();
		$sql = "SELECT * FROM ".ACTION_TABLE;
		$result = perform_query($sql, $dbLink);
		$admLink = db_connect_syslog(DBADMIN, DBADMINPW, 'C');
		while($row = fetch_array($result)) {
			$actionname = $row['actionname'];
			$inputVal = get_input($actionname.'_acl');
			if($inputVal == 1) {
				$inputVal = 'TRUE';
			}
			else {
				$inputVal = 'FALSE';
			}
			$sql = "UPDATE ".ACTION_TABLE." SET defaultaccess='".$inputVal."'
				WHERE actionname='".$actionname."'";
			perform_query($sql, $admLink);
		}
		mysql_close($admLink);
		echo "The default access settings have been updated.";
	}
}
//========================================================================
// END: HANDLE SET DEFAULT ACCESS REQUEST
//========================================================================

//========================================================================
// BEGIN: BUILDING THE HTML FORMS
//========================================================================
if(defined('REQUIRE_AUTH') && REQUIRE_AUTH) {
?>
<table class="pagecontent">
<tr><td>
<form action="index.php" method="POST">
<input type="hidden" name="pageId" value="config">
<input type="hidden" name="configTask" value="chgmypw">
<table><tr><td>
	<table class="searchform">
	<tr class="lighter"><td>
		<b>CHANGE YOUR PASSWORD:</b>
		<table align="center" class="formentry">
		<tr><td>
		Old password:
		</td><td>
		<input type="password" size=12 maxlength=32 name="oldpw">
		</td></tr><tr><td>
		New password:
		</td><td>
		<input type="password" size=12 maxlength=32 name="newpw1">
		</td></tr><tr><td>
		Retype new password:
		</td><td>
		<input type="password" size=12 maxlength=32 name="newpw2">
		</td></tr></table>
	</td></tr></table>
</td></tr>
	<table class="searchform">
	<tr><td class="darker">
	<input type="submit" value="Change Password">
	<input type="reset" value="Reset">
	</td></tr></table>
</td></tr></table>
</form>
</td></tr></table>
<?php
}

//------------------------------------------------------------------------
// cdukes - Added SQL search below to check whether of not user has permission
// to modify access controls, if they do then the options will be shown.
//------------------------------------------------------------------------ 
$acl = "FALSE";
$sql = "SELECT * FROM ".USER_ACCESS_TABLE." WHERE username='" .$_SESSION["username"] ."'";
$result = perform_query($sql, $dbLink);
while($row = fetch_array($result)) {
	   	if (array_search(edit_acl, $row))  {
			$acl = $row[2];
		}
}
if ($acl == 'TRUE') {

if(defined('REQUIRE_AUTH') && REQUIRE_AUTH && grant_access($username, 'add_user', $dbLink)) {
?>
<table class="pagecontent">
<tr><td>
<form action="index.php" method="POST">
<input type="hidden" name="pageId" value="config">
<input type="hidden" name="configTask" value="adduser">
<table><tr><td>
	<table class="searchform">
	<tr class="lighter"><td>
		<b>ADD USER:</b>
		<table align="center" class="formentry">
		<tr><td>
		New username:
		</td><td>
		<input type="text" size=12 maxlength=32 name="newuser">
		</td></tr><tr><td>
		New user password:
		</td><td>
		<input type="password" size=12 maxlength=32 name="newuserpw1">
		</td></tr><tr><td>
		Retype new user password:
		</td><td>
		<input type="password" size=12 maxlength=32 name="newuserpw2">
		</td></tr></table>
	</td></tr></table>
</td></tr>
	<table class="searchform">
	<tr><td class="darker">
	<input type="submit" value="Add user">
	<input type="reset" value="Reset">
	</td></tr></table>
</td></tr></table>
</form>
</td></tr></table>
<?php
}
if(defined('REQUIRE_AUTH') && REQUIRE_AUTH && grant_access($username, 'edit_user', $dbLink)) {
?>
<table class="pagecontent">
<tr><td>
<form action="index.php" method="POST">
<input type="hidden" name="pageId" value="config">
<input type="hidden" name="configTask" value="chguserpw">
<table><tr><td>
	<table class="searchform">
	<tr class="lighter"><td>
		<b>CHANGE USER'S PASSWORD:</b>
		<table align="center" class="formentry">
		<tr><td>
		Username:
		</td><td>
		<input type="text" size=12 maxlength=32 name="chguser">
		</td></tr><tr><td>
		New password:
		</td><td>
		<input type="password" size=12 maxlength=32 name="newpw1">
		</td></tr><tr><td>
		Retype new password:
		</td><td>
		<input type="password" size=12 maxlength=32 name="newpw2">
		</td></tr></table>
	</td></tr></table>
</td></tr>
	<table class="searchform">
	<tr><td class="darker">
	<input type="submit" value="Change user's password">
	<input type="reset" value="Reset">
	</td></tr></table>
</td></tr></table>
</form>
</td></tr></table>
<?php
}
if(defined('REQUIRE_AUTH') && REQUIRE_AUTH && grant_access($username, 'edit_user', $dbLink)) {
?>
<table class="pagecontent">
<tr><td>
<form action="index.php" method="POST">
<input type="hidden" name="pageId" value="config">
<input type="hidden" name="configTask" value="deluser">
<table><tr><td>
	<table class="searchform">
	<tr class="lighter"><td>
		<b>DELETE USER:</b>
		<table align="center" class="formentry">
		<tr><td>
		<select name="delusername" size="8">
<?php
	$query = "SELECT username FROM ".AUTHTABLENAME;
	$result = perform_query($query, $dbLink);
	while($row = fetch_array($result)) {
		echo "<option>".htmlentities($row[0])."</option>";
	}
?>
		</select>
		</td></tr></table>
	</td></tr></table>
</td></tr>
	<table class="searchform">
	<tr><td class="darker">
	<input type="submit" value="Delete user">
	<input type="reset" value="Reset">
	</td></tr></table>
</td></tr></table>
</form>
</td></tr></table>
<?php
}
if(defined('USE_ACL') && USE_ACL && grant_access($username, 'edit_acl', $dbLink)) {
	// User list
	$sql = "SELECT username FROM ".AUTHTABLENAME;
	$userResult = perform_query($sql, $dbLink);

	// Get default access
	$sql = "SELECT * FROM ".ACTION_TABLE;
	$defaultResult = perform_query($sql, $dbLink);

	// Create Javascript function that sets the current permissions
	echo "<script language=\"JavaScript\">\n<!--Begin Hide\nfunction setAccess(username) {";
	while($row = fetch_array($userResult)) {
		result_seek($defaultResult, 0);
		echo "if(username == '".$row['username']."') {\n";
		while($innerRow = fetch_array($defaultResult)) {
			$sql = "SELECT access FROM ".USER_ACCESS_TABLE." WHERE username='".$row['username'].
				"' AND actionname='".$innerRow['actionname']."'";
			$accessResult = perform_query($sql, $dbLink);
			if(num_rows($accessResult)) {
				$accessRow = fetch_array($accessResult);
				$access = $accessRow['access'];
			}
			else {
				$access = $innerRow['defaultaccess'];
			}
			if($access == 'TRUE') {
				echo "document.useracl.".$innerRow['actionname']."_acl[0].checked=true\n";
			}
			else {
				echo "document.useracl.".$innerRow['actionname']."_acl[1].checked=true\n";
			}
		}
		echo "}\n";
	}
	echo "}\n// End Hide-->\n</script>";
?>
<table class="pagecontent">
<tr><td>
<form action="index.php" method="POST" name="useracl">
<input type="hidden" name="pageId" value="config">
<input type="hidden" name="configTask" value="updateUserACL">
<table><tr><td>
	<table class="searchform">
	<tr class="lighter"><td>
		<b>SET USER ACCESS:</b>
		<table align="center" class="formentry">
		<tr><td>
		<select name="selectuser" onchange="setAccess(this.value)" size="8">
<?php
	result_seek($userResult, 0);
	while($row = fetch_array($userResult)) {
		echo "<option>".$row['username']."</option>";
	}
?>		
		</select>
		</td></tr>
		</table>
		
		<table align="center" class="formentry">
		<tr><td>
		</td><td>
		ALLOW
		</td><td>
		DENY
		</td></tr>
<?php
	result_seek($defaultResult, 0);
	while($row = fetch_array($defaultResult)) {
		echo "<tr><td>".$row['actiondescr']."</td><td><input name=\"";
		echo $row['actionname']."_acl\" value=\"1\" type=\"radio\">";
		echo "</td><td><input name=\"".$row['actionname']."_acl\"";
		echo " value=\"0\" type=\"radio\">";
		echo "</td></tr>\n";
	}
?>
		</table>
	</td></tr></table>
</td></tr>
	<table class="searchform">
	<tr><td class="darker">
	<input type="submit" value="Set user access">
	<input type="reset" value="Reset">
	</td></tr></table>
</td></tr></table>
</form>
</td></tr></table>
<?php
	// Get default access
	$sql = "SELECT * FROM ".ACTION_TABLE;
	$defaultResult = perform_query($sql, $dbLink);
?>
<table class="pagecontent">
<tr><td>
<form action="index.php" method="POST">
<input type="hidden" name="pageId" value="config">
<input type="hidden" name="configTask" value="updateDefaultACL">
<table><tr><td>
	<table class="searchform">
	<tr class="lighter"><td>
		<b>SET DEFAULT ACCESS:</b>
		<table align="center" class="formentry">
		<tr><td>
		</td><td>
		ALLOW
		</td><td>
		DENY
		</td></tr>
<?php
	while($row = fetch_array($defaultResult)) {
		echo "<tr><td>".$row['actiondescr']."</td><td><input name=\"";
		echo $row['actionname']."_acl\" value=\"1\" type=\"radio\"";
		if($row['defaultaccess'] == 'TRUE') {
			echo " checked>";
		}
		else {
			echo ">";
		}
		echo "</td><td><input name=\"".$row['actionname']."_acl\"";
		echo " value=\"0\" type=\"radio\"";
		if($row['defaultaccess'] == 'FALSE') {
			echo " checked>";
		}
		else {
			echo ">";
		}
		echo "</td></tr>\n";
	}
?>
		</table>
	</td></tr></table>
</td></tr>
	<table class="searchform">
	<tr><td class="darker">
	<input type="submit" value="Set default access">
	<input type="reset" value="Reset">
	</td></tr></table>
</td></tr></table>
</form>
</td></tr></table>
<?php
}
if(defined('USE_CACHE') && USE_CACHE && grant_access($username, 'reload_cache', $dbLink)) {
?>
<table class="pagecontent">
<tr><td>
<form action="index.php" method="POST">
<input type="hidden" name="pageId" value="config">
<input type="hidden" name="configTask" value="reloadCache">
<table><tr><td>
	<table class="searchform">
	<tr class="lighter"><td>
		<b>RELOAD SEARCH CACHE:</b>
		<table align="center" class="formentry">
		<tr><td>
		Reloading the cache may take a while if you have a lot of log data.
		</td></tr></table>
	</td></tr></table>
</td></tr>
	<table class="searchform">
	<tr><td class="darker">
	<input type="submit" value="Reload cache">
	</td></tr></table>
</td></tr></table>
</form>
</td></tr></table>
<?php
}

//-----------------------------------------------------------------------
// Endif - if ($username == 'admin')
//-----------------------------------------------------------------------
}
 
//========================================================================
// END: BUILDING THE HTML FORMS
//========================================================================
?>

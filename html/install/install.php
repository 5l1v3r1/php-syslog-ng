<?php
/**
* @version $Id: install.php,v 1.0 2006/06/16 09:00:00 cdukes Exp $
* @package PHP-Syslog-NG
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* PHP-Syslog-NG is Free Software
*/

if (file_exists( '../config/config.php' ) && filesize( '../config/config.php' ) > 10) {
	header( 'Location: ../index.php' );
	exit();
}
/** Include common.php */
include_once( 'common.php' );
include_once( '../includes/version.php' );
function writableCell( $folder ) {
	echo "<tr>";
	echo "<td class=\"item\">" . $folder . "/</td>";
	echo "<td align=\"left\">";
	echo is_writable( "../$folder" ) ? '<b><font color="green">Writeable</font></b>' : '<b><font color="red">Unwriteable</font></b>' . "</td>";
	echo "</tr>";
}
?>
<?php echo "<?xml version=\"1.0\" encoding=\"iso-8859-1\"?".">"; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
<head>
<title><?php echo $_VERSION->PRODUCT; ?> - Web Installer</title>
<meta http-equiv="Content-Type" content="text/html; charset=iso-8859-1" />
<link rel="shortcut icon" href="../../images/favicon.ico" />
<link rel="stylesheet" href="install.css" type="text/css" />
<script type="text/javascript">
<!--
var checkobj;

function agreesubmit(el){
	checkobj=el;
	if (document.all||document.getElementById){
		for (i=0;i<checkobj.form.length;i++){  //hunt down submit button
			var tempobj=checkobj.form.elements[i];
			if(tempobj.type.toLowerCase()=="submit")
				tempobj.disabled=!checkobj.checked;
		}
	}
}

function defaultagree(el){
	if (!document.all&&!document.getElementById){
		if (window.checkobj&&checkobj.checked)
			return true;
		else{
			alert("Please read/accept license to continue installation");
			return false;
		}
	}
}
//-->
</script>
</head>
<body onload="document.adminForm.next.disabled=true;">
<div id="wrapper">
	<div id="header">
		<div id="psng"><img src="header_install.png" alt="<?php echo $_VERSION->PRODUCT; ?> Installation" /></div>
	</div>
</div>
<div id="ctr" align="center">
	<form action="install1.php" method="post" name="adminForm" id="adminForm" onSubmit="return defaultagree(this)">
	<div class="install">
	<div id="stepbar">
		<div class="step-off">pre-installation check</div>
		<div class="step-on">license</div>
		<div class="step-off">step 1</div>
		<div class="step-off">step 2</div>
		<div class="step-off">step 3</div>
		<div class="step-off">step 4</div>
	</div>
	<div id="right">
		<div id="step">license</div>
		<div class="far-right">
		<input class="button" type="submit" name="next" value="Next &gt;&gt;" disabled="disabled"/>
		</div>
		<div class="clr"></div>
		<h1>GNU/GPL License:</h1>
		<div class="licensetext">
				<a href="https://sourceforge.net/projects/php-syslog-ng"><?php echo $_VERSION->PRODUCT; ?> </a> is Free Software released under the <a href="http://www.gnu.org/copyleft/gpl.html">GNU/GPL License </a>.
				<div class="error">*** To continue installing <?php echo $_VERSION->PRODUCT; ?> you must check the box under the license ***</div>

		</div>
		<div class="clr"></div>
		<div class="license-form">
			<div class="form-block" style="padding: 0px;">
				<iframe src="gpl.txt" class="license" frameborder="0" scrolling="auto"></iframe>
			</div>
		</div>
		<div class="clr"></div>
		<div class="ctr">
				<input type="checkbox" name="agreecheck" id="agreecheck" class="inputbox" onClick="agreesubmit(this)" />
				<label for="agreecheck">
					I understand that this software is released under the GNU/GPL License</label>
				</div>
		<div class="clr"></div>
		</div>
		<div id="break"></div>
	<div class="clr"></div>
	<div class="clr"></div>
	</div>
	</form>
</div>
</body>
</html>

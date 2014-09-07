<?php
/**
* @version $Id: common.php,v 1.0 2006/06/16 09:00:00 cdukes Exp $
* @package PHP-Syslog-NG
* @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
* PHP-Syslog-NG is Free Software
*/

error_reporting( E_ALL );

header ("Cache-Control: no-cache, must-revalidate");	// HTTP/1.1
header ("Pragma: no-cache");	// HTTP/1.0

/**
* Utility function to return a value from a named array or a specified default
*/
define( "_MOS_NOTRIM", 0x0001 );
define( "_MOS_ALLOWHTML", 0x0002 );
function mosGetParam( &$arr, $name, $def=null, $mask=0 ) {
	$return = null;
	if (isset( $arr[$name] )) {
		if (is_string( $arr[$name] )) {
			if (!($mask&_MOS_NOTRIM)) {
				$arr[$name] = trim( $arr[$name] );
			}
			if (!($mask&_MOS_ALLOWHTML)) {
				$arr[$name] = strip_tags( $arr[$name] );
			}
			if (!get_magic_quotes_gpc()) {
				$arr[$name] = addslashes( $arr[$name] );
			}
		}
		return $arr[$name];
	} else {
		return $def;
	}
}

function mosMakePassword($length) {
	$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
	$len = strlen($salt);
	$makepass="";
	mt_srand(10000000*(double)microtime());
	for ($i = 0; $i < $length; $i++)
	$makepass .= $salt[mt_rand(0,$len - 1)];
	return $makepass;
}

/**
* Chmods files and directories recursively to given permissions
* @param path The starting file or directory (no trailing slash)
* @param filemode Integer value to chmod files. NULL = dont chmod files.
* @param dirmode Integer value to chmod directories. NULL = dont chmod directories.
* @return TRUE=all succeeded FALSE=one or more chmods failed
*/
function mosChmodRecursive($path, $filemode=NULL, $dirmode=NULL)
{
	$ret = TRUE;
	if (is_dir($path)) {
	    $dh = opendir($path);
	    while ($file = readdir($dh)) {
	        if ($file != '.' && $file != '..') {
	            $fullpath = $path.'/'.$file;
	            if (is_dir($fullpath)) {
                    if (!mosChmodRecursive($fullpath, $filemode, $dirmode))
                        $ret = FALSE;
	            } else {
	                if (isset($filemode))
	                    if (!@chmod($fullpath, $filemode))
	                        $ret = FALSE;
	            } // if
	        } // if
	    } // while
	    closedir($dh);
	    if (isset($dirmode))
	        if (!@chmod($path, $dirmode))
	            $ret = FALSE;
	} else {
		if (isset($filemode))
			$ret = @chmod($path, $filemode);
    } // if
	return $ret;
} // mosChmodRecursive

// Get the 'memory_limit' setting for this php installation
function get_memory_limit_in_metabytes() {
	$memory_limit = ini_get('memory_limit');
	$units        = substr($memory_limit,-1,1);

	if (!is_numeric($units)) {
		$memory_limit = intval(substr($memory_limit,0,-1));
		$units        = strtolower($units);
	} else {
		// $memory_limit is in bytes
		$memory_limit = intval($memory_limit);
	}

	// sanity check
	if ($memory_limit <= 0) {
		return 0;
	}

	switch ($units) {
		case 'g': return ($memory_limit * 1024);
		case 'm': return ($memory_limit);
		case 'k': return ($memory_limit / 1024);
		default:  return ($memory_limit / 1024 / 1024);
	}
}

?>

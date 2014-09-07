<?php
require_once ("config/config.php");
	/* BEGIN: Added by BPK to reset search selections memory */
	session_start();
	foreach ($_SESSION as $key => $value) {
		unset($_SESSION[$key]);
		session_unregister($key);
	}
	session_unset();
	session_destroy();
	/* END: Added by BPK to reset search selections memory */
// session_start();
// session_destroy();
header("Location: " . INDEX_URL);
exit;
?> 

<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com

//------------------------------------------------------------------------
// Determine how long all this stuff took to generate.
//------------------------------------------------------------------------
$time_end = get_microtime();
$exetime = $time_end - $time_start;

// No need to show execution time if logging out...
if(strcasecmp($pageId, "login") != 0) {
echo "</center>\n<hr>";
echo "<table><tr><td>";
echo "<td class=\"rightfooter\">";
echo "Executed in <b>".$exetime." seconds</b>";
}
echo "</td>";

echo "</tr></table>";
echo "</body></html>";
//========================================================================
// END: BUILDING THE HTML PAGE
//========================================================================
?>

<?php
/*
 * blocks/graph-logs_per_day.php
 *
 * Developed by Clayton Dukes <cdukes@cdukes.com>
 * Copyright (c) 2008 gdd.net
 * Licensed under terms of GNU General Public License.
 * All rights reserved.
 *
 * Changelog:
 * 2008-05-13 - created
 * 2008-07-15 - Modified for use w/ SqueezeDB
 * 2008-08-01 - Added checks to see if all_logs exists so that new installs wouldn't puke
 * 2008-08-06 - Modified for use w/ caching
 *
 */

/* $Platon$ */

$today = date("Y-m-d");
$today_doy = date("D",mktime());

// check to see if this is a new install (MERGELOGTABLE doesn't exists)
$check = mysql_query ("SELECT * FROM ".MERGELOGTABLE." LIMIT 0,1"); /* >>limit<< is just to make it faster in case the db is huge */

// how many days back to graph (i.e. 6 = 1 week (don't forget, counting starts at zero))
// Less than 4 looks pretty bad (can't fit the data in the box)
if ($check) {
   	$days_back = 6; 
} else {
   	$days_back = 0; 
}
$day_names = array();
if ($days_back > 0) {
   	for ($i=0;$i<=$days_back;$i++){
	   	$day = date( "D", strtotime ("-$i day" ) ) ;
	   	array_push($day_names, $day);
   	}
} else {
   	$day = date( "D", strtotime ("$i day" ) ) ;
   	array_push($day_names, $day);
}
// die(print_r($day_names));

$day_names = array_reverse($day_names);
$data = array();
// First grab today's count (skip if using the cache table)
if(!defined('LPD_CACHE') || LPD_CACHE == FALSE) {
   	if(defined('SQZ_ENABLED') && SQZ_ENABLED == TRUE) {
	   	$sql = "SELECT SUM(counter) as count from ".DEFAULTLOGTABLE." WHERE datetime BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
   	} else {
	   	$sql = "SELECT count(*) as count from ".DEFAULTLOGTABLE." WHERE datetime BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 1 DAY)";
   	}
   	$queryresult = perform_query($sql, $dbLink);
   	while ($line = fetch_array($queryresult)) {
	   	array_push($data, $line['count']);
   	}
}

// Now get today + past $days_back count from  one of our storage tables (depending on our method of storage)
	   	for ($i=0;$i<=$days_back;$i++){
		   	if(defined('LPD_CACHE') && LPD_CACHE == TRUE) {
			   	$sql = "SELECT value as count from ".CACHETABLENAME." WHERE type='LPD' AND updatetime=DATE_SUB(CURDATE(), INTERVAL $i DAY)";
		   	} else {
			   	if(defined('SQZ_ENABLED') && SQZ_ENABLED == TRUE) {
				   	$sql = "SELECT SUM(counter) as count from ".MERGELOGTABLE." WHERE datetime BETWEEN DATE_SUB(CURDATE(), INTERVAL $i DAY) AND DATE_SUB(DATE_ADD(CURDATE(), INTERVAL 1 DAY), INTERVAL $i DAY)";
			   	} else {
				   	$sql = "SELECT count(*) as count from ".MERGELOGTABLE." WHERE datetime BETWEEN DATE_SUB(CURDATE(), INTERVAL $i DAY) AND DATE_SUB(DATE_ADD(CURDATE(), INTERVAL 1 DAY), INTERVAL $i DAY)";
			   	}
		   	}
		   	$queryresult = perform_query($sql, $dbLink);
		   	$line = fetch_array($queryresult);
		   	array_push($data, $line['count']);
		   	// echo "$sql<br>\n";
		   // 	echo "LOOP $i = " . $line['count'] ."<br>";
	   	}
$data = array_reverse($data);
$max = 1;
for ($i=0;$i<=$days_back;$i++){
   	if ($data[$i] > $max)$max=$data[$i];  // find the largest data
}
// Set variables used based on $days_back so it scales accordingly
if (($days_back < 7) && ($days_back > 0)) {
	if ($max > 100000) {
   	$multiplier = 1.8; // how much to grow the width
   	$pad = 30; // Padding between each bar or value
	} else {
   	$multiplier = 2.4; // how much to grow the width
   	$pad = 20; // Padding between each bar or value
	}
   	$width = round(($days_back + 1) * $multiplier * ceil($pad - $multiplier));
} else {
   	if ($days_back > 6) {
	   	$multiplier = 2.25; // how much to grow the width
	   	$pad = 15; // Padding between each bar or value, less padding here since the width is more
	   	$width = round(($days_back + 1) * $multiplier * floor($pad + $multiplier));
   	} else {
	   	$multiplier = 12; // how much to grow the width
	   	$pad = 15; // Padding between each bar or value, less padding here since the width is more
	   	$width = round(($days_back + 1) * $multiplier * floor($pad + $multiplier));
   	}

}
#die("Width = $width");
$im = imagecreate($width,255); // width , height px
#320
// set the graph color variables 
// $im        = imagecreate($width,$height); 
$gray      = imagecolorallocate ($im,0xcc,0xcc,0xcc); 
$gray_lite = imagecolorallocate ($im,0xee,0xee,0xee); 
$gray_dark = imagecolorallocate ($im,0x7f,0x7f,0x7f); 
$white     = imagecolorallocate ($im,0xff,0xff,0xff);

$white = imagecolorallocate($im,255,255,255); // allocate some color from RGB components 
$black = imagecolorallocate($im,0,0,0);   //
$red = imagecolorallocate($im,255,0,0);   //
$green = imagecolorallocate($im,0,255,0); //
$blue = imagecolorallocate($im,0,0,255);  //
//
//draw X, Y Co-Ordinate

// Below is: 
// Starting at top left
// X is left to right
// Y is top to bottom
$Xstart = 10;
$Ystart = 5;
$Xend   = 10;
$Yend   = 230;
// Left blue line (draw a line from top left to bottom left):
imageline($im, $Xstart, $Ystart, $Xend, $Yend, $black );
$Xstart = 10;
$Ystart = 230; 
$Xend   = ($width - 20);
$Yend   = 230;
// Bottom blue line (draw a line from bottom left to bottom right):
imageline($im, $Xstart, $Ystart, $Xend, $Yend, $blue );

// create background box
imagerectangle($im,0, 0, $Xend+18, $Yend+24, $gray_dark);

//Print X, Y
imagestring($im,3,15,5,"Logs Per Day",$black);

// imagestring($im,5,100,50,"Test Graph",$red);
// imagestring($im,5,125,75,"cdukes",$green);

// what next draw the bars
$x = 15;    // bar x1 position
$y = 229;    // bar $y1 position
$x_width = 19;  // width of bars
$y_ht = 0; // height of bars, will be calculated later
// Draw the Days of the week:
for ($i=0;$i<$days_back+1;$i++){
   	$y_ht = round(($data[$i]/$max)* 100);    // no validation so check if $max = 0 later;
   	// trying to fill the boxes in...clearly, I suck at this...someone make this prettier :-)
   	imagerectangle($im,$x-1,$y-1,($x+$x_width),($y-$y_ht-1),$black); // draw the outline
   	imagefill($im,$x+1,($y-$y_ht+1),$blue); // fill it
   	imagerectangle($im,($x+$x_width)-1,$y-1,($x+$x_width) + 2,($y-$y_ht) +1,$gray_lite); // 3d-ish bar
   	imagefill($im,($x+$x_width)+3,($y-$y_ht),$white); // fill it
   	/*
	   echo "X=$x, ";
	   echo "Y=$y, ";
	   echo "X2=".($x+$x_width) .", ";
	   echo "Y2=".round($y-$y_ht)."\n<br>";
	 */
   	imagestring( $im,2,$x-1,$y+1,$day_names[$i],$black);
   	imagestring( $im,2,$x-1,$y+10,humanReadable($data[$i]),$black);
   	$x += ($x_width+$pad);   // $pad is diff between two bars;
}
// use values from above to draw the word "Day" at the end
// subtract $pad+5 from the padding above for the diff between two bars
$x=round(((($x - $pad) + 5) / $width) * $width);
imagestring($im,3,$x,$y,"Day",$black);

imagejpeg( $im, "graph.jpeg", 90);
imagedestroy($im);
echo "<img src='graph.jpeg'><p></p>";

	function humanReadable($val,$thousands=0){
	   	if($val>=1000)
		   	$val=humanReadable($val/1000,++$thousands);
	   	else{
		   	$unit=array('','K','M','T','P','E','Z','Y');
		   	$val=round($val,2).$unit[$thousands];
	   	}
	   	return $val;
   	}
?>

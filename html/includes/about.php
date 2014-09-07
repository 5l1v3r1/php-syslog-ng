<?php
// Copyright (C) 2005 Claus Lund, clauslund@gmail.com
$basePath = dirname( __FILE__ );
require_once ($basePath . "/../includes/html_header.php");
require_once ($basePath . "/../includes/version.php");
?>
<body>
<table class="header">
<tr><td>
	<a href="index.php"><h2 class="logo"><?php echo $version?></h2></a>
</td><td class="headerright">
</td></tr></table>
<table class="headerbottom"><tr><td>
</table>
<table class="pagecontent">
<tr><td><span class="longtext">
<h3 class="title">Overview</h3>
Php-Syslog-ng is a front-end for viewing syslog-ng messages logged to MySQL in real-time. It lets you quickly and easily manage logs from many hosts. It features customized searches based on host, facility, priority, datetime and the content of the log messages. It also has a tail mode and allows for customized filters that enable you to monitor your systmes in near real-time. The latest version of Php-Syslog-ng requires MySQL 5.0 or later for full functionality. Older versions of MySQL are still supported but the functionality will be somewhat limited. Any recent version of syslog-ng, Apache and PHP should work.

<h3 class="title">License</h3>
Php-Syslog-ng is licensed under the terms of the <a href="http://www.gnu.org/copyleft/gpl.html">GNU Public License (GPL)</a> Version 2 as published by the Free Software Foundation. This gives you legal permission to copy, distribute and/or modify Php-Syslog-ng under certain conditions. Read the 'LICENSE' file in the Php-Syslog-ng distribution or read the online version of the license for more details.<br />
Php-Syslog-ng is provided AS IS with NO WARRANTY OF ANY KIND, INCLUDING THE WARRANTY OF DESIGN, MERCHANTABILITY, AND FITNESS FOR A PARTICULAR PURPOSE.

<h3 class="title">History</h3>
<?php
// Get a file into an array.  In this example we'll go through HTTP to get
// the HTML source of a URL.
$lines = file($basePath . "/../CHANGELOG");

// Loop through our array, show HTML source as HTML source; and line numbers too.
foreach ($lines as $line) {
	    echo htmlspecialchars($line) . "<br />\n";
}
?>
<hr>
<b>All Changes below are PC (Pre-Clayton :-))<br></b></b>
<hr>
2002-07-31 - Michael Earls releases version 0.1 of Php-Syslog-ng. 
More information can be found at http://www.vermeer.org/projects/Php-Syslog-ng and http://sourceforge.net/projects/Php-Syslog-ng/ <i>But please be aware that these version are old and no longer maintained.</i>

<p>
Michael Earls steadily improved on Php-Syslog-ng until his last release (2.5.1) in the summer of 2004.

<p>
After that I think the main contributor was Jason Taylor (<a href="http://deathstar.com/PhpSyslogNG">http://deathstar.com/PhpSyslogNG</a>). The development since Michael Earls' last release can be followed on the mailing list on sourceforge (<a href="http://sourceforge.net/mailarchive/forum.php?forum=Php-Syslog-ng-support">http://sourceforge.net/mailarchive/forum.php?forum=Php-Syslog-ng-support</a>). (Note: The Sourceforge list is in the process of being deprecated in favor of <a href="http://code.google.com/p/php-syslog-ng">Google's Code Repository</a>

<p>
Php-Syslog-ng May 2005 (2.5.3)<br />
I had been looking at this project and others like it for a while and I needed to implement something like this at work. That gave me the excuse to finally take a good look at the code for Php-Syslog-ng to see if it matched my requirements. I downloaded Jason Taylor's 20050422 release and started to dig in. It quickly became apparent that the code could use some clean-up work before I would start to add any new features. The clean-up was a little more work than I had first hoped but after a couple of days I had some code that I thought was work-able. I then started adding/fixing some of the things I immediately wanted done. The most significant change is that MySQL 4.0 is now required if you want full functionality (earlier versions are still supported with limited functionality). I don't know how well my approach to calculating the total number of results works in a very busy environment but I believe that it will be OK. This is something I will keep an eye on as I implement this system where I work.<br> (note from cdukes: I don't know who wrote this?)

<p>
Php-Syslog-ng May 2005 (2.5.4)<br />
User authentication, support for multiple tables with log data and improved log rotation capabilities ... those are the main changes in this version. I also fixed a couple of bugs, added input validation for must user supplied values and further cleaned up and commented the code.<br />
Enjoy! (note from cdukes: I don't know who wrote this?)

<p>
Php-Syslog-ng June 2005 (2.6)<br />
Three new features/changes:<br />
1) Most of the work has been done towards supporting other databases. The base functionality can be ported to any database by just editing a few database related functions in common_funcs.php.<br />
2) A cache function has been implemented to remove/minimize the delay when loading the search page.<br />
3) Basic access controls have been implemented. They currently only affect the configure page but I plan on expanding it to also allow for restrictions on what searches a user can make (what hosts, timeframe etc).<br />
In addition to the new stuff I also fixed a few minor bugs. (note from cdukes: I don't know who wrote this?)

<p>
Php-Syslog-ng June 2005 (2.7)<br />
This release only has bug fixes. See the changelog for the list of changes.<br />
If you are using the merge table then you need to use MySQL 4.0.18 or later. (note from cdukes: I don't know who wrote this?)

<p>
Php-Syslog-ng July 2005 (2.8)<br />
A quick bug fix to remedy a problem in logrotate.php. (note from cdukes: I don't know who wrote this?)

<p>
This latest version of Php-Syslog-ng is available at <a href="http://code.google.com/p/php-syslog-ng">http://code.google.com/p/php-syslog-ng</a>.
</span></td></tr></table>

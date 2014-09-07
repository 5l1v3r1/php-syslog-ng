<?php
/*
 * version.php
 *
 * Developed by Clayton Dukes <cdukes@cdukes.com>
 * Copyright (c) 2006 http://www.gdd.net
 * Licensed under terms of GNU General Public License.
 * All rights reserved.
 *
 * Changelog:
 * 2006-06-14 - created
 *
 */

/* $Platon$ */

/* Modeline for ViM {{{
 * vim: set ts=4:
 * vim600: fdm=marker fdl=0 fdc=0:
 * }}} */

/** Version information */
class version {
        /** @var string Product */
        var $PRODUCT = 'Php-Syslog-NG';
        /** @var int Main Release Level */
        var $RELEASE = '2.9';
        /** @var string Development Status */
        var $DEV_STATUS = 'Possibly Stable';
        /** @var int Sub Release Level */
        var $DEV_LEVEL = '8l';
        /** @var string Codename */
        var $CODENAME = 'cdukes';
        /** @var string Date */
        var $RELDATE = '2-Mar-2009';
        /** @var string Time */
        var $RELTIME = '17:56';
        /** @var string Timezone */
        var $RELTZ = 'EST';
        /** @var string Copyright Text */
        var $COPYRIGHT = 'Copyright 2009 -  All rights reserved.';
        /** @var string URL */
        var $URL = '<a href="http://php-syslog-ng.gdd.net">Php-Syslog-NG</a> is Free Software released under the GNU/GPL License.';
}
$_VERSION =& new version();

$version = $_VERSION->PRODUCT .' '. $_VERSION->RELEASE .'.'. $_VERSION->DEV_LEVEL .' ' . $_VERSION->DEV_STATUS .' [ '.$_VERSION->CODENAME .' ] '. $_VERSION->RELDATE .' ' . $_VERSION->RELTIME .' '. $_VERSION->RELTZ;
?>

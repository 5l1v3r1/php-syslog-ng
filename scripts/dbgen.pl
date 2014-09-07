#!/usr/bin/perl

#
# dbgen.pl
#
# Developed by Clayton Dukes <cdukes@cdukes.com>
# Copyright (c) 2006 
# Licensed under terms of GNU General Public License.
# All rights reserved.
#
# Changelog:
# 2006-04-14 - created
#

# $Platon$

$| = 1;
# Note - requires:
# Digest::SHA1
# and
# Net-Mysql
use Net::MySQL;
use POSIX qw(strftime);
use File::Basename;
use strict;
my $sleeptime = (1 + rand(5)); # Set sleep time as random or as an integer
#my $sleeptime = ".0001"; # Use this to just blast a whole bunch into the database

# Change below to match your config path (use full path if you run this from cron)
my $ngconfig = "/www/php-syslog-ng/html/config/config.php"; 

open( CONFIG, $ngconfig ) or die "Can't open $ngconfig : $!"; 
my @config = <CONFIG>; 
close( CONFIG );

my($table,$dbadmin,$dbpw,$dbname,$dbhost,$dbport,$DEBUG);
foreach my $var (@config) {
	next unless $var =~ /^define/; # read only def's
	$table = $1 if ($var =~ /'DEFAULTLOGTABLE', '(\w+)'/);
	$dbadmin = $1 if ($var =~ /'DBADMIN', '(\w+)'/);
	$dbpw = $1 if ($var =~ /'DBADMINPW', '(\w+)'/);
	$dbname = $1 if ($var =~ /'DBNAME', '(\w+)'/);
	$dbhost = $1 if ($var =~ /'DBHOST', '(\w+)'/);
	$dbport = $1 if ($var =~ /'DBPORT', '(\w+)'/);
	$DEBUG = $1 if ($var =~ /'DEBUG', '(\w+)'/);
}
if ( ! $table ) {
	print "Error: Unable to read config variables from $ngconfig\n";
	exit;
}
if ($DEBUG > 0) { 
	print "Table: $table\n";
	print "Adminuser: $dbadmin\n";
	print "PW: $dbpw\n";
	print "DB: $dbname\n";
	print "DB Host: $dbhost\n";
	print "DB Port: $dbport\n";
#exit;
}

### our fake stuff
our %myevents = (
	'pagp' => [
	'%AAA-3-ACCT_IOMEM_LOW: Line protocol on Interface FastEthernet0/5, changed state to up',
	'%AAA-3-IPILLEGALMSG: Line protocol on Interface FastEthernet0/7, changed state to up',
	'%FWSM-5-111008: User \'\'2g456\'\' executed the \'\'exit\'\' command',
	'%AAAA-3-ACCTFORKFAIL:Configured from console by vty0 (192.168.4.35)',
	'%AAAA-3-BADSTATE:Attempted to connect to RSHELL from 10.15.10.121',
	'%AAA-3-BADHDL:Authentication failure for SNMP req from host 10.6.1.36',
	],
	'mls' => [
	'%AAAA-4-NOSERVER: IP Multilayer switching is enabled',
	'%C4K_HWACLMAN-4-CAMAUDIT: Netflow Data Export disabled',
	'%FWSM-5-111008: User \'\'2g456\'\' executed the \'\'exit\'\' command',
	'%NCDP-5-UNABLETOREACHCL: Duplicate address 10.10.2.2 on Vlan20',
	'%NEVADA-0-BADADD: Duplicate address 10.10.7.2 on Vlan70',
	],
	'sys' => [
	'%FABRIC-5-FABRIC_MODULE_ACTIVE: Module 1 is online',
	'%FABRIC-5-FABRIC_MODULE_ACTIVE: Module 3 is online',
	'%FWSM-5-111008: User \'\'2g456\'\' executed the \'\'exit\'\' command',
	'%C6KENV-2-FANUPGREQ: Fan 1 had a rotation error reported.',
	'%C6KENV-2-FANUPGREQ: Fan 1 had earlier reported a rotation error. It is ok now',
	'%LINK-3-BADENCAP: FastEthernet0/11 link down/up 5 times per min',
	'%LINK-3-BADENCAP: FastEthernet0/12 link down/up 5 times per min',
	'%LINK-3-BADENCAP: FastEthernet0/15 link down/up 5 times per min',
	]
);

our %mynames = (
	'sys' => [
	'%AAA-1-AAA_SESSION_LIMIT_REJECT:',
	'%AAA-2-AAAMULTILINKERROR:',
	'%AAA-2-AAA_NVRAM_UPGRADE_FAILURE',
	'%AAA-2-AAA_PROGRAM_EXIT:',
	'%AAA-2-FORKFAIL:',
	'%AAA-3-AAA_NVRAMFAILURE:',
	'%AAA-3-ACCT_IOMEM_LOW:',
	'%AAA-3-ACCT_LOW_MEM_TRASH:',
	'%AAA-3-ACCT_LOW_MEM_UID_FAIL:',
	'%AAA-3-ATTRFORMATERR:',
	'%AAAA-3-BADLIST:',
	'%AAAA-3-BADREG:',
	'%AAAA-3-BADSTATE:',
	'%AAAA-3-BADSTR:',
	'%AAAA-3-DLRFORKFAIL:',
	'%AAAA-3-DROPACCTFULLQ:',
	'%AAAA-3-DROPACCTLOWMEM:',
	'%AAAA-3-DROPACCTSNDFAIL:',
	'%AAAA-3-ILLEGALNAME:',
	'%AAAA-3-ILLSGNAME:',
	'%AAAA-3-INTERNAL_ERROR:',
	'%APBR-4-PRTR_ARP_IPV6_ERR:',
	'%APBR-4-PRTR_ARP_IP_BAD:',
	'%APBR-4-PRTR_ARP_PROT_BAD:',
	'%APBR-4-PRTR_DHCP_XID_EXP:',
	],
	'mls' => [
	'%AAAA-3-INVALIDLIST:',
	'%ACLMGR-2-NOVLB:',
	'%ACLMGR-2-NOVMR:',
	'%ACLMGR-3-ACLTCAMFULL:',
	'%ACLMGR-3-AUGMENTFAIL:',
	'%ACLMGR-3-IECPORTLABELERROR:',
	'%ACLMGR-3-INSERTFAIL:',
	'%APBR-4-DNS_CON_FAILED:',
	'%APBR-4-EAP_AUTH_FAILED:',
	'%APBR-4-EAP_SRV_VRF_FAILED:',
	'%APBR-4-EAP_TOUT:',
	'%APBR-4-FWDTBL_GIVE_FAILED:',
	'%APBR-4-FWDTBL_TAKE_FAILED:',
	'%APBR-4-FWDTBL_UNKNW_HOST:',
	'%APBR-4-LOSS_ETHERNET_ACTION:',
	'%APBR-4-MACAUTH_CON_FAIL:',
	'%APBR-4-MACAUTH_DENY:',
	'%APBR-4-MACAUTH_NORESP:',
	'%APBR-4-NET_ADMSTAT_SET_FAILED:',
	'%APBR-4-PRO80211_MNGPKT_RCV_ERR1',
	'%APBR-4-PRO80211_MNGPKT_RCV_ERR2',
	'%APBR-4-PRO80211_MNGPKT_TRNC:',
	'%APBR-4-PRO80211_PKTALLOC_ERR:',
	'%APBR-4-PRO80211_PKTBSS_ERR:',
	'%APBR-4-PRO80211_RCV_INV_CTRL:',
	'%APBR-4-PRO80211_RCV_INV_PORT:',
	'%APBR-4-PRO80211_RCV_TRNC_CTRL:',
	'%APBR-4-PRO80211_SND_ERROR:',
	'%APBR-4-PRTR_ADDR_ERR:',
	'%APBR-4-PRTR_ARP_FMT_BAD:',
	'%MLS-5-MLSENABLED:',
	'%MLS-5-NDEDISABLED:',
	],
	'pagp' => [
	'%ACLMGR-3-INTTABLE:',
	'%ACLMGR-3-MAXRECURSION:',
	'%APBR-0-SYS_REBOOT_CFG_RESETALL:',
	'%APBR-0-SYS_REBOOT_CFG_RSTR:',
	'%APBR-0-SYS_REBOOT_EBUF:',
	'%APBR-0-SYS_REBOOT_FWDTSIZE:',
	'%APBR-0-SYS_REBOOT_FW_UPD:',
	'%APBR-0-SYS_REBOOT_HW_ADDR:',
	'%APBR-1-RCV_PCKT_ALERT:',
	'%APBR-4-BKP_INF_PTR_ERR:',
	'%APBR-4-BKR_KEY1_BADSIZE:',
	'%APBR-4-BKR_KEY1_NORADIOCTRL:',
	'%APBR-4-BKR_KEY1_NORADIOINF:',
	'%APBR-4-BKR_KEY1_NOT_INIT:',
	'%APBR-4-BKR_MANY_RADIOS:',
	'%APBR-4-BKR_NOT_INIT:',
	'%APBR-4-BKR_VLAN_DISABLED:',
	'%APBR-4-BKR_VLAN_NOT_INIT:',
	'%APBR-4-BRLP_SADDR_PKT:',
	'%APBR-4-DDP_RCV_PKT_ERR:',
	]
);

our @facilities = ("kern", "user", "mail", "daemon", "auth", "syslog", "lpr", "news", "uucp", "cron", "authpriv", "ftp", "local0", "local1", "local2", "local3", "local4", "local5", "local6");
our @priorities = ("debug", "info", "notice", "warning", "err", "crit", "alert", "emerg");

my $c = 0;
our @devicelist; 
until ($c > 50) {

	# cdukes - 2-28-08: Modified sections below to generate fewer random hostnames, it was a bit overkill :-)
	#my @devs = ("router","switch","server","firewall");
	my @devs = ("router","switch","server","fw","css","www","ftp","sun","linux","as400","6509","2811","1701","2911");
	my @chars = ( 'a' .. 'z', 'A' .. 'Z', 0 .. 9 ); 
	#my @nums = ( '0' .. '9'); 
	my @nums = ( '0' .. '6'); 
	my $num = join '', map $nums[ rand @nums ], 0 .. 0; 
	my $string1 = join '', map $devs[ rand @devs ], 1 ; 
	my $string2 = join '', map $chars[ rand @chars ], 1 .. 3;

	#push(@devicelist, "$string1-$num-$string2");
	push(@devicelist, "$string1-$num");
	$c++;
}

if ($DEBUG > 0) { 
	my $DEBUG = "true"; 
	print "DEBUG: $DEBUG\n";
} else { 
	my $DEBUG = ""; 
	print "Debug off, showing only inserted data...\n";
}
my $mysql = Net::MySQL->new(
	debug		=> "$DEBUG",		# enable debug
	unixsocket	=> "$dbport",	# Default use UNIX socket
	hostname	=> "$dbhost",   
	database	=> "$dbname",
	user		=> "$dbadmin",
	password	=> "$dbpw",
);

### main ###

my $i = 0;
my $total = 1500;
# Set Loopcount
#while (1) # runs in a continuous loop
until ($i > $total)
{
	my	@evtypes = ('sys', 'mls', 'pagp');

	foreach my $eventtype (@evtypes)
	{
		my $index   = rand @priorities;
		my $pri = $priorities[$index];  
		my $index   = rand @priorities;
		my $lvl = $priorities[$index];  
		my $index   = rand @facilities;
		my $fac = $facilities[$index];  
		my $index   = rand @devicelist;
		my $devlist = $devicelist[$index];  
		my $curr_fakeevent = get_rand_event ($eventtype);

		my $YMDHMS = strftime "%Y-%m-%d %H:%M:%S", localtime;
		my $HOST	 = "$devlist";
		my $FACILITY = "$fac";
		my $PRIORITY = "$pri";
		my $LEVEL	 = "$lvl";
		my $TAG	 = "Tag";
		my $PRG	 = "DBGen";
		my $MSG		 = "$curr_fakeevent";
		$mysql->query(qq{
			INSERT INTO $table (host, facility, priority, level, tag, datetime, program, msg)
			VALUES ( '$HOST', '$FACILITY', '$PRIORITY', '$LEVEL', '$TAG', '$YMDHMS', '$PRG', '$PRG: $MSG' )
			});
		print "\n\nHost: $HOST\nFacility: $FACILITY\nPriority: $PRIORITY\nLevel: $LEVEL\nTag: $TAG\nYMDHMS: $YMDHMS\nProgram: $PRG\nMessage: $PRG: $MSG \n";
		printf "Affected row: %d\n", $mysql->get_affected_rows_length;
		sleep $sleeptime;
	}
	print "Loopcount: $i of $total";
	$i++;
}

### our subs ###

sub get_rand_event
{
	my $evttype = shift;
	$evttype = "sys" if ((!defined($evttype)) or ($evttype eq ""));
	my @namesarr = @{$mynames{$evttype}};
	my @msgarr = @{$myevents{$evttype}};
	return ( $namesarr[rand($#namesarr+1)], $msgarr[rand($#msgarr+1)] );
}



# vim: ts=4
# vim600: fdm=marker fdl=0 fdc=3

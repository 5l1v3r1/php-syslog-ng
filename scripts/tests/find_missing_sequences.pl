#!/usr/bin/perl

###########################################################################################
#
# This file will look for missing sequence numbers in the syslog.logs database.
# I used it to find missing sequence numbers when inserting large amounts of test 
# data from the "loggen" program in the scripts directory.
# Created 2-23-07 by cdukes
###########################################################################################

local $\ = "\n";
use DBI;

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
	$DEBUG = 1;
}
if ( ! $table ) {
	print "Error: Unable to read config variables from $ngconfig";
	exit;
}

# Connect to DB
$dbh = DBI->connect("dbi:mysql:$dbname","$dbadmin","$dbpw")
	or die "Connection Error: $DBI::errstr";

# Prepare SELECT statement
$sql = "SELECT seq FROM $table WHERE tag=26 ORDER BY seq ASC";
$sth = $dbh->prepare($sql);

# Execute statement
$sth->execute
	or die "SQL Error: $DBI::errstr";

# Push results into an array
while (@row = $sth->fetchrow_array) {
	push(@seqs, @row);
}

if ($#seqs > 0) {
	print "\n\tSearching for gaps in $#seqs total sequence numbers...";
# I stole most of the great code below from:
# http://forums.devshed.com/perl-programming-6/find-missing-sequence-and-show-them-in-continuous-order-496037.html
	my $missing = join ',', map find_gap( $seqs[$_-1], $seqs[$_] ), 1 .. $#seqs;
	if ($missing) {
		print "The following sequence numbers are missing from the \"$table\" table: $missing";
	} else {
		print "\tNo missing sequences found in the \"$table\" table.";
	} 
}
else {
	print "\tQuery returned no results: $sql";
}

sub find_gap {
	$_[1]-$_[0]-1?$_[1]-$_[0]-2?($_[0]+1).'-'.($_[1]-1):$_[1]-1:();
} 

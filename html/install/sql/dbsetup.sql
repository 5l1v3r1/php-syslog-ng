CREATE TABLE logs (
	id bigint unsigned NOT NULL AUTO_INCREMENT,
	host varchar(128) default NULL,
	facility varchar(10) default NULL,
	priority varchar(10) default NULL,
	level varchar(10) default NULL,
	tag varchar(10) default NULL,
	datetime datetime default NULL,
	program varchar(15) default NULL,
	msg text,
	seq bigint unsigned NOT NULL default '0',
	counter int(11) NOT NULL default '1',
	fo datetime default NULL,
	lo datetime default NULL,
	PRIMARY KEY  (id),
	KEY datetime (datetime),
	KEY sequence (seq),
	KEY priority (priority),
	KEY facility (facility),
	KEY program (program),
	KEY host (host)
) ENGINE=MyISAM;


CREATE TABLE users (
username varchar(32) default NULL,
pwhash char(40) default NULL,
sessionid char(32) default NULL,
exptime datetime default NULL,
PRIMARY KEY (username)
) TYPE=MyISAM;

CREATE TABLE search_cache (
tablename varchar(32) DEFAULT NULL,
type ENUM('HOST','FACILITY','PROGRAM','LPD'),
value varchar(128) DEFAULT NULL,
updatetime datetime DEFAULT NULL,
INDEX type_name (type, tablename)
) TYPE=MEMORY;

CREATE TABLE user_access (
username varchar(32) DEFAULT NULL,
actionname varchar(32) DEFAULT NULL,
access ENUM('TRUE','FALSE'),
INDEX user_action (username, actionname)
) TYPE=MyISAM;

INSERT INTO user_access VALUES ('admin','add_user','TRUE'),('admin','edit_user','TRUE'),('admin','reload_cache','TRUE'),('admin','edit_acl','TRUE'),('admin','add_server','TRUE'),('admin','chg_auth','TRUE'),('admin','del_server','TRUE'); 

CREATE TABLE actions (
actionname varchar(32) NOT NULL,
actiondescr varchar(64) DEFAULT NULL,
defaultaccess ENUM('TRUE','FALSE'),
PRIMARY KEY (actionname)
) TYPE=MyISAM;
--
-- Table structure for table cemdb
--

CREATE TABLE cemdb (
id int(5) unsigned NOT NULL auto_increment,
name varchar(128) NOT NULL default '',
message text,
explanation text,
action text,
datetime datetime default NULL,
PRIMARY KEY  (id),
UNIQUE KEY name (name)
) ENGINE=MyISAM  COMMENT='Cisco Error Message Database';

INSERT INTO actions (actionname, actiondescr, defaultaccess) VALUES ('add_user', 'Add users', 'TRUE');
INSERT INTO actions (actionname, actiondescr, defaultaccess) VALUES ('edit_user', 'Edit users (delete and change password)', 'TRUE');
INSERT INTO actions (actionname, actiondescr, defaultaccess) VALUES ('reload_cache', 'Reload search cache', 'TRUE');
INSERT INTO actions (actionname, actiondescr, defaultaccess) VALUES ('edit_acl', 'Edit access control settings', 'TRUE');


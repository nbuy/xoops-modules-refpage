#
# XOOPS 2.0 or later refpage SQL schema
#
# $Id: mysql.sql,v 1.6 2010/01/31 06:04:36 nobu Exp $

# --------------------------------------------------------

#
# master of tracking 
#   handling from URI to track_id and so-on.
#

CREATE TABLE refpage (
  track_id int(10) unsigned NOT NULL auto_increment,
  track_uri varchar(255) NOT NULL default '',
  since int(10) unsigned NOT NULL default '0',
  disable int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (track_id),
  KEY track_id (track_uri)
);
# --------------------------------------------------------

#
# referer of tracking 
#

CREATE TABLE refpage_ref (
  ref_id int(10) unsigned NOT NULL auto_increment,
  since int(10) unsigned NOT NULL default '0',
  track_from int(10) unsigned NOT NULL,
  ref_url varchar(255) NOT NULL default '',
  title   varchar(255) NOT NULL default '',
  context text,
  nref   int(10) unsigned NOT NULL default '0',
  mtime  int(10) unsigned NOT NULL default '0',
  linked  int(1) unsigned NOT NULL default '0',
  checked int(1) unsigned NOT NULL default '0',
  PRIMARY KEY  (ref_id),
  KEY ref_id (ref_url)
);
# --------------------------------------------------------

#
# check for reload soon
#

CREATE TABLE refpage_log (
  log_id int(10) unsigned NOT NULL auto_increment,
  atime  int(10) unsigned NOT NULL default '0',
  tfrom int(10) unsigned,
  rfrom int(10) unsigned,
  ip    varchar(15) NOT NULL default '',
  PRIMARY KEY  (log_id)
);

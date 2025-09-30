# CocoaMySQL dump
# Version 0.5
# http://cocoamysql.sourceforge.net
#
# Host: db65a.pair.com (MySQL 4.0.18-log)
# Database: alicia2_UHID
# Generation Time: 2005-09-29 11:33:28 -0400
# ************************************************************

# Dump of table media
# ------------------------------------------------------------
DROP TABLE IF EXISTS `media`;


CREATE TABLE `media` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `active` int(1) unsigned NOT NULL default '1',
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `object` int(10) unsigned default NULL,
  `weight` float default NULL,
  `rank` int(10) unsigned default NULL,
  `type` varchar(10) NOT NULL default 'jpg',
  `caption` blob,
  `metadata` text,
  PRIMARY KEY  (`id`)
);



# Dump of table objects
# ------------------------------------------------------------
DROP TABLE IF EXISTS `objects`;
CREATE TABLE `objects` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `active` int(1) unsigned NOT NULL default '1',
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `rank` int(10) unsigned default NULL,
  `name1` tinytext,
  `name2` tinytext,
  `address1` text,
  `address2` text,
  `city` tinytext,
  `state` tinytext,
  `zip` tinytext,
  `country` tinytext,
  `phone` tinytext,
  `fax` tinytext,
  `url` tinytext,
  `email` tinytext,
  `begin` datetime default NULL,
  `end` datetime default NULL,
  `date` datetime default NULL,
  `head` tinytext,
  `deck` mediumblob,
  `body` mediumblob,
  `notes` mediumblob,
  PRIMARY KEY  (`id`)
);



# Dump of table wires
# ------------------------------------------------------------
DROP TABLE IF EXISTS `wires`;
CREATE TABLE `wires` (
  `id` int(10) unsigned NOT NULL auto_increment,
  `active` int(1) unsigned NOT NULL default '1',
  `created` datetime default NULL,
  `modified` datetime default NULL,
  `fromid` int(10) unsigned default NULL,
  `toid` int(10) unsigned default NULL,
  `weight` float NOT NULL default '1',
  `notes` blob,
  PRIMARY KEY  (`id`)
);




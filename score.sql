#
# Table structure for table `cities`
#

CREATE TABLE `cities` (
  `id` mediumint(9) unsigned NOT NULL auto_increment,
  `region_id` smallint(6) unsigned NOT NULL default '0',
  `R` mediumint(9) unsigned default NULL,
  `C` mediumint(9) unsigned default NULL,
  `I` mediumint(9) unsigned default NULL,
  `pop` mediumint(9) unsigned default NULL,
  `name` varchar(255) NOT NULL default '',
  `locX` tinyint(4) unsigned NOT NULL default '0',
  `locY` tinyint(4) unsigned NOT NULL default '0',
  `sizeX` tinyint(4) unsigned NOT NULL default '0',
  `sizeY` tinyint(4) unsigned NOT NULL default '0',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `mayor_id` mediumint(9) unsigned default NULL,
  `checkout` datetime default NULL,
  `money` int(11) default NULL,
  `last_mayor_id` mediumint(9) default NULL,
  `requested_mayor_id` mediumint(8) unsigned default NULL,
  `file_size` mediumint(8) unsigned default NULL,
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `city_log`
#

CREATE TABLE `city_log` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `city_id` mediumint(8) unsigned NOT NULL default '0',
  `text` text,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `messages`
#

CREATE TABLE `messages` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `type` varchar(32) NOT NULL default '',
  `relation_id` mediumint(8) unsigned NOT NULL default '0',
  `text` text,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `regions`
#

CREATE TABLE `regions` (
  `id` smallint(6) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL default '',
  `total_pop` mediumint(9) unsigned default NULL,
  `total_R` mediumint(9) unsigned default NULL,
  `total_C` mediumint(9) unsigned default NULL,
  `total_I` mediumint(9) unsigned default NULL,
  `vrestrict` varchar(5) NOT NULL default '0',
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `modified` datetime NOT NULL default '0000-00-00 00:00:00',
  `imagemap` text,
  `complete_region_dl` varchar(4) NOT NULL default 'on',
  `checkout_req_auth` varchar(4) NOT NULL default 'off',
  `checkout_user_limit` tinyint(3) unsigned NOT NULL default '0',
  `checkout_timelimit` tinyint(3) unsigned NOT NULL default '0',
  `total_money` int(11) default NULL,
  `description` text,
  `cityloc_check` varchar(4) NOT NULL default 'on',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `score_sys`
#

CREATE TABLE `score_sys` (
  `name_id` varchar(16) NOT NULL default 'score_sy',
  `PUBLIC_REG` varchar(4) NOT NULL default 'on',
  `USE_ZIP` varchar(4) NOT NULL default 'on',
  `SC4PATH` varchar(255) NOT NULL default 'Regions',
  `SC4IMG_PATH` varchar(255) NOT NULL default 'Images',
  `SC4REGION_IMG_WIDTH` varchar(6) NOT NULL default '800',
  `SC4VERSIONS` varchar(255) NOT NULL default 'a:3:{s:16:"Rush/Hour Deluxe";s:4:"1.13";s:16:"SimCity Original";s:3:"1.9";s:11:"Any Version";s:1:"0";}',
  `ADMIN_EMAIL` varchar(255) NOT NULL default '',
  `ADMIN_NAME` varchar(255) NOT NULL default '',
  `last_maintenance` datetime NOT NULL default '0000-00-00 00:00:00',
  `idle_account_limit` tinyint(3) unsigned NOT NULL default '0',
  `idle_account_limit_warn` tinyint(3) unsigned NOT NULL default '0',
  `SMTP_HOST` varchar(255) default NULL,
  `SMTP_PASS` varchar(255) default NULL,
  `SMTP_USER` varchar(255) default NULL,
  PRIMARY KEY  (`name_id`)
) TYPE=MyISAM;

# --------------------------------------------------------

#
# Table structure for table `users`
#

CREATE TABLE `users` (
  `id` mediumint(11) unsigned NOT NULL auto_increment,
  `login` varchar(255) NOT NULL default '',
  `pass` varchar(255) NOT NULL default '',
  `email` varchar(255) NOT NULL default '',
  `privileges` tinyint(9) unsigned NOT NULL default '0',
  `flagged_idle` datetime default NULL,
  `lastlogin` datetime default NULL,
  `lastlastlogin` datetime default NULL,
  `created` datetime NOT NULL default '0000-00-00 00:00:00',
  `email_messages` varchar(4) NOT NULL default 'on',
  PRIMARY KEY  (`id`)
) TYPE=MyISAM;
    



#
# Setup Data
#
INSERT INTO `score_sys` (`name_id`, `PUBLIC_REG`, `USE_ZIP`, `SC4PATH`, `SC4IMG_PATH`, `SC4REGION_IMG_WIDTH`, `SC4VERSIONS`, `ADMIN_EMAIL`, `ADMIN_NAME`, `last_maintenance`, `idle_account_limit`, `idle_account_limit_warn`, `SMTP_HOST`, `SMTP_PASS`, `SMTP_USER`) VALUES ('score_sys', 'on', 'on', 'Regions', 'Images', '99%', 'a:3:{s:16:"Rush/Hour Deluxe";s:4:"1.13";s:16:"SimCity Original";s:3:"1.9";s:11:"Any Version";s:1:"0";}', 'admin@localhost', 'admin', now(), 1, 5, '', NULL, NULL);
INSERT INTO `users` (`id`, `login`, `pass`, `email`, `privileges`, `flagged_idle`, `lastlogin`, `created`, `email_messages`) VALUES (1, 'admin', 'admin', 'admin@localhost', 150, NULL, NULL, now(), 'on');
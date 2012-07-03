CREATE TABLE `#__admin_alerts` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `at` varchar(255) NOT NULL COMMENT 'Where does it happen?',
  `when` varchar(255) NOT NULL COMMENT 'What was going on when it happened?',
  `tip` varchar(255) NOT NULL COMMENT 'Your tip to administrator',
  `times` smallint(5) unsigned NOT NULL default '1',
  `last_time` int(10) unsigned NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


CREATE TABLE `#__admin_menu` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `pic` varchar(255) NOT NULL default 'package.png' COMMENT 'This value will be passed to imageset() function. Put a "#" sign at the beginning to ignore imageset() function.',
  `parent` mediumint(8) unsigned NOT NULL default '0',
  `order` mediumint(9) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=33 ;


CREATE TABLE `#__admin_menu_map` (
  `package` varchar(255) NOT NULL COMMENT 'Package which created this menu entry',
  `menuid` mediumint(8) unsigned NOT NULL COMMENT 'Menu entry ID'
) ENGINE=MyISAM;


CREATE TABLE `#__blogcategories` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `sef_alias` varchar(255) NOT NULL,
  `desc` mediumtext NOT NULL,
  `parent` mediumint(8) unsigned NOT NULL default '0',
  `accmask` varchar(255) NOT NULL COMMENT 'Access mask by usergroups. Indicates which groups can access this category.',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=2 ;


CREATE TABLE `#__blogposts` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `sef_alias` varchar(255) NOT NULL,
  `introcontent` mediumtext NOT NULL,
  `morecontent` mediumtext NOT NULL,
  `enabled` tinyint(1) NOT NULL default '0',
  `denied` varchar(255) NOT NULL COMMENT 'Denied usergroups from accessing this entry.',
  `blogid` mediumint(8) unsigned NOT NULL COMMENT 'Post Category ID',
  `added_by` mediumint(8) unsigned NOT NULL COMMENT 'Author User ID',
  `added_time` datetime NOT NULL,
  `mod_by` mediumint(8) unsigned default NULL COMMENT 'Modifier User ID',
  `mod_time` datetime default NULL,
  `pub_time` datetime NOT NULL,
  `unpub_time` datetime default NULL,
  `hits` mediumint(8) unsigned NOT NULL default '0',
  `rating` mediumint(8) unsigned NOT NULL default '0' COMMENT 'Sum of all rates',
  `rate_count` mediumint(8) unsigned NOT NULL default '0' COMMENT 'Rate times',
  `tags` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=2 ;


CREATE TABLE `#__blog_attachments` (
  `postid` mediumint(8) unsigned NOT NULL COMMENT 'ID of owner post',
  `name` varchar(255) NOT NULL COMMENT 'Attachment display name',
  `url` varchar(255) NOT NULL COMMENT 'File URL',
  KEY `postid` (`postid`)
) ENGINE=MyISAM;


CREATE TABLE `#__blog_comments` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `postid` mediumint(8) unsigned NOT NULL COMMENT 'ID of owner post',
  `replyto` mediumint(8) unsigned NOT NULL default '0' COMMENT 'ID of comment which is getting replied by this entry.',
  `added_by` mediumint(8) unsigned NOT NULL COMMENT 'Author User ID',
  `added_time` datetime NOT NULL,
  `language` mediumint(8) unsigned NOT NULL COMMENT 'Comment language ID',
  `published` tinyint(1) NOT NULL default '0',
  `points` mediumint(9) NOT NULL default '0' COMMENT 'Comment score',
  `author` varchar(255) default NULL,
  `authormail` varchar(255) default NULL,
  `authorweb` varchar(255) default NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;

CREATE TABLE IF NOT EXISTS `#__bruteforce_blocker` (
  `name` varchar(20) NOT NULL,
  `ip` varchar(40) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `requests` tinyint(2) unsigned NOT NULL,
  `timeout` tinyint(2) unsigned NOT NULL default '5',
  `req_timeout` smallint(3) unsigned NOT NULL default '300',
  `req_max` tinyint(2) unsigned NOT NULL default '0'
) ENGINE=MyISAM COMMENT='Used to log requests to block brute force and DoS attacks.';

CREATE TABLE `#__cache_cleaning` (
  `table` varchar(255) NOT NULL COMMENT 'The table which was affected.',
  `cache_name` varchar(255) NOT NULL COMMENT 'Which cache item stores this table contents?',
  `cached_fields` varchar(255) NOT NULL COMMENT 'Which columns are cached there?',
  `ext_unique` varchar(32) NOT NULL COMMENT 'Used to store unique id of every installed extension to remove row on uninstalling it.'
) ENGINE=MyISAM COMMENT='Maps contents of cached items to tables of DB.';


CREATE TABLE `#__crons` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `cron` varchar(50) NOT NULL COMMENT 'Cron name',
  `enabled` tinyint(1) NOT NULL default '1',
  `runloop` int(10) unsigned NOT NULL COMMENT 'Time between each execution. In other words, delta of execution times.',
  `nextrun` int(10) unsigned NOT NULL COMMENT 'Next run time',
  `lastrun` int(10) unsigned default NULL COMMENT 'Last run time',
  `core` tinyint(1) NOT NULL default '0' COMMENT 'Indicates that is this a core extension or not.',
  PRIMARY KEY  (`id`),
  KEY `cron` (`cron`)
) ENGINE=MyISAM  AUTO_INCREMENT=3 ;


CREATE TABLE `#__cronslog` (
  `cron` mediumint(8) unsigned NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `text` varchar(255) NOT NULL
) ENGINE=MyISAM;


CREATE TABLE `#__domains` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `address` varchar(255) NOT NULL,
  `params` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `address` (`address`),
  UNIQUE KEY `params` (`params`)
) ENGINE=MyISAM  AUTO_INCREMENT=1;


CREATE TABLE `#__extensions_info` (
  `client` enum('admin','site','*') NOT NULL,
  `extype` enum('cron','imageset','language','module','package','plugin','template','webservice','widget','library') NOT NULL,
  `extname` varchar(50) NOT NULL,
  `version` varchar(50) NOT NULL,
  `core` tinyint(1) NOT NULL default '0'
) ENGINE=MyISAM;


CREATE TABLE `#__imagesets` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `designer` varchar(255) default NULL,
  `client` enum('admin','site') NOT NULL default 'site',
  `core` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  AUTO_INCREMENT=3 ;


CREATE TABLE `#__installation_logs` (
  `title` varchar(255) NOT NULL,
  `name` varchar(50) NOT NULL,
  `extype` enum('cron','imageset','language','module','package','plugin','template','webservice','widget','library') NOT NULL,
  `client` enum('site','admin','*') NOT NULL,
  `version` varchar(50) NOT NULL,
  `time` int(10) unsigned NOT NULL,
  `action` enum('install','uninstall','update') NOT NULL
) ENGINE=MyISAM COMMENT='Contains log of extensions (un)installed or updated.';


CREATE TABLE `#__languages` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `designer` varchar(255) default NULL,
  `client` enum('admin','site') NOT NULL default 'site',
  `core` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  AUTO_INCREMENT=5 ;


CREATE TABLE `#__languages_translations` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `language` mediumint(8) unsigned NOT NULL COMMENT 'ID of Language',
  `group` varchar(255) NOT NULL COMMENT 'Content group name',
  `row_id` mediumint(8) unsigned NOT NULL COMMENT 'ID of original content item in it''s table.',
  `row_field` varchar(255) NOT NULL COMMENT 'Name of the column which will be replaced.',
  `value` mediumtext NOT NULL COMMENT 'The replacement value for column.',
  `original_md5_checksum` varchar(32) NOT NULL COMMENT 'Checksum of original value to identify changed sources.',
  `mod_by` mediumint(8) unsigned NOT NULL,
  `mod_time` datetime NOT NULL,
  `enabled` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


CREATE TABLE `#__links` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `type` enum('inner','outer','default') NOT NULL default 'inner',
  `group` mediumint(8) unsigned NOT NULL,
  `enabled` tinyint(1) NOT NULL default '1',
  `denied` varchar(255) NOT NULL,
  `newwin` tinyint(1) NOT NULL default '0' COMMENT 'Indicates that should this link open in new window or not.',
  `order` smallint(6) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=9 ;


CREATE TABLE `#__link_groups` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=3 ;


CREATE TABLE `#__modules` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `order` smallint(6) NOT NULL,
  `location` varchar(50) NOT NULL COMMENT 'This Module should be rendered in which template location?',
  `enabled` tinyint(1) NOT NULL default '0',
  `showat` varchar(255) NOT NULL COMMENT 'Defines packages which when they run, this module should (not) be rendered.',
  `denied` varchar(255) NOT NULL,
  `module` varchar(50) NOT NULL,
  `content` mediumtext NOT NULL,
  `showtitle` tinyint(1) NOT NULL default '1',
  `core` tinyint(1) NOT NULL default '0',
  `client` enum('admin','site') NOT NULL default 'site',
  PRIMARY KEY  (`id`),
  KEY `module` (`module`)
) ENGINE=MyISAM  AUTO_INCREMENT=18 ;


CREATE TABLE `#__openid_assocs` (
  `server_url` varchar(255) NOT NULL,
  `handle` varchar(255) NOT NULL,
  `secret` varchar(255) NOT NULL,
  `issued` int(11) NOT NULL,
  `lifetime` int(11) NOT NULL,
  `type` varchar(32) NOT NULL,
  PRIMARY KEY  (`handle`)
) ENGINE=MyISAM;


CREATE TABLE `#__openid_map` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `userid` mediumint(8) unsigned NOT NULL,
  `server_url` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


CREATE TABLE `#__openid_nonces` (
  `server_url` varchar(255) NOT NULL,
  `timestamp` int(11) NOT NULL,
  `salt` varchar(40) NOT NULL
) ENGINE=MyISAM;


CREATE TABLE `#__packages` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `core` tinyint(1) NOT NULL default '0',
  `enabled` tinyint(1) NOT NULL default '1',
  `denied` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  AUTO_INCREMENT=20 ;


CREATE TABLE `#__pages` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `sef_alias` varchar(255) NOT NULL,
  `desc` text NOT NULL,
  `tags` varchar(255) NOT NULL,
  `is_dynamic` tinyint(1) NOT NULL default '0',
  `content` mediumtext NOT NULL COMMENT 'Contains page contents if it is not dynamic.',
  `mods` varchar(255) NOT NULL COMMENT 'Indicates that which modules should (not) be rendered when this page is opened.',
  `enabled` tinyint(1) NOT NULL default '0',
  `denied` varchar(255) NOT NULL,
  `added_by` mediumint(8) unsigned NOT NULL,
  `params` varchar(255) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


CREATE TABLE `#__pages_widgets` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `widget` mediumint(8) unsigned NOT NULL COMMENT 'ID of this widget''s renderer extension (look table #__pages_widgets_resource).',
  `pageid` mediumint(8) unsigned NOT NULL COMMENT 'ID of page which this widget belongs to.',
  `params` text NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


CREATE TABLE `#__pages_widgets_resource` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `filename` varchar(50) NOT NULL COMMENT 'The Widget Name.',
  `core` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=8 ;


CREATE TABLE `#__plugins` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `plugin` varchar(50) NOT NULL,
  `group` varchar(50) NOT NULL,
  `order` smallint(6) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `denied` varchar(255) NOT NULL,
  `core` tinyint(1) NOT NULL default '0',
  `client` enum('admin', 'site', '*') NOT NULL default 'site' COMMENT 'You can use "*" to include plugin in both clients.',
  PRIMARY KEY  (`id`),
  KEY `plugin` (`plugin`)
) ENGINE=MyISAM  AUTO_INCREMENT=14 ;


CREATE TABLE `#__quicklink` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `link` varchar(255) NOT NULL,
  `alt` varchar(255) default NULL,
  `img` varchar(255) NOT NULL default 'package.png' COMMENT 'This value will be passed to imageset() function. Put a "#" sign at the beginning to ignore imageset() function.',
  `acckey` varchar(25) default NULL COMMENT 'Shortcut key combination for this link.',
  `order` smallint(6) NOT NULL,
  PRIMARY KEY  (`id`)
) ENGINE=MyISAM  AUTO_INCREMENT=11 ;


CREATE TABLE `#__sessions` (
  `session_id` varchar(32) NOT NULL COMMENT 'Session Identifier',
  `session_cookievar` varchar(32) NOT NULL COMMENT 'Session cookie name of this user',
  `time` int(10) unsigned NOT NULL COMMENT 'Last activity timestamp',
  `client` enum('admin','site') NOT NULL default 'site',
  `userid` mediumint(8) unsigned default NULL COMMENT 'User ID if this is not a guest user.',
  `ip` varchar(40) NOT NULL COMMENT 'IP Address of user.',
  `agent` varchar(255) NOT NULL COMMENT 'User agent value',
  `data` mediumtext COMMENT 'Session data if session_type is set to db.',
  `position` varchar(255) NOT NULL COMMENT 'User''s pathway',
  PRIMARY KEY  (`session_id`)
) ENGINE=MyISAM;


CREATE TABLE `#__settings` (
  `var` varchar(255) NOT NULL COMMENT 'Setting variable name',
  `value` mediumtext COMMENT 'Value of the var encoded with serialize() PHP function.',
  `default` mediumtext COMMENT 'Default value of the var encoded with serialize() PHP function.',
  `extype` enum('cron','imageset','language','module','package','plugin','template','webservice','widget','library') NOT NULL COMMENT 'Owner extension''s type.',
  `extname` varchar(50) NOT NULL COMMENT 'Owner extension''s name.',
  `client` enum('admin','site','*') NOT NULL default 'site' COMMENT 'Owner extension''s client.',
  `vartype` enum('custom','date','calendar','users','usergroups','packages','modules','plugins','languages','templates','bool','radio','select','text','textbox','imagesets') NOT NULL COMMENT 'Variable type. Used to render controls for modifying value of this var. It will be passed to ArtaTagsHtml::PreFormItem() function.',
  `vartypedata` mediumtext COMMENT 'Variable type description. It will be passed to ArtaTagsHtml::PreFormItem() function as vartype descriptor code.',
  `check` mediumtext COMMENT 'Piece of PHP code which runs when storing new value to check input.',
  KEY `var` (`var`)
) ENGINE=MyISAM;


CREATE TABLE `#__templates` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `designer` varchar(255) default NULL,
  `client` enum('admin','site') NOT NULL default 'site',
  `core` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  AUTO_INCREMENT=3 ;


CREATE TABLE `#__userfields` (
  `var` varchar(255) NOT NULL,
  `default` mediumtext,
  `extype` enum('cron','imageset','language','module','package','plugin','template','webservice','widget','library') NOT NULL COMMENT '"library" extype can be used for library parameters like time offset.',
  `extname` varchar(50) NOT NULL,
  `vartype` enum('custom','date','calendar','users','usergroups','packages','modules','plugins','languages','templates','bool','radio','select','text','textbox','imagesets') NOT NULL,
  `vartypedata` mediumtext,
  `check` mediumtext,
  `viewcode` mediumtext COMMENT 'Piece of PHP code used to render misc fields on user profile page.',
  `fieldtype` enum('setting','misc') NOT NULL,
  `show_on_register` tinyint(1) NOT NULL default '0' COMMENT 'Should this value be asked on user registeration page?',
  KEY `var` (`var`)
) ENGINE=MyISAM;


CREATE TABLE `#__usergroupperms` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `default` mediumtext,
  `extname` varchar(50) NOT NULL,
  `extype` enum('cron','imageset','language','module','package','plugin','template','webservice','widget','library') NOT NULL,
  `client` enum('admin','site') NOT NULL default 'site',
  `vartype` enum('custom','date','calendar','users','usergroups','packages','modules','plugins','languages','templates','bool','radio','select','text','textbox','imagesets') NOT NULL default 'bool',
  `vartypedata` mediumtext,
  `check` mediumtext,
  PRIMARY KEY  (`id`),
  KEY `name` (`name`)
) ENGINE=MyISAM  AUTO_INCREMENT=93 ;


CREATE TABLE `#__usergroupperms_value` (
  `usergroupperm` mediumint(8) unsigned NOT NULL,
  `usergroup` mediumint(8) unsigned NOT NULL,
  `value` text NOT NULL,
  KEY `usergroupperm` (`usergroupperm`)
) ENGINE=MyISAM;


CREATE TABLE `#__usergroups` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL,
  `title` varchar(255) NOT NULL,
  `active` tinyint(1) NOT NULL default '1',
  PRIMARY KEY  (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  AUTO_INCREMENT=3 ;

CREATE TABLE `#__users` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `name` varchar(255) NOT NULL COMMENT 'User''s real name.',
  `username` varchar(255) NOT NULL COMMENT 'User''s nick name.',
  `email` varchar(255) NOT NULL,
  `password` varchar(32) NOT NULL,
  `usergroup` mediumint(8) unsigned NOT NULL,
  `ban` tinyint(1) NOT NULL default '0',
  `ban_reason` varchar(255) default NULL,
  `register_date` datetime NOT NULL,
  `lastvisit_date` datetime default NULL,
  `activation` varchar(32) NOT NULL default '0',
  `avatar` varchar(20) NOT NULL,
  `settings` text,
  `misc` text,
  PRIMARY KEY  (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=MyISAM  AUTO_INCREMENT={$RAND} ;


CREATE TABLE `#__usertext` (
  `uid` mediumint(8) unsigned NOT NULL,
  `text` mediumtext NOT NULL,
  `modified` datetime NOT NULL,
  PRIMARY KEY  (`uid`)
) ENGINE=MyISAM;


CREATE TABLE `#__user_visitormessages` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `title` varchar(255) NOT NULL,
  `content` mediumtext NOT NULL,
  `for` mediumint(8) unsigned NOT NULL,
  `by` mediumint(8) unsigned NOT NULL,
  `added_time` datetime NOT NULL,
  `checkedout` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `for` (`for`)
) ENGINE=MyISAM AUTO_INCREMENT=1 ;


CREATE TABLE `#__webservices` (
  `id` mediumint(8) unsigned NOT NULL auto_increment,
  `webservice` varchar(50) NOT NULL,
  `title` varchar(255) NOT NULL,
  `enabled` tinyint(1) NOT NULL,
  `core` tinyint(1) NOT NULL default '0',
  PRIMARY KEY  (`id`),
  KEY `webservice` (`webservice`)
) ENGINE=MyISAM  AUTO_INCREMENT=3 ;

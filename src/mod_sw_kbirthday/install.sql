  -- @package Joomla.Module
-- @subpackage mod_sw_kbirthday
-- @copyright Copyright (C) 2010-2013 Sven Schultschik. All rights reserved.
-- @license GNU General Public License version 2 or later
-- @link http://www.schultschik.de


CREATE TABLE IF NOT EXISTS `#__sw_kbirthday` (
  `uid` int(11) NOT NULL,
  `date` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  KEY `uid` (`uid`)
);

-- @since 1.9.0
CREATE TABLE IF NOT EXISTS `#__schuweb_birthday_message` (
  `userid` int(11) NOT NULL DEFAULT '0',
  `topicid` int(11) NOT NULL,
  PRIMARY KEY (`userid`)
);

-- @since 2.0.0
CREATE TABLE IF NOT EXISTS `#__schuweb_birthday` (
  `userid` int(11) NOT NULL,
  `daystill` smallint(11) unsigned NOT NULL,
  `age` tinyint(11) unsigned NOT NULL,
  `birthdate` date NOT NULL,
  `correction` tinyint(4) NOT NULL,
  `calcdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);
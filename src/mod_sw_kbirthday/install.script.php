<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

defined('_JEXEC') or die();

use Joomla\CMS\Factory;
use Joomla\CMS\Installer\InstallerScript;

class mod_sw_kbirthdayInstallerScript extends InstallerScript
{
    public function __construct($parent)
    {
        // Define the minumum versions to be supported.
        $this->minimumJoomla = '4.0';
        $this->minimumPhp = '7.4';
    }

    /**
     * Called before any type of action
     *
     * @param   string $route Which action is happening (install|uninstall|discover_install|update)
     * @param   JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function preflight($route, $parent)
    {
        $db = Factory::getDbo();

        if (version_compare($this->getParam('version'), '1.9.0') < 0) {
            $db->setQuery("CREATE TABLE IF NOT EXISTS `#__schuweb_birthday_message` ( `userid` int(11) NOT NULL DEFAULT '0', `topicid` int(11) NOT NULL, PRIMARY KEY (`userid`));");
            $db->execute();
        }

        if (version_compare($this->getParam('version'), '2.0.0') < 0) {
            $db->setQuery("CREATE TABLE IF NOT EXISTS `#__schuweb_birthday` (
  `userid` int(11) NOT NULL,
  `daystill` smallint(11) unsigned NOT NULL,
  `age` tinyint(11) unsigned NOT NULL,
  `birthdate` date NOT NULL,
  `correction` tinyint(4) NOT NULL,
  `calcdate` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP
);");
            $db->execute();
        }
    }
}
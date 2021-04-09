<?php
/**
 * @package SW KBirthday Module
 *
 * @Copyright (C) 2010-2013 Schultschik Websolution All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking

defined('_JEXEC') or die();

class mod_sw_kbirthdayInstallerScript
{
    /**
     * Constructor
     *
     * @param   JAdapterInstance $adapter The object responsible for running this script
     */
    public function __construct($parent)
    {

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
        $db = JFactory::getDbo();

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

    /**
     * Called after any type of action
     *
     * @param   string $route Which action is happening (install|uninstall|discover_install|update)
     * @param   JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function postflight($route, $parent)
    {

    }

    /**
     * Called on installation
     *
     * @param   JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function install($parent)
    {

    }

    /**
     * Called on update
     *
     * @param   JAdapterInstance $adapter The object responsible for running this script
     *
     * @return  boolean  True on success
     */
    public function update($parent)
    {
    }

    /**
     * Called on uninstallation
     *
     * @param   JAdapterInstance $adapter The object responsible for running this script
     */
    public function uninstall($parent)
    {

    }

    function getParam($name)
    {
        $db = JFactory::getDbo();
        $db->setQuery('SELECT manifest_cache FROM #__extensions WHERE name = "mod_sw_kbirthday"');
        $manifest = json_decode($db->loadResult(), true);
        return $manifest[$name];
    }
}
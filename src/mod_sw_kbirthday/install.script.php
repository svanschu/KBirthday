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
        if (!parent::preflight($route, $parent)) return false;

        $db = Factory::getDbo();

        $manifest = $this->getItemArray('manifest_cache', '#__extensions', 'name', $this->extension);

        // Check whether we have an old release installed and skip this check when this here is the initial install.
        if (!isset($manifest['version'])) {
            $manifest = $this->getItemArray('manifest_cache', '#__extensions', 'name', 'mod_sw_kbirthday');
            if (!isset($manifest['version'])) {
                $manifest = $this->getItemArray('manifest_cache', '#__extensions', 'name', 'SCHUWEB_BIRTHDAY');
                if (!isset($manifest['version'])) {
                    return true;
                } else {
                    // not needed if foldername and extension name are the same
                   $this->extension = 'mod_sw_kbirthday';
                }
            } else {
                // not needed if foldername and extension name are the same
                $this->extension = 'mod_sw_kbirthday';
            }
        }

        $oldRelease = $manifest['version'];

        if (version_compare($oldRelease, '2.0.0') < 0) {
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

        if (version_compare($oldRelease, '3.1.0') <= 0) {
            $ids = $this->getInstances(true);

            foreach ($ids as $id) {
                $connection = $this->getParam('connection', $id);
                
                if (strcmp($connection, 'Forum') == 0) {
                    $param = array( 'connection' => 'Kunena');
                    $this->setParams($param, 'edit', $id);
                }
            }
        }

        $this->extension = 'mod_SCHUWEB_BIRTHDAY';
    }
}
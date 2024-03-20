<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

namespace SchuWeb\Module\Birthday\Site\Helper\Integration;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;

/**
 * Class IntegrationHelper
 * Abstract class for integration of different extensions
 *
 * @abstract
 * @since 2.0.0
 */
abstract class IntegrationHelper
{
    protected $params = null;

    /**
     * Database Object
     * 
     * @var     DatabaseDriver
     * @since  __BUMP_VERSION__
     */
    protected $db;

    function __construct($params)
    {
        $this->params = new Registry($params);

        $this->db = Factory::getContainer()->get(DatabaseInterface::class);
    }

    /**
     * returns an array of fields to get the birthdates
     *
     * @abstract
     * @return mixed
     * @since 2.0.0
     */
    abstract public function getBirthdayDatabaseFields();

    /**
     * returns the Avatar
     *
     * @abstract
     * @param $user
     * @return mixed
     * @since 2.0.0
     */
    abstract public function getAvatar($user);

    /**
     * Returns the link to the user profile link
     *
     * @abstract
     * @param $user
     * @return mixed
     */
    abstract public function getProfileLink($user);

    /**
     * returns the username
     *
     * @abstract
     * @param $userId user array or userId
     * @return mixed
     */
    abstract public function getUserName($userId);

    /**
     * return Instance of the needed integration object
     *
     * @param $params
     * @return mixed
     * @since 2.0.0
     */
    static public function getInstance($params)
    {
        //get the birthday list with connection links
        $class = "SchuWeb\Module\Birthday\Site\Helper\Integration\\{$params->get('integration')}Helper";
        return new $class($params);
    }
}
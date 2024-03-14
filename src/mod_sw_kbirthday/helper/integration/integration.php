<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

defined('_JEXEC') or die();

/**
 * Class SWBirthdayIntegration
 * Abstract class for integration of different extensions
 *
 * @abstract
 * @since 2.0.0
 */
abstract class SWBirthdayIntegration
{

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
        require_once(dirname(__FILE__) .'/' . strtolower($params->get('integration')) . '.php');
        $class = "SWBirthdayIntegration{$params->get('integration')}";
        return new $class($params);
    }
}
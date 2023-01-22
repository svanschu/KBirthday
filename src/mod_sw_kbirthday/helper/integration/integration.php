<?php
/**
 * @package             SchuWeb Birthday
 *
 * @version             sw.build.version
 * @author              Sven Schultschik
 * @copyright (C)       2010 - 2023 Sven Schultschik. All rights reserved
 * @license             http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link                http://www.schultschik.de
 **/

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
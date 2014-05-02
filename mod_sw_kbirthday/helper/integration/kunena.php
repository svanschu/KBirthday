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

/**
 * Class SWBirthdayIntegrationKunena
 * Kunena integration class
 *
 * @since 2.0.0
 */
class SWBirthdayIntegrationKunena extends SWBirthdayIntegration
{
    function __construct($params) {


        $this->integration = $params->get('k20integration');
    }

    /**
     * returns an array of fields to get the birthdates
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getBirthdayDatabaseFields()
    {
        $birthdayFields = array();

        $db = JFactory::getDbo();

        if ($this->integration === 'jomsocial') {
            $birthdayFields['birthdate'] = 'value';
            $birthdayFields['fromtable'] = '#__community_fields_values';
            $birthdayFields['jomsocial'] = ' AND a.field_id = 3 ';
            $birthdayFields['userid'] = 'user_id';
        } elseif ($this->integration === 'communitybuilder') {
            //get the list of user birthdays
            $cbfield = $this->params->get('swkbcbfield', 'cb_birthday');
            $birthdayFields['birthdate'] = $db->escape($cbfield);
            $birthdayFields['fromtable'] = '#__comprofiler';
            $birthdayFields['userid'] = 'id';
        } else {
            $birthdayFields['birthdate'] = 'birthdate';
            $birthdayFields['fromtable'] = '#__kunena_users';
            $birthdayFields['userid'] = 'userid';
        }

        if (KunenaFactory::getConfig()->username == 0)
            $birthdayFields['$order'] = 'name';
        else
            $birthdayFields['$order'] = 'username';

        return $birthdayFields;
    }

    /**
     * returns the Avatar
     *
     * @param $user
     * @return mixed
     * @since 2.0.0
     */
    public function getAvatar($user)
    {
        if (class_exists('KunenaForum')) {
            return KunenaFactory::getUser($user)->getAvatarImage();
        } else {
            return;
        }
    }

    /**
     * Returns the link to the user profile link
     *
     * @abstract
     * @param $user
     * @return mixed
     */
    public function getProfileLink($user)
    {
        return KunenaFactory::getUser($user['userid'])->getLink();
    }

    /**
     * returns the username
     *
     * @abstract
     * @param $userId user array or userId
     * @return mixed
     */
    public function getUserName($userId){

        if (is_array($userId)) {
            $userId = $userId['userid'];
        }

        return KunenaFactory::getUser($userId)->getName();
    }
}
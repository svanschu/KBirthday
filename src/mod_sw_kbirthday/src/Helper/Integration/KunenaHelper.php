<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

namespace SchuWeb\Module\Birthday\Site\Helper\Integration;

use Kunena\Forum\Libraries\Factory\KunenaFactory;

\defined('_JEXEC') or die;

/**
 * Class KunenaHelper
 * Kunena integration class
 *
 * @since 2.0.0
 */
class KunenaHelper extends IntegrationHelper
{
    /**
     * returns an array of fields to get the birthdates
     *
     * @return mixed
     * @since 2.0.0
     */
    public function getBirthdayDatabaseFields()
    {
        $birthdayFields = array();

        $integration = $this->params->get('k20integration');

        if ($integration === 'jomsocial') {
            $birthdayFields['birthdate'] = 'value';
            $birthdayFields['fromtable'] = '#__community_fields_values';
            $birthdayFields['jomsocial'] = ' AND a.field_id = 3 ';
            $birthdayFields['userid'] = 'user_id';
        } elseif ($integration === 'communitybuilder') {
            //get the list of user birthdays
            $cbfield = $this->params->get('swkbcbfield', 'cb_birthday');
            $birthdayFields['birthdate'] = $this->db->escape($cbfield);
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
        $width = $this->params->get('avatarWidth');
        $height = $this->params->get('avatarHeight');

        if (empty($width)) {
            $width = 'thumb';
        }

        if (empty($height)) {
            $height = 90;
        }

        if (class_exists('KunenaForum')) {
            return KunenaFactory::getUser($user)->getAvatarImage('', $width, $height);
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
    public function getUserName($userId)
    {

        if (is_array($userId)) {
            $userId = $userId['userid'];
        }

        return KunenaFactory::getUser($userId)->getName();
    }
}
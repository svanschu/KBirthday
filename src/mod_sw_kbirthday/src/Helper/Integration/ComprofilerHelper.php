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

use Joomla\CMS\Language\Text;
use CBLib\Application\Application as CBApplication;

include_once(JPATH_ROOT . '/administrator/components/com_comprofiler/plugin.foundation.php');

/**
 * Class ComprofilerHelper
 * CommunityBuilder integration class
 *
 * @since 2.0.0
 */
class ComprofilerHelper extends IntegrationHelper
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

        //get the list of user birthdays
        $cbfield = $this->params->get('swkbcbfield', 'cb_birthday');
        $birthdayFields['birthdate'] = $this->db->escape($cbfield);
        $birthdayFields['fromtable'] = '#__comprofiler';
        $birthdayFields['userid'] = 'id';

        $config = CBApplication::Config();
        switch ($config->get('name_format')) {
            case 1:
            case 2:
            case 5:
            case 6:
            case 7:
            case 8:
            case 9:
            case 10:
            case 11:
                $birthdayFields['$order'] = 'name';
                break;
            case 3:
            case 4:
            default:
                $birthdayFields['$order'] = 'username';
        }

        return $birthdayFields;
    }

    /**
     * returns the Avatar
     *
     * @param $userId
     * @internal param $user
     * @return mixed
     * @since 2.0.0
     */
    public function getAvatar($userId)
    {
        $width = $this->params->get('avatarWidth');
        $height = $this->params->get('avatarHeight');

        $style = '';

        if (!empty($width)) {
            $style = 'width:' . $width . 'px';
        }

        if (!empty($height)) {
            if (!empty($style)) {
                $style .= ';';
            }
            $style .= 'height:' . $height . 'px';
        }

        if (!empty($style)) {
            $style = 'style=' . $style;
        }

        $user = \CBuser::getInstance($userId);
        $avatar = $user->getField('avatar', null, 'csv', 'none', 'list');
        return '<img src="' . $avatar . '" alt="' . Text::sprintf('SWBIRTHDAY_AVATAR_TITLE', $this->getUserName($userId)) . '" ' . $style . ' />';
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
        $userName = $this->getUserName($user);

        if ($user['userid'] && ($user['userid'] == CBApplication::MyUser()->getUserId())) {
            $user['userid'] = null;
        }

        global $_CB_framework;

        $link = $_CB_framework->userProfileUrl((int) $user['userid']);

        $title = Text::sprintf('SWBIRTHDAY_USER_LINK_TITLE', $userName);

        return "<a href=\"{$link}\" title=\"{$title}\" rel=\"nofollow\">{$userName}</a>";
    }

    /**
     * returns the username
     *
     * @abstract
     * @param $userId user array or userId
     * @internal param $user
     * @return mixed
     */
    public function getUserName($userId)
    {
        if (is_array($userId)) {
            $userId = $userId['userid'];
        }

        $user = \CBuser::getUserDataInstance($userId);
        
        return $user->getFormattedName();
    }
}
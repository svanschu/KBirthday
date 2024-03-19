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
use Joomla\CMS\Language\Text;
use Joomla\Registry\Registry;
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

    private $params = null;

    function __construct($params)
    {
        $this->params = new Registry($params);
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

        $db = Factory::getDbo();

        //get the list of user birthdays
        $cbfield = $this->params->get('swkbcbfield', 'cb_birthday');
        $birthdayFields['birthdate'] = $db->escape($cbfield);
        $birthdayFields['fromtable'] = '#__comprofiler';
        $birthdayFields['userid'] = 'id';

        $config = CBApplication::Config();
        switch ($config["name_format"]) {
            case 1:
            case 2:
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

        $user = CBuser::getInstance($userId);
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
        $link = CBApplication::CBFramework()->cbSef('index.php?option=com_comprofiler' . ($user['userid'] ? '&task=userprofile&user=' . (int)$user['userid'] : ''), true, 'html');

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

        $user = \CBuser::getInstance($userId);

        $config = CBApplication::Config();
        switch ($config["name_format"]) {
            case 1:
            case 2:
                $name = $user->getUserData()->name;
                break;
            case 3:
            case 4:
            default:
                $name = $user->getUserData()->username;
        }
        return $name;
    }
}
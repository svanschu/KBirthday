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

require_once( JPATH_ROOT . '/components/com_community/libraries/core.php' );

/**
 * Class SWBirthdayIntegrationKunena
 * Kunena integration class
 *
 * @since 2.0.0
 */
class SWBirthdayIntegrationJomsocial extends SWBirthdayIntegration
{

    private $params = null;

    function __construct($params)
    {
        $this->params = $params;
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

        //get the list of user birthdays
        $birthdayFields['birthdate'] = 'value';
        $birthdayFields['fromtable'] = '#__community_fields_values';
        $birthdayFields['jomsocial'] = ' AND a.field_id = 3 ';
        $birthdayFields['userid'] = 'user_id';
        $birthdayFields['$order'] = 'name';

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

        $avatar = CFactory::getUser($userId)->getThumbAvatar();

        return '<img src="' . $avatar . '" alt="' . JText::sprintf('SWBIRTHDAY_AVATAR_TITLE', $this->getUserName($userId)) . '" ' . $style . ' />';
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
        $userName = CFactory::getUser($user['userid'])->getDisplayName();

        $link = CRoute::_('index.php?option=com_community&view=profile&userid=' . (int)$user['userid']);

        $title = JText::sprintf('SWBIRTHDAY_USER_LINK_TITLE', $userName);

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

        return CFactory::getUser($userId)->getDisplayName();
    }
}
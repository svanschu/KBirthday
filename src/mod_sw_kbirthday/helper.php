<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

use Joomla\CMS\Factory;

defined('_JEXEC') or die();

require_once(dirname(__FILE__) . '/helper/integration/integration.php');

abstract class ModSWKbirthdayHelper
{
    /**
     * @since 1.7.0
     * @param $params
     */
    function __construct($params)
    {
        $this->app = JFactory::getApplication();
        $this->uri = JURI::getInstance();
        $this->params = $params;
        //get the date today
        $config = JFactory::getConfig();
        $this->soffset = $config->get('offset');
        $this->time_now = new JDate('now', $this->soffset);

        $this->integration = SWBirthdayIntegration::getInstance($params);
    }

    static function loadHelper($params)
    {
        //get the birthday list with connection links
        $class = "ModSWKbirthdayHelper{$params->get('connection')}";
        $bday = new $class($params);
        return $bday->getUserBirthday();
    }

    /**
     * @since 1.6.0
     * @return list of users
     */
    private function getBirthdayUser()
    {
        switch ($this->params->get('calcinterval')) {
            case 'oneperday':
                return $this->calcBirthdays();
                break;
            case 'eachtime':
            default:
                return $this->getBirthdayData();
                break;
        }

    }

    /**
     * adds the link for the connect param
     * @since 1.7.3
     * @param  $user pass-by-reference
     * @return void
     */
    public abstract function getUserLink(& $user);

    protected function getGraphicDate($date)
    {
        $ret = '<p class="swkb_calendar">'
            . $date->format('j')
            . ' <em>'
            . $date->format('M')
            . '</em></p>';
        return $ret;
    }

    /**
     * Get the subject of/for the forum post
     * @since 1.7.0
     * @param $username
     * @return string subject
     */
    protected function getSubject($username)
    {
        if ($this->params->get('activatelanguage') == 'yes') {
            $lang = $this->params->get('subjectlanguage');
            if (empty($lang)) {
                $this->app->enqueueMessage(JText::_('SCHUWEB_BIRTHDAY_LANGUAGE_NOSUBJECT'), 'error');
                return;
            }
            $subject = self::getWantedLangString($lang, 'SCHUWEB_BIRTHDAY_SUBJECT', $username);
        } else {
            $conf = JFactory::getConfig();
            $subject = self::getWantedLangString($conf->get('language'), 'SCHUWEB_BIRTHDAY_SUBJECT', $username);
        }
        return $subject;
    }

    protected function getMessage($username)
    {
        if ($this->params->get('activatelanguage') == 'yes') {
            $lang = $this->params->get('messagelanguage');
            if (empty($lang)) {
                $this->app->enqueueMessage(JText::_('SCHUWEB_BIRTHDAY_LANGUAGE_NOMESSAGE'), 'error');
                return;
            }
            $langa = explode(",", $lang);
            foreach ($langa as $value) {
                $value = trim($value);
                $marray[] = self::getWantedLangString($value, 'SCHUWEB_BIRTHDAY_MESSAGE', $username);
            }
            $message = implode('\n\n', $marray);
        } else {
            $conf = JFactory::getConfig();
            $message = self::getWantedLangString($conf->get('language'), 'SCHUWEB_BIRTHDAY_MESSAGE', $username);
        }
        return $message;
    }

    /**
     * Get strings for multi language support
     * @since 1.7.0
     * @param $lang the needed language in ISO format xx-XX
     * @param $arg which argument should be trabslated
     * @param $username insert into translated string
     * @return string
     */
    private function getWantedLangString($lang, $arg, $username)
    {
        jimport('joomla.filesystem.file');
        $exist = JFile::exists(JPATH_BASE . '/language/' . $lang . '/' . $lang . '.mod_sw_kbirthday.ini');
        if ($exist == FALSE) {
            $this->app->enqueueMessage(JText::sprintf('SCHUWEB_BIRTHDAY_LANGUAGE_NOTEXIST', $lang), 'error');
            return;
        }
        $language = JLanguage::getInstance($lang);
        $language->load('mod_sw_kbirthday');
        $string = $language->_($arg);
        $string = sprintf($string, $username);
        return $string;
    }

    /**
     * Add date to sring
     * @param $user pass-by-refernce
     * @return void
     * @since 1.7.0
     */
    private function addDate(& $user)
    {
        $bdate = $user['birthdate']->format($this->params->get('dateform'), true);
        $user['date'] = JText::sprintf('SCHUWEB_BIRTHDAY_DATE', $bdate);
    }

    /**
     * Add number of days till birthdate and language string
     * @param  $tillstring pass-by-refernce
     * @return void
     * @since 1.6.0
     */
    private function addDaysTill(& $tillstring)
    {
        if (empty($tillstring['till']) || $tillstring['till'] == 0) {
            if ($this->params->get('todaygraphic') === 'graphic') {
                $doc = JFactory::getDocument();
                $style = '.swkb_today{
					background: url("' . $this->uri->base() . '/media/mod_sw_kbirthday/img/birthday16x16.png") no-repeat center top transparent scroll;
					height: 16px;
					width: 16px;
					display: inline-block;}';
                $doc->addStyleDeclaration($style);
                $tillstring['day_string'] = '<span class="swkb_today"> </span> ';
            } else
                $tillstring['day_string'] = JText::_('SCHUWEB_BIRTHDAY_TODAY');
        } elseif ($tillstring['till'] == 1)
            $tillstring['day_string'] = JText::sprintf('SCHUWEB_BIRTHDAY_DAY', $tillstring['till']);
        else
            $tillstring['day_string'] = JText::sprintf('SCHUWEB_BIRTHDAY_DAYS', $tillstring['till']);
    }

    /*
           * Get the list of the user who have birthday in next days
           * @since 1.6.0
           * @return Array
           */
    function getUserBirthday()
    {
        $list = self::getBirthdayUser();
        $dage = $this->params->get('displayage');
        $ddate = $this->params->get('displaydate');
        $avatar = $this->params->get('displayavatar');
        $graphicdate = $this->params->get('graphicdate');
        if ($graphicdate === 'graphic') {
            $doc = JFactory::getDocument();
            $doc->addStyleSheet($this->uri->base() . '/modules/mod_sw_kbirthday/css/calendar.css');
        }
        $tgraphic = '';
        if ($this->params->get('todaygraphic') === 'graphic')
            $tgraphic = '_GRAPHIC';
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                if ($this->hideUser($v) === true) {
                    unset($list[$k]);
                } else {
                    $this->addDaysTill($v);
                    $this->getUserLink($v);
                    //Show Avatar?
                    if ($avatar)
                        $list[$k]['avatar'] = $this->integration->getAvatar($v['userid']);
                    //Should we display the age?
                    if ($dage)
                        $v['age'] = JText::sprintf('SCHUWEB_BIRTHDAY_ADD_AGE', $v['age']);
                    else
                        $v['age'] = '';
                    //Should we display the date?
                    $v['date'] = $graphic = '';
                    if ($ddate && $graphicdate === 'text')
                        self::addDate($v);
                    elseif ($ddate && $graphicdate === 'graphic')
                        $graphic = self::getGraphicDate($v['birthdate']);
                    $list[$k]['link'] = $graphic . '<span>' . JText::sprintf('SCHUWEB_BIRTHDAY_HAVEBIRTHDAYIN' . $tgraphic, $v['link'], $v['day_string'], $v['age'], $v['date']) . '</span>';
                }
            }
        }
        return $list;
    }

    private function hideUser($user)
    {
        $hideUser = $this->params->get('hideuser');
        if (!empty($hideUser)) {
            $users = explode(',', $hideUser);
            $users = $users ? $users : array();

            foreach ($users as $uid) {
                if ($uid == $user['userid']) {
                    return true;
                };
            }
        }

        if ($this->params->get('includeAll', 1) != 1) {
            $userGroups = JUserHelper::getUserGroups($user['userid']);
            $includeUserGroups = $this->params->get('usergrouplist', array());
            $res = array_diff($includeUserGroups, $userGroups);

            if (count($includeUserGroups) == count($res)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Get the information from all users which have birthday in a defined space of time.
     *
     * @return mixed|string
     */
    private function getBirthdayData()
    {
        $db = Factory::getDBO();
        $query = $db->getQuery(true);
        $query->select('b.username');
        $query->select('b.name');
        $query->select('b.id AS userid');
        $query->select('b.email');
        $jomsocial = '';

        $birthdayFields = $this->integration->getBirthdayDatabaseFields();

        $query->select('YEAR(a.' . $birthdayFields['birthdate'] . ') AS year')
            ->select('MONTH(a.' . $birthdayFields['birthdate'] . ') AS month')
            ->select('DAYOFMONTH(a.' . $birthdayFields['birthdate'] . ') AS day')
            ->select('DAYOFYEAR(a.' . $birthdayFields['birthdate'] . ') AS yearday')
            ->select('DATEDIFF(DATE(a.' . $birthdayFields['birthdate'] . ') +
                        INTERVAL(YEAR(CURDATE()) - YEAR(a.' . $birthdayFields['birthdate'] . ') + (RIGHT(CURDATE(),5)>RIGHT(DATE(a.' . $birthdayFields['birthdate'] . '),5)))
                        YEAR, CURDATE()) AS till');
        if ($this->params->get('displayage'))
            $query->select('(YEAR(CURDATE()) - YEAR(a.' . $birthdayFields['birthdate'] . ') + (RIGHT(CURDATE(),5)>RIGHT(DATE(a.' . $birthdayFields['birthdate'] . '),5))) AS age');
        $query->from($birthdayFields['fromtable'] . ' AS a')
            ->innerJoin('#__users AS b ON a.' . $birthdayFields['userid'] . ' = b.id' . $jomsocial)
            ->having('till >= 0')
            ->having('till <= ' . $this->params->get('nextxdays'))
            ->order('till');

        $query->order($db->escape($birthdayFields['$order']));
        $db->setQuery($query, 0, $this->params->get('limit'));
        $res = '';
        try {
            $res = $db->loadAssocList();
        } catch (RuntimeException $e) {
            JLog::add('Can\'t load user birthdates!', JLog::ERROR, 'mod_sw_kbirthday');
        }
        if (!empty($res)) {
            //setting up the right birthdate
            //$todayyear = $this->time_now->format('Y', true);
            foreach ($res as $k => $v) {
                if ($v['year'] == 1 || empty($v['year'])) {
                    unset($res[$k]);
                } else {
                    //DON'T USE OFFSET! because the birthdate is saved without time 0:00-2h is a day earlier which is wrong!
                    $res[$k]['birthdate'] = new JDate($v['year'] . '-' . $v['month'] . '-' . $v['day']);
                    $res[$k]['correction'] = 0;
                    //both are leapyears or both are not
                    if ($this->time_now->format('L') == $res[$k]['birthdate']->format('L')) {
                        $res[$k]['correction'] = 0;
                    } //now leap year and birthday not
                    elseif ($this->time_now->format('L') == 1 && $res[$k]['birthdate']->format('L') == 0 &&
                        $res[$k]['birthdate']->format('m') > 2
                    ) {
                        //this value have to added to the birthdate!
                        $res[$k]['correction'] = 1;
                    } //now non leap year but birthday leap year
                    elseif ($this->time_now->format('L') == 0 && $res[$k]['birthdate']->format('L') == 1 &&
                        $res[$k]['birthdate']->format('m') > 2
                    ) {
                        //this value have to added to the birthdate!
                        $res[$k]['correction'] = -1;
                    }
                }
            }
        }
        return $res;
    }

    private function calcBirthdays()
    {
        $db = JFactory::getDbo();
        $query = $db->getQuery(true);

        $query->select('calcdate')
            ->from('#__schuweb_birthday');
        $timestamp = $db->setQuery($query)->loadResult();

        $calcDate = new JDate($timestamp);
        $todayDate = new JDate();
        $diff = $calcDate->diff($todayDate);

        if (empty($timestamp) || $diff->format('%a') != 0) {

            $db->truncateTable('#__schuweb_birthday');

            $listOfBirthdays = $this->getBirthdayData();

            $insert = array();

            foreach ($listOfBirthdays as $birthday) {
                $insert[] = $birthday['userid']
                    . ', ' . $birthday['till']
                    . ', ' . $birthday['age']
                    . ', ' . $db->q($birthday['birthdate']->format('Y-m-d'))
                    . ', ' . $birthday['correction'];
            }

            if (!empty($insert)) {
                $query = $db->getQuery(true);
                $query->insert('#__schuweb_birthday')
                    ->columns('userid, daystill, age, birthdate, correction')
                    ->values($insert);

                $db->setQuery($query)
                    ->execute();
            }
        }

        //return the calculated list
        $query = $db->getQuery(true);
        $query->select('*')
            ->from('#__schuweb_birthday');
        $res = $db->setQuery($query)
            ->loadAssocList();

        foreach ($res as $k => $v) {
            $res[$k]['birthdate'] = new JDate($v['birthdate']);
        }

        return $res;
    }
}
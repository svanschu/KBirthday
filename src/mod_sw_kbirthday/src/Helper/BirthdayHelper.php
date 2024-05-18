<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

namespace SchuWeb\Module\Birthday\Site\Helper;

use Joomla\CMS\Factory;
use Joomla\CMS\Uri\Uri;
use Joomla\CMS\Date\Date;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\WebAsset\WebAssetItem;
use Joomla\Registry\Registry;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseInterface;
use Joomla\CMS\Application\CMSApplicationInterface;


\defined('_JEXEC') or die;

abstract class BirthdayHelper
{

    /**
     * The application instance
     *
     * @var    CMSApplicationInterface
     * @since  __BUMP_VERSION__
     */
    protected $app;

    /**
     * Module parameter
     * 
     * @var     Registry
     * @since  __BUMP_VERSION__
     */
    protected $params;

    /**
     * Database Object
     * 
     * @var     DatabaseDriver
     * @since  __BUMP_VERSION__
     */
    protected $db;

    /**
     * Integration Object
     * 
     * @since  __BUMP_VERSION__
     */
    protected $integration;

    /**
     * Website Time Zone
     * 
     * @var string
     * @since  __BUMP_VERSION__
     */
    protected $soffset;

    /**
     * Time now with the configured Website Time Zone
     * 
     * @var Date
     * @since  __BUMP_VERSION__
     */
    protected $time_now;

    /**
     * @since 1.7.0
     * @param $params
     */
    function __construct($params)
    {
        $this->app    = Factory::getApplication();
        $this->db     = Factory::getContainer()->get(DatabaseInterface::class);
        $this->params = new Registry($params);
        //get the date today
        $config         = $this->app->getConfig();
        $this->soffset  = $config->get('offset');
        $this->time_now = new Date('now', $this->soffset);
    }

    /**
     * Set the helper class for integration
     * 
     * @since   _BUMP_VERSION_
     */
    function setIntegration($integration)
    {
        $this->integration = $integration;
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
            case 'eachtime':
            default:
                return $this->getBirthdayData();
        }

    }

    /**
     * adds the link for the connect param
     * @since 1.7.3
     * @param  $user pass-by-reference
     * @return void
     */
    public abstract function getUserLink(&$user);

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
            if (empty ($lang)) {
                $this->app->enqueueMessage(Text::_('SCHUWEB_BIRTHDAY_LANGUAGE_NOSUBJECT'), 'error');
                return null;
            }
            $subject = self::getWantedLangString($lang, 'SCHUWEB_BIRTHDAY_SUBJECT', $username);
        } else {
            $conf    = $this->app->getConfig();
            $subject = self::getWantedLangString($conf->get('language'), 'SCHUWEB_BIRTHDAY_SUBJECT', $username);
        }
        return $subject;
    }

    protected function getMessage($username)
    {
        if ($this->params->get('activatelanguage') == 'yes') {
            $lang = $this->params->get('messagelanguage');
            if (empty ($lang)) {
                $this->app->enqueueMessage(Text::_('SCHUWEB_BIRTHDAY_LANGUAGE_NOMESSAGE'), 'error');
                return;
            }
            $langa = explode(",", $lang);
            foreach ($langa as $value) {
                $value    = trim($value);
                $marray[] = self::getWantedLangString($value, 'SCHUWEB_BIRTHDAY_MESSAGE', $username);
            }
            $message = implode('\n\n', $marray);
        } else {
            $conf    = $this->app->getConfig();
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
        $exist = file_exists(JPATH_BASE . '/language/' . $lang . '/' . 'mod_sw_kbirthday.ini');
        if ($exist == FALSE) {
            $this->app->enqueueMessage(Text::sprintf('SCHUWEB_BIRTHDAY_LANGUAGE_NOTEXIST', $lang), 'error');
            return null;
        }
        $language = Factory::getContainer()->get(\Joomla\CMS\Language\LanguageFactoryInterface::class)->createLanguage($lang);
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
    private function addDate(&$user)
    {
        $bdate        = $user['birthdate']->format($this->params->get('dateform'), true);
        $user['date'] = Text::sprintf('SCHUWEB_BIRTHDAY_DATE', $bdate);
    }

    /**
     * Add number of days till birthdate and language string
     * @param  $tillstring pass-by-refernce
     * @return void
     * @since 1.6.0
     */
    private function addDaysTill(&$tillstring)
    {
        if (empty ($tillstring['till']) || $tillstring['till'] == 0) {
            if ($this->params->get('todaygraphic') === 'graphic') {
                $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
                $style = '.swkb_today{
					background: url("' . URI::base() . '/media/mod_sw_kbirthday/img/birthday16x16.png") no-repeat center top transparent scroll;
					height: 16px;
					width: 16px;
					display: inline-block;}';
                $wa->addInlineStyle($style);
                $tillstring['day_string'] = '<span class="swkb_today"> </span> ';
            } else
                $tillstring['day_string'] = Text::_('SCHUWEB_BIRTHDAY_TODAY');
        } elseif ($tillstring['till'] == 1)
            $tillstring['day_string'] = Text::sprintf('SCHUWEB_BIRTHDAY_DAY', $tillstring['till']);
        else
            $tillstring['day_string'] = Text::sprintf('SCHUWEB_BIRTHDAY_DAYS', $tillstring['till']);
    }

    /*
     * Get the list of the user who have birthday in next days
     * @since 1.6.0
     * @return Array
     */
    function getUserBirthday($params)
    {
        $list        = self::getBirthdayUser();
        $dage        = $this->params->get('displayage');
        $ddate       = $this->params->get('displaydate');
        $avatar      = $this->params->get('displayavatar');
        $graphicdate = $this->params->get('graphicdate');
        if ($graphicdate === 'graphic') {
            $wa = Factory::getApplication()->getDocument()->getWebAssetManager();
            $wai = new WebAssetItem('schuweb_birthday.calendar.css', 'media/mod_sw_kbirthday/css/calendar.css');
            $wa->registerAndUseStyle($wai);
        }
        $tgraphic = '';
        if ($this->params->get('todaygraphic') === 'graphic')
            $tgraphic = '_GRAPHIC';
        if (!empty ($list)) {
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
                        $v['age'] = Text::sprintf('SCHUWEB_BIRTHDAY_ADD_AGE', $v['age']);
                    else
                        $v['age'] = '';
                    //Should we display the date?
                    $v['date'] = $graphic = '';
                    if ($ddate && $graphicdate === 'text')
                        self::addDate($v);
                    elseif ($ddate && $graphicdate === 'graphic')
                        $graphic = self::getGraphicDate($v['birthdate']);
                    $list[$k]['link'] = $graphic . '<span>' . Text::sprintf('SCHUWEB_BIRTHDAY_HAVEBIRTHDAYIN' . $tgraphic, $v['link'], $v['day_string'], $v['age'], $v['date']) . '</span>';
                }
            }
        }
        return $list;
    }

    private function hideUser($user)
    {
        $hideUser = $this->params->get('hideuser');
        if (!empty ($hideUser)) {
            $users = explode(',', $hideUser);
            $users = $users ? $users : array();

            foreach ($users as $uid) {
                if ($uid == $user['userid']) {
                    return true;
                }
                ;
            }
        }

        if ($this->params->get('includeAll', 1) != 1) {
            $userGroups        = \Joomla\CMS\User\UserHelper::getUserGroups($user['userid']);
            $includeUserGroups = $this->params->get('usergrouplist', array());
            $res               = array_diff($includeUserGroups, $userGroups);

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
        $query     = $this->db->getQuery(true)
            ->select([
                $this->db->quoteName('b.username'),
                $this->db->quoteName('b.name'),
                $this->db->quoteName('b.id') . ' AS ' . $this->db->quoteName('userid'),
                $this->db->quoteName('b.email')
            ]);
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

        $query->order($this->db->escape($birthdayFields['$order']));
        $this->db->setQuery($query, 0, $this->params->get('limit'));
        $res = '';
        try {
            $res = $this->db->loadAssocList();
        } catch (\RuntimeException $e) {
            Log::add('Can\'t load user birthdates!', Log::ERROR, 'mod_sw_kbirthday');
        }
        if (!empty ($res)) {
            //setting up the right birthdate
            //$todayyear = $this->time_now->format('Y', true);
            foreach ($res as $k => $v) {
                if ($v['year'] == 1 || empty ($v['year'])) {
                    unset($res[$k]);
                } else {
                    //DON'T USE OFFSET! because the birthdate is saved without time 0:00-2h is a day earlier which is wrong!
                    $res[$k]['birthdate']  = new Date($v['year'] . '-' . $v['month'] . '-' . $v['day']);
                    $res[$k]['correction'] = 0;
                    //both are leapyears or both are not
                    if ($this->time_now->format('L') == $res[$k]['birthdate']->format('L')) {
                        $res[$k]['correction'] = 0;
                    } //now leap year and birthday not
                    elseif (
                        $this->time_now->format('L') == 1 && $res[$k]['birthdate']->format('L') == 0 &&
                        $res[$k]['birthdate']->format('m') > 2
                    ) {
                        //this value have to added to the birthdate!
                        $res[$k]['correction'] = 1;
                    } //now non leap year but birthday leap year
                    elseif (
                        $this->time_now->format('L') == 0 && $res[$k]['birthdate']->format('L') == 1 &&
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
        $query = $this->db->getQuery(true);

        $query->select('calcdate')
            ->from('#__schuweb_birthday');
        $timestamp = $this->db->setQuery($query)->loadResult();

        $calcDate  = new Date($timestamp);
        $todayDate = new Date();
        $diff      = $calcDate->diff($todayDate);

        if (empty ($timestamp) || $diff->format('%a') != 0) {

            $this->db->truncateTable('#__schuweb_birthday');

            $listOfBirthdays = $this->getBirthdayData();

            $insert = array();

            foreach ($listOfBirthdays as $birthday) {
                $insert[] = $birthday['userid']
                    . ', ' . $birthday['till']
                    . ', ' . $birthday['age']
                    . ', ' . $this->db->q($birthday['birthdate']->format('Y-m-d'))
                    . ', ' . $birthday['correction'];
            }

            if (!empty ($insert)) {
                $query = $this->db->getQuery(true);
                $query->insert('#__schuweb_birthday')
                    ->columns('userid, daystill, age, birthdate, correction')
                    ->values($insert);

                $this->db->setQuery($query)
                    ->execute();
            }
        }

        //return the calculated list
        $query = $this->db->getQuery(true);
        $query->select('*')
            ->from('#__schuweb_birthday');
        $res = $this->db->setQuery($query)
            ->loadAssocList();

        foreach ($res as $k => $v) {
            $res[$k]['birthdate'] = new Date($v['birthdate']);
        }

        return $res;
    }
}
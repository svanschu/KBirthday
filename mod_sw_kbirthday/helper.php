<?php
/**
 * @version $Id$
 *
 * @package SW KBirthday Module
 *
 * @Copyright (C) 2010-2011 Schultschik Websolution All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking

defined('_JEXEC') or die();

require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_kunena' . DS . 'libraries' . DS . 'api.php');
require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_kunena' . DS . 'libraries' . DS . 'integration' . DS . 'integration.php');
require_once (JPATH_BASE . DS . 'components' . DS . 'com_kunena' . DS . 'class.kunena.php');
require_once (JPATH_BASE . DS . 'components' . DS . 'com_kunena' . DS . 'lib' . DS . 'kunena.link.class.php');
require_once (JPATH_BASE . DS . 'components' . DS . 'com_kunena' . DS . 'lib' . DS . 'kunena.config.class.php');
require_once (JPATH_BASE . DS . 'components' . DS . 'com_kunena' . DS . 'lib' . DS . 'kunena.timeformat.class.php');

abstract class ModSWKbirthdayHelper
{
    /*
      * @since 1.7.0
      * @param $params
      */
    function __construct($params)
    {
        $this->app = JFactory::getApplication();
        $k_config = KunenaFactory::getConfig();
        $this->integration = $k_config->integration_profile;
        $this->username = $k_config->username;
        $this->params = $params;
        //get the date today
        $config = JFactory::getConfig();
        $this->soffset = $config->get('offset');
        $this->timeo = new JDate('now', $this->soffset);
        $this->btimeline = $params->get('nextxdays');
        $this->datemaxo = new JDate('now', $this->soffset);
        $this->datemaxo->add(new DateInterval('P' . $this->btimeline . 'D'));
    }

    /*
      * @since 1.6.0
      * @return list of users
      */
    private function getBirthdayUser()
    {
        $from = $this->timeo->format('z', true) + 1;
        $to = $this->datemaxo->format('z', true) + 1;
        if ($this->integration == 'auto')
            $this->integration = KunenaIntegration::detectIntegration('profile', true);
        $db = JFactory::getDBO();
        $query = $db->getQuery(true);
        $query->select('b.username');
        $query->select('b.name');
        $query->select('b.id AS userid');
        $jomsocial = '';
        if ($this->integration === 'jomsocial') {
            $birthdate = 'value';
            $fromtable = '#__community_fields_values';
            $jomsocial = ' AND a.field_id = 3 ';
            $userid = 'user_id';
        } elseif ($this->integration === 'communitybuilder') {
            //get the list of user birthdays
            $cbfield = $this->params->get('swkbcbfield', 'cb_birthday');
            $birthdate = $db->escape($cbfield);
            $fromtable = '#__comprofiler';
            $userid = 'id';
        } else {
            $birthdate = 'birthdate';
            $fromtable = '#__kunena_users';
            $userid = 'userid';
        }
        $query->select('YEAR(a.' . $birthdate . ') AS year');
        $query->select('MONTH(a.' . $birthdate . ') AS month');
        $query->select('DAYOFMONTH(a.' . $birthdate . ') AS day');
        $query->select('DATEDIFF(DATE(a.' . $birthdate . ') +
                        INTERVAL(YEAR(CURDATE()) - YEAR(a.' . $birthdate . ') + (RIGHT(CURDATE(),5)>RIGHT(DATE(a.' . $birthdate . '),5)))
                        YEAR, CURDATE()) AS till');
        if ($this->params->get('displayage'))
            $query->select('(YEAR(CURDATE()) - YEAR(a.' . $birthdate . ') + (DAYOFYEAR(CURDATE())>DAYOFYEAR(a.' . $birthdate . '))) AS age');
        $query->from($fromtable . ' AS a');
        $query->innerJoin('#__users AS b ON a.' . $userid . ' = b.id' . $jomsocial);
        $query->where('(DAYOFYEAR(a.' . $birthdate . ')>=' . $db->escape($from));
        if ($from > $to || $this->btimeline >= 365) {
            $query->where('DAYOFYEAR(a.' . $birthdate . ')<=366) OR (DAYOFYEAR(a.' . $birthdate . ')>=0');
            $query->where('DAYOFYEAR(a.' . $birthdate . ')<=' . $db->escape($to) . ')');
        } else {
            $query->where('DAYOFYEAR(a.' . $birthdate . ')<=' . $db->escape($to) . ')');
        }
        $query->order('till');
        if ($this->username == 0)
            $order = 'name';
        else
            $order = 'username';
        $query->order($db->escape($order));
        $db->setQuery($query, 0, $this->params->get('limit'));
        $res = $db->loadAssocList();
        if (!$res) {
            KunenaError::checkDatabaseError();
            if ($this->integration === 'communitybuilder')
                throw new DatabaseException(JText::_('SW_KBIRTHDAY_NOCBFIELD_IF'));
        }
        if (!empty($res)) {
            //setting up the right birthdate
            $todayyear = $this->timeo->format('Y', true);
            foreach ($res as $k => $v) {
                if ($v['year'] == 1 || empty($v['year'])) {
                    unset($res[$k]);
                } else {
                    $res[$k]['birthdate'] = new JDate($v['year'].'-'.$v['month'].'-'.$v['day'], $this->soffset);
                    $res[$k]['leapcorrection'] = $res[$k]['birthdate']->format('z', true) + 1;
                    $useryear = $res[$k]['birthdate']->format('Y', true);
                    //we have NOT a leap year?
                    if (($todayyear % 400) != 0 || !(($todayyear % 4) == 0 && ($todayyear % 100) != 0)) {
                        //was the birthdate in a leap year?
                        if (($useryear % 400) == 0 || (($useryear % 4) == 0 && ($useryear % 100) != 0)) {
                            //if we haven't leap year and birthdate was in leapyear we have to cut yday after february
                            if ($res[$k]['birthdate']->format('m', true) > 2) {
                                $res[$k]['leapcorrection'] -= 1;
                                if (($this->timeo->format('z', true) + 1) > $res[$k]['leapcorrection']) unset($res[$k]);
                            }
                            //was birthday on 29 february? then show it on 1 march
                            if ($v['month'] == 2 && $v['day'] == 29) {
                                $res[$k]['birthdate'] = $res[$k]['birthdate']->add(new DateInterval('P1D'));
                            }
                        }
                    } else { //We have a leap year
                        //Is the birthday not in a leap year?
                        if (($useryear % 400) != 0 || !(($useryear % 4) == 0 && ($useryear % 100) != 0)) {
                            //if we have leap year and birthday was not, need to increment birthdays after february
                            if ($res[$k]['birthdate']->format('m', true) > 2) {
                                $res[$k]['leapcorrection'] += 1;
                            }
                        }
                    }
                }
            }
        }
        return $res;
    }

    /**
     * adds the link for the connect param
     * @since 1.7.3
     * @param  $user pass-by-reference
     * @return void
     */
    abstract function getUserLink(& $user);

    /*
      * Get the subject of/for the forum post
      * @since 1.7.0
      * @return string subject
      */
    protected function getSubject($username)
    {
        if ($this->params->get('activatelanguage') == 'yes') {
            $lang = $this->params->get('subjectlanguage');
            if (empty($lang)) {
                $this->app->enqueueMessage(JText::_('SW_KBIRTHDAY_LANGUAGE_NOSUBJECT'), 'error');
                return;
            }
            $subject = self::getWantedLangString($lang, 'SW_KBIRTHDAY_SUBJECT', $username);
        } else {
            $conf = JFactory::getConfig();
            $subject = self::getWantedLangString($conf->get('language'), 'SW_KBIRTHDAY_SUBJECT', $username);
        }
        return $subject;
    }

    protected function getMessage($username)
    {
        if ($this->params->get('activatelanguage') == 'yes') {
            $lang = $this->params->get('messagelanguage');
            if (empty($lang)) {
                $this->app->enqueueMessage(JText::_('SW_KBIRTHDAY_LANGUAGE_NOMESSAGE'), 'error');
                return;
            }
            $langa = explode(",", $lang);
            foreach ($langa as $value) {
                $value = trim($value);
                $marray[] = self::getWantedLangString($value, 'SW_KBIRTHDAY_MESSAGE', $username);
            }
            $message = implode('\n\n', $marray);
        } else {
            $conf = JFactory::getConfig();
            $message = self::getWantedLangString($conf->get('language'), 'SW_KBIRTHDAY_MESSAGE', $username);
        }
        return $message;
    }

    /*
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
        $exist = JFile::exists(JPATH_BASE . DS . 'language' . DS . $lang . DS . $lang . '.mod_sw_kbirthday.ini');
        if ($exist == FALSE) {
            $this->app->enqueueMessage(JText::sprintf('SW_KBIRTHDAY_LANGUAGE_NOTEXIST', $lang), 'error');
            return;
        }
        $language = &JLanguage::getInstance($lang);
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
        $user['date'] = JText::sprintf('SW_KBIRTHDAY_DATE', $bdate);
    }

    /**
     * Add number of days till birthdate and language string
     * @param  $tillstring pass-by-refernce
     * @return void
     * @since 1.6.0
     */
    private function addDaysTill(& $tillstring)
    {
        if (empty($tillstring['till']) || $tillstring['till'] == 0)
            $tillstring['day_string'] = JText::_('SW_KBIRTHDAY_TODAY');
        elseif ($tillstring['till'] == 1)
            $tillstring['day_string'] = JText::sprintf('SW_KBIRTHDAY_DAY', $tillstring['till']);
        else
            $tillstring['day_string'] = JText::sprintf('SW_KBIRTHDAY_DAYS', $tillstring['till']);
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
        $users = explode(',', $this->params->get('hideuser'));
        $users = $users ? $users : array();
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                if ($this->hideUser($v, $users) === true) {
                    unset($list[$k]);
                } else {
                    $this->addDaysTill($v);
                    $this->getUserLink($v);
                    //Should we display the age?
                    if ($dage)
                        $v['age'] = JText::sprintf('SW_KBIRTHDAY_ADD_AGE', $v['age']);
                    else
                        $v['age'] = '';
                    //Should we display the date?
                    if ($ddate)
                        self::addDate($v);
                    else
                        $v['date'] = '';
                    $list[$k]['link'] = JText::sprintf('SW_KBIRTHDAY_HAVEBIRTHDAYIN', $v['link'], $v['day_string'], $v['age'], $v['date']);
                }
            }
        }
        return $list;
    }

    private function hideUser($user, $users){
        foreach ($users as $uid) {
            if ($uid == $user['userid']) {
                return true;
            };
        }
        return false;
    }
}
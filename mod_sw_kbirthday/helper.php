<?php
/**
 * @package SW KBirthday Module
 *
 * @Copyright (C) 2010-2012 Schultschik Websolution All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking

defined('_JEXEC') or die();

abstract class ModSWKbirthdayHelper
{
	/*
		  * @since 1.7.0
		  * @param $params
		  */
	function __construct($params)
	{
		if (class_exists('Kunena') && version_compare(Kunena::version(), '2.0.0', '<')) {
			require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_kunena' . DS .
				'libraries' . DS . 'api.php');
			require_once (JPATH_ADMINISTRATOR . DS . 'components' . DS . 'com_kunena' . DS .
				'libraries' . DS . 'integration' . DS . 'integration.php');
			require_once (JPATH_BASE . DS . 'components' . DS . 'com_kunena' . DS . 'class.kunena.php');
			require_once (JPATH_BASE . DS . 'components' . DS . 'com_kunena' . DS . 'lib' . DS .
				'kunena.link.class.php');
			require_once (JPATH_BASE . DS . 'components' . DS . 'com_kunena' . DS . 'lib' . DS .
				'kunena.config.class.php');
			require_once (JPATH_BASE . DS . 'components' . DS . 'com_kunena' . DS . 'lib' . DS .
				'kunena.timeformat.class.php');
		}
		$this->app = JFactory::getApplication();
		$this->uri =& JURI::getInstance();
		$k_config = KunenaFactory::getConfig();
		if (class_exists('Kunena') && version_compare(Kunena::version(), '2.0.0', '<'))
			$this->integration = $k_config->integration_profile;
		else
			$this->integration = $params->get('k20integration');
		$this->username = $k_config->username;
		$this->params = $params;
		//get the date today
		$config = JFactory::getConfig();
		$this->soffset = $config->get('offset');
		$this->time_now = new JDate('now', $this->soffset);
		$this->till_date = new JDate('now', $this->soffset);
		if (phpversion() < '5.3.0') {
			$this->till_date->modify('+' . $this->params->get('nextxdays') . ' day');
		} else {
			$this->till_date->add(new DateInterval('P' . $this->params->get('nextxdays') . 'D'));
		}
	}

	function loadHelper($params)
	{
		//get the birthday list with connection links
		$class = "ModSWKbirthdayHelper{$params->get('connection')}";
		$bday = new $class($params);
		return $bday->getUserBirthday();
	}

	/*
		  * @since 1.6.0
		  * @return list of users
		  */
	private function getBirthdayUser()
	{
		$from = $this->time_now->format('z');
		$to = $this->till_date->format('z');
		if ($this->integration == 'auto' &&
			(class_exists('Kunena') && version_compare(Kunena::version(), '2.0.0', '<'))
		)
			$this->integration = KunenaIntegration::detectIntegration('profile', true);
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('b.username');
		$query->select('b.name');
		$query->select('b.id AS userid');
		$query->select('b.email');
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
		if ($from > $to || $this->params->get('nextxdays') >= 365) {
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
		try {
			$res = $db->loadAssocList();
		} catch (JDatabaseException $e) {
			//loadAssocList seems to not throw an exception!
			JLog::addLogger( array() );
			JLog::add( 'Can\'t load user birthdates!');
			if ($this->integration === 'communitybuilder')
				JLog::add( JText::_('SW_KBIRTHDAY_NOCBFIELD_IF'), JLog::ERROR, 'SW KBirthday FAILURE:');
		}
		if (!empty($res)) {
			//setting up the right birthdate
			//$todayyear = $this->time_now->format('Y', true);
			foreach ($res as $k => $v) {
				if ($v['year'] == 1 || empty($v['year'])) {
					unset($res[$k]);
				} else {
					$res[$k]['birthdate'] = new JDate($v['year'] . '-' . $v['month'] . '-' . $v['day'], $this->soffset);
					$res[$k]['correction'] = 0;
					//both are leapyears or both are not
					if ( $this->time_now->format('L') == $res[$k]['birthdate']->format('L') ) {
						$res[$k]['correction'] = 0;
					} //now leap year and birthday not
					elseif ( $this->time_now->format('L') == 1  && $res[$k]['birthdate']->format('L') == 0 &&
								$res[$k]['birthdate']->format('m') > 2 ) {
						//this value have to added to the birthdate!
						$res[$k]['correction'] = 1;
					} //now non leap year but birthday leap year
					elseif ( $this->time_now->format('L') == 0  && $res[$k]['birthdate']->format('L') == 1 &&
								$res[$k]['birthdate']->format('m') > 2 ) {
						//this value have to added to the birthdate!
						$res[$k]['correction'] = -1;
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
	public abstract function getUserLink(& $user);

	protected function getAvatar($user)
	{
		if (class_exists('Kunena')) {
			return KunenaFactory::getUser($user)->getAvatarLink();
		} elseif (class_exists('KunenaForum')) {
			return KunenaFactory::getUser($user)->getAvatarImage();
		} else {
			return;
		}
	}

	protected function getGraphicDate($date)
	{
		$ret = '<p class="swkb_calendar">'
			. $date->format('j')
			. ' <em>'
			. $date->format('M')
			. '</em></p>';
		return $ret;
	}

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
		if (empty($tillstring['till']) || $tillstring['till'] == 0) {
			if ($this->params->get('todaygraphic') === 'graphic') {
				$doc = & JFactory::getDocument();
				$style = '.swkb_today{
					background: url("' . $this->uri->base() . '/media/mod_sw_kbirthday/img/birthday16x16.png") no-repeat center top transparent scroll;
					height: 16px;
					width: 16px;
					display: inline-block;}';
				$doc->addStyleDeclaration($style);
				$tillstring['day_string'] = '<span class="swkb_today"> </span> ';
			} else
				$tillstring['day_string'] = JText::_('SW_KBIRTHDAY_TODAY');
		} elseif ($tillstring['till'] == 1)
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
		$avatar = $this->params->get('displayavatar');
		$users = explode(',', $this->params->get('hideuser'));
		$graphicdate = $this->params->get('graphicdate');
		if ($graphicdate === 'graphic') {
			$doc = & JFactory::getDocument();
			$doc->addStyleSheet($this->uri->base() . '/modules/mod_sw_kbirthday/css/calendar.css');
		}
		$tgraphic = '';
		if ($this->params->get('todaygraphic') === 'graphic')
			$tgraphic = '_GRAPHIC';
		$users = $users ? $users : array();
		if (!empty($list)) {
			foreach ($list as $k => $v) {
				if ($this->hideUser($v, $users) === true) {
					unset($list[$k]);
				} else {
					$this->addDaysTill($v);
					$this->getUserLink($v);
					//Show Avatar?
					if ($avatar)
						$list[$k]['avatar'] = $this->getAvatar($v['userid']);
					//Should we display the age?
					if ($dage)
						$v['age'] = JText::sprintf('SW_KBIRTHDAY_ADD_AGE', $v['age']);
					else
						$v['age'] = '';
					//Should we display the date?
					$v['date'] = $graphic = '';
					if ($ddate && $graphicdate === 'text')
						self::addDate($v);
					elseif ($ddate && $graphicdate === 'graphic')
						$graphic = self::getGraphicDate($v['birthdate']);
					$list[$k]['link'] = $graphic . '<span>' . JText::sprintf('SW_KBIRTHDAY_HAVEBIRTHDAYIN' . $tgraphic, $v['link'], $v['day_string'], $v['age'], $v['date']) . '</span>';
				}
			}
		}
		return $list;
	}

	private function hideUser($user, $users)
	{
		foreach ($users as $uid) {
			if ($uid == $user['userid']) {
				return true;
			}
			;
		}
		return false;
	}
}
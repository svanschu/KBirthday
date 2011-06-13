<?php
/**
 * @version $Id$
 * 
 * @package SW KBirthday Module
 *
 * @Copyright (C) 2010 Schultschik Websolution All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking

defined( '_JEXEC' ) or die();

require_once (JPATH_ADMINISTRATOR . DS. 'components' . DS. 'com_kunena' . DS . 'libraries' . DS . 'api.php');
require_once (JPATH_ADMINISTRATOR . DS. 'components' . DS. 'com_kunena' . DS . 'libraries' . DS . 'integration'.DS.'integration.php');
require_once (JPATH_BASE . DS. 'components' . DS. 'com_kunena' . DS . 'class.kunena.php');
require_once (JPATH_BASE . DS. 'components' . DS. 'com_kunena' . DS . 'lib' . DS . 'kunena.link.class.php');
require_once (JPATH_BASE . DS. 'components' . DS. 'com_kunena' . DS . 'lib' . DS . 'kunena.config.class.php');
require_once (JPATH_BASE . DS. 'components' . DS. 'com_kunena' . DS . 'lib' . DS . 'kunena.timeformat.class.php');

class ModSWKbirthdayHelper
{
	/*
	 * @since 1.7.0
	 * @param $params
	 */
	function __construct($params){
		$this->app			= JFactory::getApplication();
		$k_config		    = KunenaFactory::getConfig ();
        $this->integration  = $k_config->integration_profile;
        $this->username     = $k_config->username;
		$this->params		= $params;
		//get the date today
		$timefrom	= $params->get('timefrom');
		$config	= JFactory::getConfig();
		$soffset = $config->getValue('config.offset');
		$this->timeo		= new JDate(); 
		switch ($timefrom){
			case 'website':
				$this->timeo	= new JDate( $this->timeo->toUnix(), -$soffset);
				break;
			case 'user':
				$user	=& JFactory::getUser();
				if(!$user->guest){
					$offset	= $user->getParam('timezone');
					if(!empty($offset))
						$this->timeo	= new JDate( $this->timeo->toUnix(), -$offset);
					else
						$this->timeo	= new JDate( $this->timeo->toUnix(), -$soffset);
				}
				break;
		}
		$this->btimeline = $params->get('nextxdays');
		$this->datemaxo	= new JDate( ($this->timeo->toUnix() + ( $this->btimeline * 86400) ) );
	}
	/*
	 * @since 1.6.0
	 * @return list of users
	 */
	private function getBirthdayUser()
	{
		$from			= $this->timeo->format('z');
		$to				= $this->datemaxo->format('z');
		if($this->integration == 'auto')
			$this->integration	= KunenaIntegration::detectIntegration ( 'profile' , true );
		$db		= JFactory::getDBO();
        if($this->username == 0)
            $order = 'name';
        else
            $order = 'username';
		if($this->integration === 'jomsocial'){
			$query = "SELECT b.username, b.name, b.id AS userid, YEAR(a.value) AS year,
					MONTH(a.value) AS month,DAYOFMONTH(a.value) AS day,
						DATEDIFF(DATE(a.value) +
						    INTERVAL(YEAR(CURDATE()) - YEAR(a.value) + (RIGHT(CURDATE(),5)>RIGHT(DATE(a.value),5)))
						    YEAR, CURDATE()) AS till";
            if ($this->params->get('displayage'))
                $query .= ",(YEAR(CURDATE()) - YEAR(a.value) + (DAYOFYEAR(CURDATE())>DAYOFYEAR(a.value))) AS age";
			$query .= " FROM #__community_fields_values AS a
					INNER JOIN #__users AS b
					ON a.user_id = b.id AND a.field_id = 3
					WHERE ( DAYOFYEAR(a.value)>={$from} AND DAYOFYEAR(a.value)<=";
			if($from>$to || $this->btimeline >= 365){
				$query .= "366) OR ( DAYOFYEAR(a.value)>=0 AND DAYOFYEAR(a.value)<={$to})";
			}else{
				$query .= "{$to})";
			}
		}elseif($this->integration === 'communitybuilder'){
			//get the list of user birthdays
			$cbfield	= $this->params->get('swkbcbfield', 'cb_birthday');
			$cb 	= $db->getEscaped($cbfield);
			$query	= "SELECT b.username, b.name, b.id AS userid, YEAR(a.{$cb}) AS year,
						MONTH(a.{$cb}) AS month,DAYOFMONTH(a.{$cb}) AS day,
						DATEDIFF(a.{$cb} +
						    INTERVAL(YEAR(CURDATE()) - YEAR(a.{$cb}) + (RIGHT(CURDATE(),5)>RIGHT(a.{$cb},5)))
						    YEAR, CURDATE()) AS till";
            if ($this->params->get('displayage'))
                $query .= ",(YEAR(CURDATE()) - YEAR(a.{$cb}) + (DAYOFYEAR(CURDATE())>DAYOFYEAR(a.{$cb}))) AS age";
            $query .= "	FROM #__comprofiler AS a
						INNER JOIN #__users AS b
						ON a.id = b.id
						WHERE (DAYOFYEAR(a.{$cb})>={$from} AND DAYOFYEAR(a.{$cb})<=";
			if($from>$to || $this->btimeline >= 365){
				$query .= "366) OR (DAYOFYEAR(a.{$cb})>=0 AND DAYOFYEAR(a.{$cb})<={$to})";
			}else{
				$query .= "{$to})";
			}
		}else{
			$query	= "SELECT b.username, b.name, b.id AS userid, YEAR(a.birthdate) AS year,
						MONTH(a.birthdate) AS month,DAYOFMONTH(a.birthdate) AS day,
						DATEDIFF(a.birthdate +
						    INTERVAL(YEAR(CURDATE()) - YEAR(a.birthdate) + (RIGHT(CURDATE(),5)>RIGHT(a.birthdate,5)))
						    YEAR, CURDATE()) AS till";
            if ($this->params->get('displayage'))
                $query .= ",(YEAR(CURDATE()) - YEAR(a.birthdate) + (DAYOFYEAR(CURDATE())>DAYOFYEAR(a.birthdate))) AS age";
            $query.= " FROM #__kunena_users AS a
						INNER JOIN #__users AS b
						ON a.userid = b.id
						WHERE (DAYOFYEAR(a.birthdate)>={$from} AND DAYOFYEAR(a.birthdate)<=";
			if($from>$to || $this->btimeline >= 365){
				$query .= "366) OR (DAYOFYEAR(a.birthdate)>=0 AND DAYOFYEAR(a.birthdate)<={$to})";
			}else{
				$query .= "{$to})";
			}
		}
		$query .= " ORDER BY till,".$db->getEscaped($order);
		$db->setQuery($query, 0, $this->params->get('limit') );
		$res	= $db->loadAssocList();
		if($db->getErrorMsg()){ 
			KunenaError::checkDatabaseError();
			if($this->integration === 'communitybuilder')
				$this->app->enqueueMessage ( JText::_('SW_KBIRTHDAY_NOCBFIELD_IF') , 'error' );
		}
		if(!empty($res)){
			//setting up the right birthdate
			$todayyear	= $this->timeo->format('Y');
			foreach ($res as $k=>$v){
				if($v['year'] == 1 || empty($v['year'])){
					unset($res[$k]);
				}else{
					$res[$k]['birthdate'] = new JDate( mktime(0,0,0,$v['month'],$v['day'],$v['year']) );
					$res[$k]['leapcorrection'] = $res[$k]['birthdate']->format('z');
					$useryear = $res[$k]['birthdate']->format('Y');			
					//we have NOT a leap year?
					if( ($todayyear % 400) != 0 || !( ( $todayyear % 4 ) == 0 && ( $todayyear % 100 ) != 0) ){
						//was the birthdate in a leap year?
						if( ($useryear % 400) == 0 || ( ( $useryear % 4 ) == 0 && ( $useryear % 100 ) != 0) ){
							//if we haven't leap year and birthdate was in leapyear we have to cut yday after february
							if($res[$k]['birthdate']->format('m') > 2){
								$res[$k]['leapcorrection'] -= 1;
								if( $this->timeo->format('z') > $res[$k]['leapcorrection'] ) unset($res[$k]);
							}
							//was birthday on 29 february? then show it on 1 march
							if($v['month'] == 2 && $v['day'] == 29){
								$res[$k]['birthdate'] = new JDate( ($res[$k]['birthdate']->toUnix() + 86400) );
							}
						}
					}else{ //We have a leap year
						//Is the birthday not in a leap year?
						if( ($useryear % 400) != 0 || !( ( $useryear % 4 ) == 0 && ( $useryear % 100 ) != 0) ){
							//if we have leap year and birthday was not, need to increment birthdays after february
							if($res[$k]['birthdate']->format('m') > 2){
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
	private function getUserLink(& $user){
		$username = KunenaFactory::getUser($user['userid'])->getName();
		switch ($this->params->get('connection')){
			case 'profil':
				$user['link'] = CKunenaLink::GetProfileLink($user['userid']);
				break;
			case 'forum':
				if( $user['leapcorrection'] == $this->timeo->format('z')){
					$subject = self::getSubject($username);
					$db		= JFactory::getDBO();
					$query	= "SELECT id,catid,subject,time as year FROM #__kunena_messages WHERE subject='{$subject}'";
					$db->setQuery($query,0,1);
					$post	= $db->loadAssoc();
					if($db->getErrorMsg()) KunenaError::checkDatabaseError();
					$catid		= $this->params->get('bcatid');
					$postyear = new JDate($post['year']);
					if( empty($post) && !empty($catid) ||
					!empty($post) && !empty($catid) && $postyear->format('Y') < $this->timeo->format('Y') ){

						$botname	= $this->params->get('swkbbotname', JText::_('SW_KBIRTHDAY_FORUMPOST_BOTNAME_DEF'));
						$botid		= $this->params->get('swkbotid');
						$time		= CKunenaTimeformat::internalTime ();
						//Insert the birthday thread into DB
						$query	= "INSERT INTO #__kunena_messages (catid,name,userid,email,subject,time, ip)
							VALUES({$catid},'{$botname}',{$botid}, '','{$subject}', {$time}, '')";
						$db->setQuery($query);
						$db->query();
						if($db->getErrorMsg()) KunenaError::checkDatabaseError();
						//What ID get our thread?
						$messid = (int) $db->insertID();
						//Insert the thread message into DB
						$message = self::getMessage($username);
						$query	= "INSERT INTO #__kunena_messages_text (mesid,message)
							VALUES({$messid},'{$message}')";
						$db->setQuery($query);
						$db->query();
						if($db->getErrorMsg()) KunenaError::checkDatabaseError();
						//We know the thread ID so we can update the parent thread id with it's own ID because we know it's
						//the first post
						$query = "UPDATE #__kunena_messages SET thread={$messid} WHERE id={$messid}";
						$db->setQuery($query);
						$db->query();
						if($db->getErrorMsg()) KunenaError::checkDatabaseError();
						// now increase the #s in categories
						CKunenaTools::modifyCategoryStats ( $messid, 0 , $time , $catid );
						$user['link'] = CKunenaLink::GetViewLink('view', $messid, $catid, '', $username);
						$uri = JFactory::getURI();
						if($uri->getVar('option') == 'com_kunena') {
							$app = & JFactory::getApplication();
							$app->redirect($uri->toString());
						}
					}elseif (!empty($post)){
						$user['link'] = CKunenaLink::GetViewLink('view', $post['id'], $post['catid'], '', $username);
					}
				}else{
					$user['link'] = CKunenaLink::GetProfileLink($user['userid']);
				}
				break;
			default:
				$user['link'] = $username;
				break;
		}
	}
	
	/*
	 * Get the subject of/for the forum post
	 * @since 1.7.0
	 * @return string subject
	 */
	private function getSubject($username){
		if($this->params->get('activatelanguage') == 'yes'){
			$lang = $this->params->get('subjectlanguage');
			if(empty($lang)){
				$this->app->enqueueMessage ( JText::_('SW_KBIRTHDAY_LANGUAGE_NOSUBJECT') , 'error' );
				return ;
			}
			$subject = self::getWantedLangString($lang, 'SW_KBIRTHDAY_SUBJECT', $username);
		}else{
			$conf = JFactory::getConfig();
			$subject = self::getWantedLangString($conf->getValue( 'config.language'), 'SW_KBIRTHDAY_SUBJECT', $username);
		}
		return $subject;
	}
	
	private function getMessage($username){
		if($this->params->get('activatelanguage') == 'yes'){
			$lang = $this->params->get('messagelanguage');
			if(empty($lang)){
				$this->app->enqueueMessage ( JText::_('SW_KBIRTHDAY_LANGUAGE_NOMESSAGE') , 'error' );
				return ;
			}
			$langa = explode(",",$lang);
			foreach ($langa as $value) {
				$value = trim($value);
				$marray[] = self::getWantedLangString($value, 'SW_KBIRTHDAY_MESSAGE', $username );
			}
			$message = implode('\n\n',$marray);
		}else{
			$conf = JFactory::getConfig();
			$message= self::getWantedLangString($conf->getValue( 'config.language'), 'SW_KBIRTHDAY_MESSAGE', $username );
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
	private function getWantedLangString($lang, $arg, $username){
		jimport('joomla.filesystem.file');
		$exist = JFile::exists(JPATH_BASE.DS.'language'.DS.$lang.DS.$lang.'.mod_sw_kbirthday.ini');
		if($exist == FALSE){
			$this->app->enqueueMessage ( JText::sprintf('SW_KBIRTHDAY_LANGUAGE_NOTEXIST',$lang) , 'error' );
			return ;
		}
		$language = &JLanguage::getInstance($lang);
		$language->load('mod_sw_kbirthday');
		$string = $language->_($arg);
		$string = sprintf($string,$username);
		return $string;
	}

    /**
     * Add date to sring
     * @param $user pass-by-refernce
     * @return void
	 * @since 1.7.0
	 */
	private function addDate(& $user){
			$bdate	= $user['birthdate']->format($this->params->get('dateform'));
			$user['date'] = JText::sprintf('SW_KBIRTHDAY_DATE', $bdate);
	}
	
	/**
     * Add number of days till birthdate and language string
     * @param  $tillstring pass-by-refernce
     * @return void
     * @since 1.6.0
     */
	private function addDaysTill(& $tillstring){
		if(empty($tillstring['till']) || $tillstring['till'] == 0)
			$tillstring['day_string']= JText::_('SW_KBIRTHDAY_TODAY');
		elseif($tillstring['till'] == 1)
			$tillstring['day_string']= JText::sprintf('SW_KBIRTHDAY_DAY', $tillstring['till']);
		else
			$tillstring['day_string']= JText::sprintf('SW_KBIRTHDAY_DAYS', $tillstring['till']);
	}
	
	/*
	 * Get the list of the user who have birthday in next days
	 * @since 1.6.0
	 * @return Array
	 */
	function getUserBirthday(){
		$list		= self::getBirthdayUser( );
		if(!empty($list)){
			foreach ($list as $k=>$v){
				$this->addDaysTill($v);
                $this->getUserLink($v);
                //Should we display the age?
                if ($this->params->get('displayage'))
				    $v['age'] = JText::sprintf('SW_KBIRTHDAY_ADD_AGE', $v['age']);
				else
                    $v['age']='';
                //Should we display the date?
                if ($this->params->get('displaydate'))
                    self::addDate($v);
			    else
                    $v['date'] = '';
				$list[$k]['link']		= JText::sprintf('SW_KBIRTHDAY_HAVEBIRTHDAYIN', $v['link'], $v['daystring'], $v['age'], $v['date'] );
			}
		}
		return $list;
	}
}
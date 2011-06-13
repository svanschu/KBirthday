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
		$this->k_config		= KunenaFactory::getConfig ();
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
		$btimeline = $params->get('nextxdays');
		$this->datemaxo	= new JDate( ($this->timeo->toUnix() + ( $btimeline * 86400) ) );
	}
	/*
	 * @since 1.6.0
	 * @return list of users
	 */
	private function getBirthdayUser()
	{
		$from			= $this->timeo->format('z');
		$to				= $this->datemaxo->format('z');
		$integration	= $this->k_config->integration_profile;
		if($integration == 'auto')
			$integration	= KunenaIntegration::detectIntegration ( 'profile' , true );
		$db		= JFactory::getDBO();
		if($integration === 'jomsocial'){
			$query = "SELECT b.username, b.name, b.id AS userid, YEAR(a.value) AS year, 
					MONTH(a.value) AS month,DAYOFMONTH(a.value) AS day
					FROM #__community_fields_values AS a 
					INNER JOIN #__users AS b
					ON a.user_id = b.id AND a.field_id = 3
					WHERE ( DAYOFYEAR(a.value)>={$from} AND DAYOFYEAR(a.value)<=";
			if($from>$to){
				$query .= "366) OR ( DAYOFYEAR(a.value)>=0 AND DAYOFYEAR(a.value)<={$to})";
			}else{
				$query .= "{$to})";
			}
		}elseif($integration === 'communitybuilder'){
			//get the list of user birthdays
			$cbfield	= $this->params->get('swkbcbfield');
			if(!empty($cbfield)){
				$cb 	= $db->getEscaped($cbfield);
			}else{
				JError::raiseWarning('', JText::_('SW_KBIRTHDAY_NOCBFIELD'));
				return NULL;
			}
			$query	= "SELECT b.username, b.name, b.id AS userid, YEAR(a.{$cb}) AS year, 
						MONTH(a.{$cb}) AS month,DAYOFMONTH(a.{$cb}) AS day
						FROM #__comprofiler AS a 
						INNER JOIN #__users AS b
						ON a.id = b.id
						WHERE (DAYOFYEAR(a.{$cb})>={$from} AND DAYOFYEAR(a.{$cb})<=";
			if($from>$to){
				$query .= "366) OR (DAYOFYEAR(a.{$cb})>=0 AND DAYOFYEAR(a.{$cb})<={$to})";
			}else{
				$query .= "{$to})";
			}
		}else{
			$query	= "SELECT b.username, b.name, b.id AS userid, YEAR(a.birthdate) AS year, 
						MONTH(a.birthdate) AS month,DAYOFMONTH(a.birthdate) AS day
						FROM #__kunena_users AS a 
						INNER JOIN #__users AS b
						ON a.userid = b.id
						WHERE (DAYOFYEAR(a.birthdate)>={$from} AND DAYOFYEAR(a.birthdate)<=";
			if($from>$to){
				$query .= "366) OR (DAYOFYEAR(a.birthdate)>=0 AND DAYOFYEAR(a.birthdate)<={$to})";
			}else{
				$query .= "{$to})";
			}
		}
		$db->setQuery($query);
		$res	= $db->loadAssocList();
		if($db->getErrorMsg()){ 
			KunenaError::checkDatabaseError();
			if($integration === 'communitybuilder')
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
	
	/*
	 * @since 1.6.0
	 * @param $list Assoc list with user data
	 * @return array of names/links
	 */
	private function getUserLinkList($list){
		foreach ($list as $k=>$user) {
			if($this->k_config->username == 0)
				$list[$k]['username'] = $user['name'];
			else
				$list[$k]['username'] = $user['username'];
			$con	= $this->params->get('connection');
			switch ($con){
				case 'profil': 
					$list[$k]['link'] = CKunenaLink::GetProfileLink($user['userid']);
					break;
				case 'forum':
					if( $user['leapcorrection'] == $this->timeo->format('z')){
						$subject = self::getSubject($list[$k]['username']);
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
							$message = self::getMessage($list[$k]['username']);
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
							$list[$k]['link'] = CKunenaLink::GetViewLink('view', $messid, $catid, '', $list[$k]['username']);
							$uri = JFactory::getURI();
							if($uri->getVar('option') == 'com_kunena') {
								$app = & JFactory::getApplication();
								$app->redirect($uri->toString());
							}
						}elseif (!empty($post)){
							$list[$k]['link'] = CKunenaLink::GetViewLink('view', $post['id'], $post['catid'], '', $list[$k]['username']);
						}
					}else{
						$list[$k]['link'] = CKunenaLink::GetProfileLink($user['userid']);
					}
					break;
				default:
					$list[$k]['link'] = $list[$k]['username'];
					break;				
			}
		}
		return $list;
	}
	
	/*
	 * Get the subject of/for the forum post
	 * @since 1.7.0
	 * @return string subject
	 */
	// TODO Made one function out of getSubjaect & getMessage
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
	
	/*
	 * Add Age to the Asocc list
	 * @since 1.6.0
	 * @param $linklist
	 * @param $bd
	 * @return asocc list
	 */
	private function addUserAge($linklist){
		$tyear	= (int)$this->timeo->format('Y');
		$tyday	= (int)$this->timeo->format('z');
		foreach ($linklist as $key=>$value){
			$byday	= (int)$value['birthdate']->format('z');
			if( $tyday > $byday) $nexty = 1;
			else $nexty = 0;
			$linklist[$key]['age'] = $tyear + $nexty - (int)$value['birthdate']->format('Y');
		}
		return $linklist;
	}
	
	/* Add date to sring
	 * @since 1.7.0
	 */
	private function addDate($linklist){
		$format		= $this->params->get('dateform');
		foreach ($linklist as $k=>$v) {
			$bdate	= $v['birthdate']->format($format);
			$linklist[$k]['date'] = JText::sprintf('SW_KBIRTHDAY_DATE', $bdate);
		}
		return $linklist;
	}
	
	/*
	 * Sort the birthday list after daysin and username
	 * @since 1.7.0
	 * @param $list array
	 * @return array sorted
	 */
	static private function bsort($list){
		$temp = NULL;
		foreach ($list as $v) {
			$temp[$v['daytill']][$v['username']]	= $v;
		}
		//sort after days till
		ksort($temp);
		//second sort after name
		foreach ($temp as $k=>$v){
			ksort($v);
			$temp[$k]=$v;
		}
		unset($list);
		//bring back in old array form
		foreach ($temp as $value) {
			foreach ($value as $v) {
				$ttemp[]	= $v;
			};
		}
		return $ttemp;
	}
	
	/*
	 * Add number of days till birthdate and language string to the Asocc list
	 * @since 1.6.0
	 * @param $linklist
	 * @param $bd
	 * @return asocc list
	 */
	private function addDaysTill($linklist){
		$tyday		= $this->timeo->format('z');
		$tyear		= $this->timeo->format('Y');
		$bonusday	= 0;
		//We have leap year?
		if( ($tyear % 400) == 0 || ( ( $tyear % 4 ) == 0 && ( $tyear % 100 ) != 0) )
			$bonusday = 1;			
		foreach ($linklist as $key=>$value){
			$byday	= $value['birthdate']->format('z');
			if($byday < $tyday) $linklist[$key]['daytill']= (365 + $bonusday - $tyday) + $byday;
			elseif ($byday > $tyday) $linklist[$key]['daytill']= $value['leapcorrection'] - $tyday;
			else $linklist[$key]['daytill']= 0;
			
			if(empty($linklist[$key]['daytill']) || $linklist[$key]['daytill'] == 0) 
				$linklist[$key]['daystring']= JText::_('SW_KBIRTHDAY_TODAY');
			elseif($linklist[$key]['daytill'] == 1) 
				$linklist[$key]['daystring']= JText::sprintf('SW_KBIRTHDAY_DAY', $linklist[$key]['daytill']);
			else 
				$linklist[$key]['daystring']= JText::sprintf('SW_KBIRTHDAY_DAYS', $linklist[$key]['daytill']);
			
		}
		$linklist = self::bsort( $linklist);
		return $linklist;
	}
	
	/*
	 * Get the list of the user who have birthday in next days
	 * @since 1.6.0
	 * @return Array
	 */
	function getUserBirthday(){
		$list		= self::getBirthdayUser( );
		
		$list1 = '';
		if(!empty($list)){
			$list1		= self::addDaysTill($list);
			//get limit number for birthdays
			$limit		= (int) $this->params->get('limit');
			//use limit to minimise the Array
			$list1 = array_slice($list1, 0, $limit);
			$list1		= self::getUserLinkList($list1);
			
			
			$disage		= $this->params->get('displayage');
			if (!empty($disage)) $list1 = self::addUserAge($list1); 
			
			$disdate	= $this->params->get('displaydate');
			if (!empty($disdate)) $list1 = self::addDate($list1);
			
			If(!empty($list1)){
				foreach ($list1 as $k=>$v){
					if (!empty($v['age']) ) $age = JText::sprintf('SW_KBIRTHDAY_ADD_AGE', $v['age']);
					else $age='';
					if( !isset($v['date']) ) $v['date'] = '';
					$list1[$k]['link']		= JText::sprintf('SW_KBIRTHDAY_HAVEBIRTHDAYIN', $v['link'], $v['daystring'], $age, $v['date'] );
				}
			}
		}
		
		return $list1;
	}
}
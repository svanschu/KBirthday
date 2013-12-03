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

class ModSWKbirthdayHelperMail extends ModSWKbirthdayHelper
{
	/**
	 * adds the link for the connect param
	 * @since 1.8.0
	 * @param  $user pass-by-reference
	 * @return void
	 */
	public function getUserLink(& $user)
	{
		//Did the user already get an e-mail?
		$db = JFactory::getDBO();
		$query = $db->getQuery(true);
		$query->select('uid');
		$query->select('YEAR(date) AS year');
		$query->from('#__sw_kbirthday');
		$query->where('uid=' . $db->escape($user['userid']));
		$db->setQuery($query);
		$res = $db->loadAssoc();

		$_user = KunenaFactory::getUser($user['userid']);
		$username = $_user->getName();

		if ( ($user['birthdate']->format('z') + $user['correction']) == ($this->time_now->format('z'))) {
			if ($res && ($res['year'] != $this->time_now->format('Y'))) {
				$query = $db->getQuery(true);
				$query->delete('#__sw_kbirthday');
				$query->where('uid=' . $db->escape($user['userid']));
				$db->setQuery($query);
				$db->query();
				unset($res);
			}
			if ( !isset($res) ) {
				$subject = self::getSubject($username);
				$message = self::getMessage($username);
				$config = JFactory::getConfig();
				//Prepare mail
				/*jimport('joomla.mail.mail');
				$mail = JMail::getInstance()
					->addRecipient($user['email'])
					->setSubject($subject)
					->setBody($message)
					->setSender($config->get('mailfrom'));
				$return = $mail->send();*/
                $return = JFactory::getMailer()->sendMail($config->get('mailfrom'), $config->get('fromname'), $user['email'], $subject, $message);
				if ($return  !== true) {
					JLog::add(JText::_('MOD_SW_KBIRTHDAY_SEND_MAIL_FAILED'), JLog::ERROR, 'mod_sw_kbirthday');
				} else {
					$query = $db->getQuery(true)
						->insert('#__sw_kbirthday')
						->set('uid=' . $db->escape($user['userid']));
					$db->setQuery($query)
						->query();
				}
			}
		}
		if (class_exists('Kunena'))
			$user['link'] = CKunenaLink::GetProfileLink($user['userid']);
		else {
			$user['link'] = $_user->getLink($username);
		}
	}
}
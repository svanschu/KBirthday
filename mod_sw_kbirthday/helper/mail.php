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
        $query->where('uid='.$db->escape($user['userid']));
        $db->setQuery($query);
        $res = $db->loadAssoc();

        if ($user['leapcorrection'] == ($this->timeo->format('z', true) + 1)) {
            if ($res && ($res['year'] != $this->timeo->format('Y', true))) {
                $query = $db->getQuery(true);
                $query->delete('#__sw_kbirthday');
                $query->where('uid='.$db->escape($user['userid']));
                $db->setQuery($query);
                $db->query();
                unset($res);
            }
            if (!$res) {
                $username   = KunenaFactory::getUser($user['userid'])->getName();
                $subject    = self::getSubject($username);
                $message    = self::getMessage($username);
                $config     = JFactory::getConfig();
                //Prepare mail
                jimport('joomla.mail.mail');
                $mail = JMail::getInstance()
                        ->addRecipient($user['email'])
                        ->setSubject($subject)
                        ->setBody($message)
                        ->setSender($config->get('mailfrom'));
                $return = $mail->send();
                if ($return !== true) {
                    JLog::add(JText::_('MOD_SW_KBIRTHDAY_SEND_MAIL_FAILED'), JLog::ERROR);
                } else {
                    $query = $db->getQuery(true)
                            ->insert('#__sw_kbirthday')
                            ->set('uid='.$db->escape($user['userid']));
                    $db->setQuery($query)
                        ->query();
                }
            }
        }
		if ( class_exists( 'Kunena') )
			$user['link'] = CKunenaLink::GetProfileLink($user['userid']);
		else {
			$user['link'] = KunenaUserHelper::get( $user['userid'] )->getLink( $username );
		}
    }
}
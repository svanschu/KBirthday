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

defined('_JEXEC') or die();

class ModSWKbirthdayHelperForum extends ModSWKbirthdayHelper
{
    /**
     * adds the link for the connect param
     * @since 1.7.3
     * @param  $user pass-by-reference
     * @return void
     */
    public function getUserLink(& $user)
    {
        $username = KunenaFactory::getUser($user['userid'])->getName();
        if ($user['leapcorrection'] == ($this->timeo->format('z', true) + 1)) {
            $subject = self::getSubject($username);
            $db = JFactory::getDBO();
            $query = "SELECT id,catid,subject,time as year FROM #__kunena_messages WHERE subject='{$subject}'";
            $db->setQuery($query, 0, 1);
            $post = $db->loadAssoc();
            if ($db->getErrorMsg()) KunenaError::checkDatabaseError();
            $catid = $this->params->get('bcatid');
            $postyear = new JDate($post['year'], $this->soffset);
            if (empty($post) && !empty($catid) ||
            !empty($post) && !empty($catid) && $postyear->format('Y', true) < $this->timeo->format('Y', true)) {
                $botname = $this->params->get('swkbbotname', JText::_('SW_KBIRTHDAY_FORUMPOST_BOTNAME_DEF'));
                $botid = $this->params->get('swkbotid');
                $time = CKunenaTimeformat::internalTime();
                //Insert the birthday thread into DB
                $query = "INSERT INTO #__kunena_messages (catid,name,userid,email,subject,time, ip)
		    		VALUES({$catid},'{$botname}',{$botid}, '','{$subject}', {$time}, '')";
                $db->setQuery($query);
                if (!$db->query()) KunenaError::checkDatabaseError();
                //What ID get our thread?
                $messid = (int)$db->insertID();
                //Insert the thread message into DB
                $message = self::getMessage($username);
                $query = "INSERT INTO #__kunena_messages_text (mesid,message)
                    VALUES({$messid},'{$message}')";
                $db->setQuery($query);
                if (!$db->query()) KunenaError::checkDatabaseError();
                //We know the thread ID so we can update the parent thread id with it's own ID because we know it's
                //the first post
                $query = "UPDATE #__kunena_messages SET thread={$messid} WHERE id={$messid}";
                $db->setQuery($query);
                if (!$db->query()) KunenaError::checkDatabaseError();
                // now increase the #s in categories
                CKunenaTools::modifyCategoryStats($messid, 0, $time, $catid);
                $user['link'] = CKunenaLink::GetViewLink('view', $messid, $catid, '', $username);
                $uri = JFactory::getURI();
                if ($uri->getVar('option') == 'com_kunena') {
                    $app = & JFactory::getApplication();
                    $app->redirect($uri->toString());
                }
            } elseif (!empty($post)) {
                $user['link'] = CKunenaLink::GetViewLink('view', $post['id'], $post['catid'], '', $username);
            }
        } else {
            $user['link'] = CKunenaLink::GetProfileLink($user['userid']);
        }
    }
}
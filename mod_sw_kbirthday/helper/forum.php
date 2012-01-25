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
			$db = JFactory::getDBO();
			$subject = $db->escape( self::getSubject($username) );
			if ( class_exists ( 'Kunena' ) ) {
            	$query = "SELECT id,catid,subject,time as year FROM #__kunena_messages WHERE subject='{$subject}'";
			} else {
				$query = "SELECT id,category_id as catid,subject,first_post_time as year FROM #__kunena_topics WHERE subject='{$subject}'";
			}
            $db->setQuery($query, 0, 1);
			$post = $db->loadAssoc();
            $catid = $this->params->get('bcatid');
            $postyear = new JDate($post['year'], $this->soffset);
            if (empty($post) && !empty($catid) ||
            !empty($post) && !empty($catid) && $postyear->format('Y', true) < $this->timeo->format('Y', true)) {
                $botname = $db->escape(
					$this->params->get('swkbbotname', JText::_('SW_KBIRTHDAY_FORUMPOST_BOTNAME_DEF') ) );
				$botid = $db->escape( $this->params->get('swkbotid') );
				$message = $db->escape( self::getMessage($username));
				if (class_exists('Kunena')) {
                	$time = CKunenaTimeformat::internalTime();
					//Insert the birthday thread into DB
					$query = "INSERT INTO #__kunena_messages (catid,name,userid,email,subject,time, ip)
						VALUES({$catid},'{$botname}',{$botid}, '','{$subject}', {$time}, '')";
					$db->setQuery($query);
					if (!$db->query()) KunenaError::checkDatabaseError();
					//What ID get our thread?
					$messid = (int)$db->insertID();
					//Insert the thread message into DB
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
				} else {
					$_user = KunenaUserHelper::get($botid);
					$fields = array(
						'category_id' => (int)$catid,
						'name' => $_user->getName(''),
						'email' => null,
						'subject' => $subject,
						'message' => $message,
						'icon_id' => 0,
						'tags' => null,
						'mytags' => null,
						'subscribe' => 0,);
					$category = KunenaForumCategoryHelper::get( (int)$catid );
					$app = JFactory::getApplication ();
					if (!$category->exists()) {
						$app->setUserState('com_kunena.postfields', $fields);
						$app->enqueueMessage ( $category->getError(), 'notice' );
					}
					if (!$category->authorise('topic.create', $_user)) {
						$app->setUserState('com_kunena.postfields', $fields);
						$app->enqueueMessage ( $category->getError(), 'notice' );
						//$this->redirectBack ();
					}
					list( $topic, $message) = $category->newTopic( $fields , $_user);
					//save message
					$success = $message->save ();
					if (! $success) {
						$app->enqueueMessage ( $message->getError (), 'error' );
						$app->setUserState('com_kunena.postfields', $fields);
						//$this->redirectBack ();
					}
					// Display possible warnings (upload failed etc)
					foreach ( $message->getErrors () as $warning ) {
						$app->enqueueMessage ( $warning, 'notice' );
					}
					//TODO alt tag
					$user['link'] = '<a href="'. $message->getUrl( $catid, true) .'">'. $username . '</a>';
				}
            } elseif (!empty($post)) {
				if ( class_exists( 'Kunena') )
                	$user['link'] = CKunenaLink::GetViewLink('view', $post['id'], $post['catid'], '', $username);
				else {
					//TODO alt tag
					$user['link'] = '<a href="'. KunenaForumTopicHelper::get( $post['id'] )->getUrl( $post['catid'], true, 'view') .'">'. $username . '</a>';
				}
            }
        } else {
            if ( class_exists( 'Kunena') )
				$user['link'] = CKunenaLink::GetProfileLink($user['userid']);
			else {
				$user['link'] = KunenaUserHelper::get( $user['userid'] )->getLink( $username );
			}
        }
    }
}
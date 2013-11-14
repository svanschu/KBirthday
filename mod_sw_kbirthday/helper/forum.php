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
        //DEBUG
        //print_r($user['birthdate']->format('Y-m-d').': '.$user['birthdate']->format('z') .'+'. $user['correction'] .' == '. ($this->time_now->format('z')) .'<br />');
		if ( ($user['birthdate']->format('z') + $user['correction']) == $this->time_now->format('z') ) {
			$db = JFactory::getDBO();
			$subject = $db->escape(self::getSubject($username));
			if (class_exists('Kunena')) {
				$query = "SELECT id,catid,subject,time as year FROM #__kunena_messages WHERE subject='{$subject}'";
			} else {
				$query = "SELECT id,category_id as catid,subject,first_post_time as year FROM #__kunena_topics WHERE subject='{$subject}'";
			}
			$db->setQuery($query, 0, 1);
			$post = $db->loadAssoc();
			$catid = $this->params->get('bcatid');
			$postyear = new JDate($post['year'], $this->soffset);
			if (empty($post) && !empty($catid) ||
				!empty($post) && !empty($catid) && $postyear->format('Y', true) < $this->time_now->format('Y', true)
			) {
				$botid = $db->escape($this->params->get('swkbotid'));
				$message = $db->escape(self::getMessage($username));

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
					$category = KunenaForumCategoryHelper::get((int)$catid);
					$app = JFactory::getApplication();
					if (!$category->exists()) {
						$app->setUserState('com_kunena.postfields', $fields);
						$app->enqueueMessage($category->getError(), 'notice');
					}
					if (!$category->authorise('topic.create', $_user)) {
						$app->setUserState('com_kunena.postfields', $fields);
						$app->enqueueMessage($category->getError(), 'notice');
						//$this->redirectBack ();
					}
					list($topic, $message) = $category->newTopic($fields, $_user);
					//save message
					$success = $message->save();
					if (!$success) {
						$app->enqueueMessage($message->getError(), 'error');
						$app->setUserState('com_kunena.postfields', $fields);
						//$this->redirectBack ();
					}
					// Display possible warnings (upload failed etc)
					foreach ($message->getErrors() as $warning) {
						$app->enqueueMessage($warning, 'notice');
					}
					//TODO alt tag
					$user['link'] = '<a href="' . $message->getUrl($catid, true) . '">' . $username . '</a>';

			} elseif (!empty($post)) {
				if (class_exists('Kunena'))
					$user['link'] = CKunenaLink::GetViewLink('view', $post['id'], $post['catid'], '', $username);
				else {
					//TODO alt tag
					$user['link'] = '<a href="' . KunenaForumTopicHelper::get($post['id'])->getUrl($post['catid'], true, 'view') . '">' . $username . '</a>';
				}
			}
		} else {
			if (class_exists('Kunena'))
				$user['link'] = CKunenaLink::GetProfileLink($user['userid']);
			else {
				$user['link'] = KunenaUserHelper::get($user['userid'])->getLink($username);
			}
		}
	}
}
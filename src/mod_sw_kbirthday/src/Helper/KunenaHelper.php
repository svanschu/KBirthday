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
use Joomla\CMS\Log\Log;
use Kunena\Forum\Libraries\Factory\KunenaFactory;
use Kunena\Forum\Libraries\User\KunenaUserHelper;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use Kunena\Forum\Libraries\Forum\Category\KunenaCategoryHelper;
use Kunena\Forum\Libraries\Forum\Topic\KunenaTopicHelper;

\defined('_JEXEC') or die;

class KunenaHelper extends BirthdayHelper
{
    /**
     * adds the link for the connect param
     * @since 1.7.3
     * @param  $user pass-by-reference
     * @return void
     */
    public function getUserLink(& $user)
    {
        $fail = false;

        if (!KunenaForum::enabled() || !KunenaForum::isCompatible('6.0')) {
            // Kunena is not installed or enabled
            $fail = true;
        }

        $user['link'] = '';

        $integration = $this->params->get('integration');
        if ( !($integration == 'jomsocial' || $integration == 'comprofiler' || $fail )) {
            $username = KunenaFactory::getUser($user['userid'])->getName();

            if (($user['birthdate']->format('z') + $user['correction']) == $this->time_now->format('z')) {
                $db = Factory::getDBO();
                $query = $db->getQuery(true);
                $query->select('a.topicid, b.first_post_time AS year, b.category_id AS catid')
                    ->from('#__schuweb_birthday_message AS a')
                    ->leftJoin('#__kunena_topics AS b ON b.id = a.topicid')
                    ->where("a.userid=" . $db->escape($user['userid']));
                $db->setQuery($query, 0, 1);
                $post = $db->loadAssoc();
                $catid = $this->params->get('bcatid');

                if (!empty($post))
                    $postyear = new JDate($post['year'], $this->soffset);

                if (empty($post) && !empty($catid) ||
                    !empty($post) && !empty($catid) && $postyear->format('Y', true) < $this->time_now->format('Y', true)
                ) {
                    if (!empty($post)) {
                        $query = $db->getQuery(true);
                        $query->delete('#__schuweb_birthday_message')
                            ->where('userid=' . $db->escape($user['userid']))
                            ->where('topicid=' . $db->escape($post['topicid']));
                        $db->setQuery($query);
                        $db->execute();
                    }

                    $botid = $db->escape($this->params->get('swkbotid'));
                    $message = $db->escape(self::getMessage($username));
                    $subject = $db->escape(self::getSubject($username));

                    if (empty($message) || empty($subject)){
                    	Factory::$application->enqueueMessage("Message and or subject are empty. Are the language files for KBirthday installed proberly?", 'error');
                    	return;
                    }

                    if ($botid != 0) {
                        $_user = KunenaUserHelper::get($botid);
                        $fields = array(
                            'name' => $_user->getName(''),
                            'email' => null,
                            'subject' => $subject,
                            'message' => str_replace('\n', "\n", html_entity_decode($message, ENT_COMPAT, 'UTF-8')),
                            'icon_id' => 0,
                            'tags' => null,
                            'mytags' => null,
                            'subscribe' => 0,
                        );

                        $safefields = array (
                            'category_id' => (int)$catid
                        );

                        $category = KunenaCategoryHelper::get((int)$catid);
                        $app = Factory::getApplication();
                        if (!$category->exists()) {
                            $app->setUserState('com_kunena.postfields', $fields);
                            $app->enqueueMessage($category->getError(), 'notice');
                        }

                        try
	                    {
		                    $category->tryAuthorise('topic.create', $_user);
	                    }
	                    catch (Exception $exception)
	                    {
		                    $app->setUserState('com_kunena.postfields', $fields);
		                    $app->enqueueMessage($exception->getMessage(), 'notice');
	                    }
                        
                        list($topic, $message) = $category->newTopic($fields, $_user, $safefields);

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

                        //Save the new topic to the topic user matching table
                        $query = $db->getQuery(true);
                        $query->insert('#__schuweb_birthday_message')
                            ->set('userid = ' . $user['userid'])
                            ->set('topicid = ' . $topic->id);
                        $db->setQuery($query);
                        $db->execute();


                        //TODO alt tag
                        $user['link'] = '<a href="' . $message->getUrl($catid, true) . '">' . $username . '</a>';
                    } else {
                        Log::add('The user ID for the bot creating Kunena threads does not exist', Log::WARNING, 'mod_sw_kbirthday');
                        $user['link'] = $username;
                    }

                } elseif (!empty($post)) {
                    //TODO alt tag
                    $user['link'] = '<a href="' . KunenaTopicHelper::get($post['topicid'])->getUrl($post['catid'], true, 'first') . '">' . $username . '</a>';

                }
            } else {
                $user['link'] = KunenaUserHelper::get($user['userid'])->getLink($username);
            }
        } else {
            $user['link'] = $this->integration->getProfileLink($user);
        }
    }
}
<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

namespace SchuWeb\Module\Birthday\Site\Helper;

\defined('_JEXEC') or die;

use Joomla\CMS\Factory;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Mail\MailerFactoryInterface;

class MailHelper extends BirthdayHelper
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
        $query = $this->db->getQuery(true);
        $query->select('uid');
        $query->select('YEAR(date) AS year');
        $query->from('#__sw_kbirthday');
        $query->where('uid=' . $this->db->escape($user['userid']));
        $this->db->setQuery($query);
        $res = $this->db->loadAssoc();

        $username = $this->integration->getUserName($user);

        if (($user['birthdate']->format('z') + $user['correction']) == ($this->time_now->format('z'))) {
            if ($res && ($res['year'] != $this->time_now->format('Y'))) {
                $query = $this->db->getQuery(true);
                $query->delete('#__sw_kbirthday');
                $query->where('uid=' . $this->db->escape($user['userid']));
                $this->db->setQuery($query)
                    ->execute();
                unset($res);
            }
            if (!isset($res)) {
                $subject = self::getSubject($username);
                $message = self::getMessage($username);
                $config = $this->app->getConfig();
                //Prepare mail
                $return = Factory::getContainer()
                    ->get(MailerFactoryInterface::class)
                    ->createMailer()
                    ->sendMail($config->get('mailfrom'), $config->get('fromname'), $user['email'], $subject, $message);
                if ($return !== true) {
                    Log::add(Text::_('SCHUWEB_BIRTHDAY_SEND_MAIL_FAILED'), Log::ERROR, 'mod_sw_kbirthday');
                } else {
                    $query = $this->db->getQuery(true)
                        ->insert('#__sw_kbirthday')
                        ->set('uid=' . $this->db->escape($user['userid']));
                    $this->db->setQuery($query)
                        ->execute();
                }
            }
        }
        $user['link'] = $this->integration->getProfileLink($user);
    }
}
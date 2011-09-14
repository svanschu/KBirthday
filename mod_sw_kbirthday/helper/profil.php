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

class ModSWKbirthdayHelperProfil extends ModSWKbirthdayHelper
{
    /**
     * adds the link for the connect param
     * @since 1.7.3
     * @param  $user pass-by-reference
     * @return void
     */
    public function getUserLink(& $user)
    {
        $user['link'] = CKunenaLink::GetProfileLink($user['userid']);
    }
}
<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

use Joomla\CMS\Component\ComponentHelper;
use Joomla\CMS\Log\Log;
use Joomla\CMS\Language\Text;
use Joomla\CMS\Factory;
use Kunena\Forum\Libraries\Forum\KunenaForum;
use SchuWeb\Module\Birthday\Site\Helper\BirthdayHelper;

defined('_JEXEC') or die();

Log::addLogger(array('text_file' => 'mod_sw_kbirthday.errors.php'), Log::ALL, 'mod_sw_kbirthday');

$kunenaConnection = $params->get('connection');
$integration = $params->get('integration');

$minCBVersion = '2.0.0';
$minKunenaVersion = '6.0.0';

$fail = false;

if ($integration == 'kunena' || $kunenaConnection == 'forum') {

    $kunenaRecord = ComponentHelper::getComponent('com_kunena');
    if (ComponentHelper::isInstalled('com_kunena') == 0) {
        $res = Text::sprintf('SCHUWEB_BIRTHDAY_NOT_INSTALLED', $minKunenaVersion);
        $fail = true;
    } elseif (!ComponentHelper::isEnabled('com_kunena')) {
        $res = Text::_('SCHUWEB_BIRTHDAY_NOT_ENABLED');
        $fail = true;
    } elseif (!version_compare(KunenaForum::version(), $minKunenaVersion, '>=')) {
        // Kunena is not installed or enabled
        $res = Text::sprintf('SCHUWEB_BIRTHDAY_NOT_INSTALLED', $minKunenaVersion);
        $fail = true;
    } elseif (!KunenaForum::enabled()) {
        // Kunena is not online, DO NOT use Kunena!
        $res = Text::_('SCHUWEB_BIRTHDAY_NOT_ENABLED');
        $fail = true;
    }
}

if ($integration == 'jomsocial' || $kunenaConnection == 'jomsocial') {
    //TODO check if version is correct and installed
}

if ($integration == 'comprofiler' || $kunenaConnection == 'communitybuilder') {
    if (!Joomla\CMS\Component\ComponentHelper::isEnabled("com_comprofiler", true)) {
        $fail = true;
        $res = Text::_("SWBIRTHDAY_CB_NOTINSTALLED_ENABLED");
    }

    $db = Factory::getDbo();
    $query = $db->getQuery(true);
    $query->select('manifest_cache');
    $query->from($db->quoteName('#__extensions'));
    $query->where('element = "com_comprofiler"');
    $db->setQuery($query);
    $manifest = json_decode($db->loadResult(), true);

    if (!version_compare($manifest['version'], $minCBVersion, '>=')) {
        $fail = true;
        $res = Text::sprintf("SWBIRTHDAY_CB_WRONG_VERSION", $minCBVersion);
    }
}

if ($fail != true) {
    $res = BirthdayHelper::loadHelper($params);
}

if (empty($res)) $res = Text::_('SCHUWEB_BIRTHDAY_NOUPCOMING');
require(Joomla\CMS\Helper\ModuleHelper::getLayoutPath('mod_sw_kbirthday', $params->get('tmpl', 'default')));

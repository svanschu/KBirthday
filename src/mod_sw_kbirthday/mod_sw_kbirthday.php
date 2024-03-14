<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

use Joomla\CMS\Component\ComponentHelper;
use Kunena\Forum\Libraries\Forum\KunenaForum;

defined('_JEXEC') or die();

require_once(dirname(__FILE__) . '/helper.php');
require_once(dirname(__FILE__) . '/helper/' . strtolower($params->get('connection')) . '.php');

JLog::addLogger(array('text_file' => 'mod_sw_kbirthday.errors.php'), JLog::ALL, 'mod_sw_kbirthday');

$kunenaConnection = $params->get('connection');
$integration = $params->get('integration');

$minCBVersion = '2.0.0';
$minKunenaVersion = '6.0.0';

$fail = false;

if ($integration == 'kunena' || $kunenaConnection == 'forum') {

    $kunenaRecord = ComponentHelper::getComponent('com_kunena');
    if (ComponentHelper::isInstalled('com_kunena') == 0) {
        $res = JText::sprintf('SCHUWEB_BIRTHDAY_NOT_INSTALLED', $minKunenaVersion);
        $fail = true;
    } elseif (!ComponentHelper::isEnabled('com_kunena')) {
        $res = JText::_('SCHUWEB_BIRTHDAY_NOT_ENABLED');
        $fail = true;
    } elseif (!version_compare(KunenaForum::version(), $minKunenaVersion, '>=')) {
        // Kunena is not installed or enabled
        $res = JText::sprintf('SCHUWEB_BIRTHDAY_NOT_INSTALLED', $minKunenaVersion);
        $fail = true;
    } elseif (!KunenaForum::enabled()) {
        // Kunena is not online, DO NOT use Kunena!
        $res = JText::_('SCHUWEB_BIRTHDAY_NOT_ENABLED');
        $fail = true;
    }
}

if ($integration == 'jomsocial' || $kunenaConnection == 'jomsocial') {
    //TODO check if version is correct and installed
}

if ($integration == 'comprofiler' || $kunenaConnection == 'communitybuilder') {
    if (!JComponentHelper::isEnabled("com_comprofiler", true)) {
        $fail = true;
        $res = JText::_("SWBIRTHDAY_CB_NOTINSTALLED_ENABLED");
    }

    $db = JFactory::getDbo();
    $query = $db->getQuery(true);
    $query->select('manifest_cache');
    $query->from($db->quoteName('#__extensions'));
    $query->where('element = "com_comprofiler"');
    $db->setQuery($query);
    $manifest = json_decode($db->loadResult(), true);

    if (!version_compare($manifest['version'], $minCBVersion, '>=')) {
        $fail = true;
        $res = JText::sprintf("SWBIRTHDAY_CB_WRONG_VERSION", $minCBVersion);
    }
}

if ($fail != true) {
    $res = ModSWKbirthdayHelper::loadHelper($params);
}

if (empty($res)) $res = JText::_('SCHUWEB_BIRTHDAY_NOUPCOMING');
require(JModuleHelper::getLayoutPath('mod_sw_kbirthday', $params->get('tmpl', 'default')));

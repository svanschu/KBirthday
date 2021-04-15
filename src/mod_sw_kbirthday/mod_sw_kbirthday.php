<?php
/**
 * @package SW KBirthday Module
 *
 * @Copyright (C) 2010-2021 Sven Schultschik. All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking

defined('_JEXEC') or die();

require_once(dirname(__FILE__) . '/helper.php');
require_once(dirname(__FILE__) . '/helper/' . strtolower($params->get('connection')) . '.php');

JLog::addLogger(array('text_file' => 'mod_sw_kbirthday.errors.php'), JLog::ALL, 'mod_sw_kbirthday');

$kunenaConnection = $params->get('connection');
$integration = $params->get('integration');

$minCBVersion = '2.0.0';
$minKunenaVersion = '3.0.0';

//TODO use Exceptions instead of if else
$fail = false;

if ($integration == 'kunena' || $kunenaConnection == 'forum') {
// Kunena detection and version check
    if (!class_exists('Kunena') || !version_compare(Kunena::version(), $minKunenaVersion, '>=')) {
        if (!class_exists('KunenaForum') || !version_compare(KunenaForum::version(), $minKunenaVersion, '>=')) {
            // Kunena is not installed or enabled
            $res = JText::sprintf('SW_KBIRTHDAY_NOT_INSTALLED', $minKunenaVersion);
            $fail = true;
        } elseif (!KunenaForum::enabled()) {
            // Kunena is not online, DO NOT use Kunena!
            $res = JText::_('SW_KBIRTHDAY_NOT_ENABLED');
            $fail = true;
        }
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

if (empty($res)) $res = JText::_('SW_KBIRTHDAY_NOUPCOMING');
require(JModuleHelper::getLayoutPath('mod_sw_kbirthday', $params->get('tmpl', 'default')));

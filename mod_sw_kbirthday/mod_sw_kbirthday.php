<?php
/**
 * @package SW KBirthday Module
 *
 * @Copyright (C) 2010-2013 Schultschik Websolution All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking

defined('_JEXEC') or die();

require_once (dirname(__FILE__) . '/helper.php');
require_once (dirname(__FILE__) . '/helper/' . strtolower($params->get('connection')) . '.php');

JLog::addLogger(array('text_file' => 'mod_sw_kbirthday.errors.php'), JLog::ALL, 'mod_sw_kbirthday');

// Kunena detection and version check
$minKunenaVersion = '2.0.0';
$minPHPVersion = '5.3.0';
if (!class_exists('Kunena') || !version_compare(Kunena::version(), $minKunenaVersion, '>=')) {
	if (!class_exists('KunenaForum') || !version_compare(KunenaForum::version(), $minKunenaVersion, '>=')) {
		// Kunena 1.6 is not installed or enabled
		$res = JText::sprintf('SW_KBIRTHDAY_NOT_INSTALLED', $minKunenaVersion);
	} elseif (!KunenaForum::enabled()) {
		// Kunena 1.6 is not online, DO NOT use Kunena!
		$res = JText::_('SW_KBIRTHDAY_NOT_ENABLED');
	} else {
		$res = ModSWKbirthdayHelper::loadHelper($params);
	}
} elseif (!Kunena::enabled()) {
	// Kunena 1.6 is not online, DO NOT use Kunena!
	$res = JText::_('SW_KBIRTHDAY_NOT_ENABLED');
} else {
	$res = ModSWKbirthdayHelper::loadHelper($params);
}

if (empty($res)) $res = JText::_('SW_KBIRTHDAY_NOUPCOMING');
require(JModuleHelper::getLayoutPath('mod_sw_kbirthday', $params->get('tmpl', 'default')));

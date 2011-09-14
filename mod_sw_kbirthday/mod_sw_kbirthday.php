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

defined( '_JEXEC' ) or die();

require_once (dirname(__FILE__).'/helper.php');
require_once (dirname(__FILE__).'/helper/'.strtolower($params->get('connection')).'.php');
// Kunena detection and version check
$minKunenaVersion = '1.6.0';
$minPHPVersion = '5.3.0';
if (!class_exists ( 'Kunena' ) || !version_compare( Kunena::version (), $minKunenaVersion, '>=' )  ) {
	// Kunena 1.6 is not installed or enabled
	$res = JText::sprintf('SW_KBIRTHDAY_NOT_INSTALLED', $minKunenaVersion);
}elseif (! Kunena::enabled ()) {
	// Kunena 1.6 is not online, DO NOT use Kunena!
	$res = JText::_('SW_KBIRTHDAY_NOT_ENABLED');
}elseif (phpversion() < $minPHPVersion) {
    $res = JText::sprintf('SW_KBIRTHDAY_MIN_PHP', $minPHPVersion);
}else{
	//get the birthday list with connection links
    $class = "ModSWKbirthdayHelper{$params->get('connection')}";
	$bday = new $class($params);
	$res = $bday->getUserBirthday();
}

if(empty($res)) $res = JText::_('SW_KBIRTHDAY_NOUPCOMING');

$tmpl = $params->get('tmpl');
require(JModuleHelper::getLayoutPath('mod_sw_kbirthday', $tmpl));

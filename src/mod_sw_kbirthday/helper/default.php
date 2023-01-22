<?php
/**
 * @package             SchuWeb Birthday
 *
 * @version             sw.build.version
 * @author              Sven Schultschik
 * @copyright (C)       2010 - 2023 Sven Schultschik. All rights reserved
 * @license             http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link                http://www.schultschik.de
 **/

defined('_JEXEC') or die();

class ModSWKbirthdayHelperDefault extends ModSWKbirthdayHelper
{
	/**
	 * adds the link for the connect param
	 * @since 1.7.3
	 * @param  $user pass-by-reference
	 * @return void
	 */
	public function getUserLink(& $user)
	{
		$user['link'] = $this->integration->getUserName($user);
	}
}
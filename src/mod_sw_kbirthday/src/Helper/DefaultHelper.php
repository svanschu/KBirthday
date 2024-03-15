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

class DefaultHelper extends BirthdayHelper
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
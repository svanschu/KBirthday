<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

defined('_JEXEC') or die();
if (is_array($res)) {
	?>
<ul class="swkb<?php echo htmlspecialchars($params->get('moduleclass_sfx', '')) ?>">
	<?php foreach ($res as $v) {
	$str = '<li>';
	if (isset($v['avatar']))
		$str .= $v['avatar'];
	echo $str . $v['link'] . '</li>';
}
	?>
</ul>
<?php
} else {
	echo $res;
}
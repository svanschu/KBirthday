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
<?php
/**
 * @version     sw.build.version
 * @copyright   Copyright (C) 2010 - 2024 Sven Schultschik. All rights reserved
 * @license     GPL-3.0-or-later
 * @author      Sven Schultschik (extensions@schultschik.de)
 * @link        extensions.schultschik.de
 */

defined('_JEXEC') or die();

use Joomla\CMS\Language\Text;
use Kunena\Forum\Libraries\Icons\KunenaIcons;

?>
<div class="kfrontend sw_kbirthday <?php echo htmlspecialchars($params->get('moduleclass_sfx', '')) ?>">
<div class="btn-toolbar float-end">
        <div class="btn-group">
            <div class="btn btn-outline-primary border btn-sm" data-bs-toggle="collapse"
                 data-bs-target="#sw_kbirthday"><?php echo KunenaIcons::collapse(); ?></div>
        </div>
    </div>
    <h2 class="btn-link">
        <?php echo Text::_('SCHUWEB_BIRTHDAY_BIRTHDAY')?>
    </h2>
	<div class="row-fluid collapse in" id="sw_kbirthday">
		<div class="well-small">
            <ul class="unstyled span1 btn-link">
                <span class="icon icon-calendar icon-big" aria-hidden="true"></span>
            </ul>
            <?php
			if (is_array($res)) :
			    $count = count($res);
			    $singlecount = ceil($count / 3);

			    for ($i = 0; $i < 3; $i++): ?>
				    <ul class="unstyled span3">
                        <?php for ($ii = 0; $ii < $singlecount; $ii++): ?>
                            <li>
	                            <?php if ($count > $ii + ($i * $singlecount))
	                            {
		                            echo $res[$ii + ($i * $singlecount)]['link'];
	                            } ?>
                            </li>
                        <?php endfor; ?>
                    </ul>
                <?php endfor;
            else: ?>
                <ul class="unstyled span3">
                    <li><?php echo $res; ?></li>
                </ul>
			<?php endif; ?>
		</div>
	</div>
</div>
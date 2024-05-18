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
		<?php echo Text::_('SCHUWEB_BIRTHDAY_BIRTHDAY') ?>
    </h2>
    <div class="collapse in" id="sw_kbirthday">
        <div class="well well-sm">
            <div class="container">
                <div class="row">
                    <div class="col-md-1">
                        <ul class="list-unstyled">
                            <li class="btn-link text-center">
                                <?php echo KunenaIcons::birthdate(); ?>
                            </li>
                        </ul>
                    </div>
					<?php
					if (is_array($res)) :
						$count = count($res);
						$singlecount = ceil($count / 3);

						for ($i = 0; $i < 3; $i++): ?>
                            <div class="col-md-3">
                                <ul class="list-unstyled">
									<?php for ($ii = 0; $ii < $singlecount; $ii++): ?>
                                        <li>
	                                        <?php if ($count > $ii + ($i * $singlecount))
	                                        {
		                                        echo $res[$ii + ($i * $singlecount)]['link'];
	                                        } ?>
                                        </li>
									<?php endfor; ?>
                                </ul>
                            </div>
						<?php endfor;
					else: ?>
                        <ul class="unstyled span3">
                            <li><?php echo $res; ?></li>
                        </ul>
					<?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>
<?php
/**
 * @package SchuWeb Birthday Module
 *
 * @Copyright (C) 2010-sw.build.year Sven Schultschik. All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link http://www.schultschik.de
 **/
// Dont allow direct linking
defined('_JEXEC') or die();

?>
<div class="kfrontend sw_kbirthday <?php echo htmlspecialchars($params->get('moduleclass_sfx', '')) ?>">
    <div class="btn-toolbar pull-right">
        <div class="btn-group">
            <div class="btn btn-small" data-toggle="collapse" data-target="#sw_kbirthday"></div>
        </div>
    </div>
    <h2 class="btn-link">
        <?php echo JText::_('SW_KBIRTHDAY_BIRTHDAY')?>
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
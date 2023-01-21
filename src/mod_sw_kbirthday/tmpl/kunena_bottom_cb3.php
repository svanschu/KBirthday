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

?>
<div class="kfrontend sw_kbirthday <?php echo htmlspecialchars($params->get('moduleclass_sfx', '')) ?>">
    <div class="btn-toolbar pull-right">
        <div class="btn-group">
            <div class="btn btn-default btn-sm" data-toggle="collapse" data-target="#sw_kbirthday"><span
                        class="glyphicon glyphicon-sort" aria-hidden="true"></span></div>
        </div>
    </div>
    <h2 class="btn-link">
		<?php echo JText::_('SW_KBIRTHDAY_BIRTHDAY') ?>
    </h2>
    <div class="collapse in" id="sw_kbirthday">
        <div class="well well-sm">
            <div class="container">
                <div class="row">
                    <div class="col-md-1">
                        <ul class="list-unstyled">
                            <li class="btn-link text-center">
                                <span class="glyphicon glyphicon-gift glyphicon-super" aria-hidden="true"></span>
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
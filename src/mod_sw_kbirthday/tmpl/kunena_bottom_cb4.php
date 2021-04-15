<?php
/**
 * @package SchuWeb Birthday Module
 *
 * @Copyright (C) 2010-2021 Sven Schultschik. All rights reserved
 * @license http://www.gnu.org/copyleft/gpl.html GNU/GPL
 * @link    http://www.schultschik.de
 **/
// Dont allow direct linking
defined('_JEXEC') or die();

?>
<div class="kfrontend shadow-lg rounded mt-4 border sw_kbirthday <?php echo htmlspecialchars($params->get('moduleclass_sfx', '')) ?>">
    <div class="btn-toolbar float-right">
        <div class="btn-group">
            <div class="btn btn-outline-primary border btn-sm" data-toggle="collapse" data-target="#sw_kbirthday">
                <span class="icon icon-compress" aria-hidden="true"></span>
            </div>
        </div>
    </div>
    <h2 class="card-header">
		<?php echo JText::_('SW_KBIRTHDAY_BIRTHDAY') ?>
    </h2>
    <div class="shadow-lg rounded collapse show" id="sw_kbirthday">
        <div class="card-body">
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
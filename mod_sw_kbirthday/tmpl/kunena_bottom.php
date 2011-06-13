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
$doc = & JFactory::getDocument();
$uri =& JURI::getInstance();
$style = '#Kunena div.sw_kbirthday td.kcol-first{width:1%;}
			#Kunena .swkbicon{
				background: url("'.$uri->base().'/media/mod_sw_kbirthday/img/birthday.png") no-repeat center top transparent scroll;
				height: 32px;
				width: 32px;}';
$doc->addStyleDeclaration($style);
?>
<div class="kblock sw_kbirthday <?php echo $params->get('moduleclass_sfx', '') ?>">
	<div class="kheader">
		<span class="ktoggler">
			<a class="ktoggler close" title="<?php echo JText::_('COM_KUNENA_TOGGLER_COLLAPSE') ?>" rel="sw_kbirthday"></a>
		</span>
		<h2><span class="ktitle km">
			<?php echo JText::_('SW_KBIRTHDAY_BIRTHDAY')?>
		</span></h2>
	</div>
	<div class="kcontainer" id="sw_kbirthday">
		<div class="kbody">
			<table class = "kblocktable">
				<tr class = "krow2">
					<td class = "kcol-first">
						<div class="swkbicon"></div>
					</td>
					<td class = "kcol-mid km">
						<div class="sw_kbirthdy ks">
						<?php
						if(is_array($res)){
							$num = false;
							foreach ($res as $v){
								if($num == false){ 
									echo $v['link'];
									$num = true;
								}
								else echo ', '.$v['link'];
							}
						}else{
							echo $res;
						}?>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>
</div>
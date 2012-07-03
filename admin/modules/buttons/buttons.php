<?php 
if(!defined('ARTA_VALID')){die('No access');}
echo '<a name="buttons_hand" style="display: none;"></a>';
ArtatagsHtml::addheader('<style>
table.adminbuttons td {
	text-align:center; 
	width:48px; 
	height:48px; 
	vertical-align:top;
}
</style>');
$type=$this->getSetting('direction', 'hor');
$i=$this->getSetting('show_images', false);
$this->assign('show_images',$i);
$this->setLayout($type);
$this->render();

?>
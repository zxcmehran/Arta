<?php if(!defined('ARTA_VALID')){die('No access');}
if($this->getPerm()){
	echo '<a href="index.php?pack=pages&view=new&pid='.$this->get('pid').'"><img src="'.Imageset('edit.png').'" title="'.trans('EDIT PAGE').'" style="position:absolute; top:5px;left:5px;" /></a>';
}
ArtaTagsHtml::addHeader('<style>
div#widgets_container div.custom_widget > table {
	width:100%;
	height:100%;
}
div#widgets_container div.custom_widget > table td {
	vertical-align:top;
}
</style>');
?>
<noscript style="color:red; font-weight:bold;"><?php echo trans('RECOMMENDED TO ACTIVATE SCRIPT ERROR');?></noscript>
<article>
<div<?php 
	$align = $this->get('align');
	if($align!=false){
		echo ' align="'.htmlspecialchars($align).'"';
	} ?>>
<div id="widgets_container" align="<?php echo trans('_LANG_DIRECTION')=='ltr'?'left':'right' ?>" style="<?php
	echo ArtaString::stickvars($this->get('params'), '; ', ': ');
?>">
<?php
	echo $this->get('wids');
?>
</div>
</div>
</article>
<?php
	
	if(ArtaBrowser::getPlatform() != 'midp'){
		$t=ArtaLoader::Template();
		$t->addtoTmpl('<script>function widgets_move(){
	widgets=$$(\'div#widgets_container div.custom_widget\');
	elm=Element.cumulativeOffset($(\'widgets_container\'));
	for(i=0;i<widgets.length;i++){
		wtop=widgets[i].getStyle(\'top\');
		wtop=parseInt(wtop);
		wleft=widgets[i].getStyle(\'left\');
		wleft=parseInt(wleft);
		widgets[i].setStyle({top: (wtop+elm[1])+\'px\', left: (wleft+elm[0])+\'px\', position:\'absolute\'});
	}
}widgets_move();</script>', 'beforebodyend');
	}
	
?>
<?php if(!defined('ARTA_VALID')){die('No access');} 
$align = strtolower($this->get('align'));

ArtaTagsHtml::addHeader('<script>
curpageid="'.$this->get('pid').'";
ERROR_IN_CONTACTING_SERVER="'.JSValue(trans('ERROR_IN_CONTACTING_SERVER')).'";
DO_YOU_REALLY_WANT_TO_DELETE_THIS_WIDGET="'.JSValue(trans('DO_YOU_REALLY_WANT_TO_DELETE_THIS_WIDGET')).'";
WIDGET_DELETED="'.JSValue(trans('WIDGET_DELETED')).'";
TRYING_TO_SAVE="'.JSValue(trans('TRYING_TO_SAVE')).'";
DONE="'.JSValue(trans('DONE')).'";
SAVING_FINISHED="'.JSValue(trans('SAVING_FINISHED')).'";
RESET_TO_AUTO="'.JSValue(trans('RESET_TO_AUTO')).'";
PAGEDIR="'.JSValue(trans('_LANG_DIRECTION')).'";
DIMS="'.JSValue(trans('DIMENSIONS')).'";
TOKEN="'.ArtaSession::genToken().'";
</script>
<style>
span.resizeHandle{
	display:block;
	width:10px;
	height:10px;
	cursor: se-resize;
	border-right: 1px solid gray;
	border-bottom: 1px solid gray;
}
div.toolbar button {
	width:100%;
}
div#widgets_container div.custom_widget > table {
	width:100%;
	height:100%;
}
div#widgets_container div.custom_widget > table td {
	vertical-align:top;
}
input.alignRadio{
	padding:1px;
	margin:1px;
}

</style>
')?>
<noscript style="color:red; font-weight:bold;"><?php echo trans('MUST ACTIVATE SCRIPT ERROR');?></noscript>

<div class="toolbar" id="PageTools" style="position:absolute;top:5px;left:5px;background:#205070;width:200px;color:white;">

<table width="100%"><tr><td>
<button onclick="openNew('<?php	echo $this->get('pid');?>')"><?php echo trans('ADD NEW WIDGET'); ?></button><br />
<button onclick="setCanvWidAuto()"><?php echo trans('RESET_TO_AUTO_BTN'); ?></button><br />
<button onclick="saveData()"><?php echo trans('SAVE'); ?></button><br />
<button onclick="closeenv()"><?php echo trans('CLOSE EDITING ENV'); ?></button><br />
</td><td style="min-height: 70px;vertical-align:top;"><br />
<a href="index.php?pack=pages&pid=<?php echo $this->get('pid') ?>" target="_blank" style="text-align:center; "><img src="<?php echo Imageset('preview.png');?>" title="<?php echo trans('preview');?>" alt="<?php echo trans('preview');?>"/></a><br/>
<img src="<?php echo Imageset('loading.gif');?>" id="loading_img" style="width: 32px;height: 32px; display:none;"/>
<br /></td></tr>
<tr>
<td colspan="2" dir="ltr" align="center">
<div style="text-align: center; font-weight: bold;"><?php echo trans('CANVAS ALIGN'); ?></div>
<label dir="ltr"><input class="alignRadio" type="radio" onchange="setCanvasAlign(this.value)" <?php if($align=='left') echo 'checked="checked" ' ?>value="left" name="align"/> <?php echo trans("ALEFT"); ?></label> |
<label dir="ltr"><input class="alignRadio" type="radio" onchange="setCanvasAlign(this.value)" <?php if($align=='center') echo 'checked="checked" ' ?>value="center" name="align"/> <?php echo trans("ACENTER"); ?></label> |
<label dir="ltr"><input class="alignRadio" type="radio" onchange="setCanvasAlign(this.value)" <?php if($align=='right') echo 'checked="checked" ' ?>value="right" name="align"/> <?php echo trans("ARIGHT"); ?></label>
</td></tr>
</table>

<span style="padding:2px 2px 2px 2px;border:2px solid orange; width:90%; display:block" id="status_bar" ><?php echo trans('DBLCLICK TO EDIT WIDGETS OR ADD NEW'); ?></span><br />


<span>
<?php
	echo trans('NOTE YOU CAN MOVE IT');
?>
<script>
/*if(Prototype.Browser.IE){
	document.write('<br />IE is not recommended to edit pages. You may experience some unexpected behavior while moving widgets or other events. It\'s better to use another Browser.');
}*/
</script>
</span><br />
<span style="padding:2px 2px 2px 2px;color:white; width:90%; display:block;" id="dim" ></span>
</div>
		


<div<?php 
	if($align!=false){
		echo ' align="'.htmlspecialchars($align).'"';
	} ?> id="canvasAligner">
	<div id="widgets_container" align="<?php echo trans('_LANG_DIRECTION')=='ltr'?'left':'right' ?>" style="border:1px dashed gray;<?php
		echo ArtaString::stickvars($this->get('params'), '; ', ': ');
	?>">
		<?php
			echo $this->get('pages');
		?>
		<span class="resizeHandle" id="canvasResizer"></span>
		
		<img align="left" src="<?php echo Imageset('trash.png') ?>" id="delete_handler" onclick="alert('<?php
	echo trans('DROP WIDGETS HERE TO DELETE');
?>')" style="padding: 10px; position:relative; top:-74px; border: 2px dotted darkblue;background:#CEEFFF;"/>
		
	</div>
</div>
<?php
	$t=ArtaLoader::Template();
	$t->addtoTmpl('<script>widgets_move();
	vp=document.viewport.getWidth();
	$("PageTools").style.left=(vp - $("PageTools").getWidth() -10) + \'px\';
	new Draggable("PageTools",{zindex:999,snap:5});</script>', 'beforebodyend');
?>
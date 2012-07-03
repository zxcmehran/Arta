<?php if(!defined('ARTA_VALID')){die('No access');} 
$v=$this->get('data');
?>
<fieldset style="width:50%;">
<legend><?php
	echo trans('WIDGET SELECTION');
?></legend>
<form name="widform" method="get" action="<?php echo makeURL('index.php') ?>?pack=pages">
<?php
$x=$_GET;
if(isset($x['new_widget'])){
	unset($x['new_widget']);
}
	foreach($x as $kk=>$vv){
			echo '<input name="'.htmlspecialchars($kk).'" value="'.htmlspecialchars($vv).'" type="hidden"/>';
	}
?>
<?php
	echo trans('WIDGET TYPE');
?>: <select name="new_widget" onchange="if(this.options[0].selected == true){document.location.href='index.php?<?php
	echo ArtaUrl::makeupQuery(array_merge($x,array('new_widget'=>'0')));
?>';}else{document.widform.submit();}">
	<?php
	echo $this->getWidgets();
?>
</select>
</form>
</fieldset>
<form method="post" action="<?php makeURL('index.php') ?>?pack=pages">
<fieldset>
<legend><?php
	echo trans('WIDGET CONTENT');
?></legend>
<?php
	echo trans('TITLE');
?>: <input type="text" name="title" value="<?php echo $v->title;?>"/>
<br/><br/>
<?php
	if((int)$v->widget>0){
		echo $this->getSettings();
	}else{
		echo ArtaTagsHtml::addEditor('content', $v->content);
	}
?>
</fieldset>
<fieldset>
<legend><?php
	echo trans('STYLING');
?></legend>
<table>
<tr><td style="label"><?php echo trans('width') ?></td><td style="value"><input type="text" name="params[width]" value="<?php echo @htmlspecialchars($v->params['width']); ?>"/></td></tr>
<tr><td style="label"><?php echo trans('height') ?></td><td style="value"><input type="text" name="params[height]" value="<?php echo @htmlspecialchars($v->params['height']); ?>"/></td></tr>
<tr><td style="label"><?php echo trans('TOP') ?></td><td style="value"><input type="text" name="params[top]" value="<?php echo @htmlspecialchars($v->params['top']); ?>"/></td></tr>
<tr><td style="label"><?php echo trans('left') ?></td><td style="value"><input type="text" name="params[left]" value="<?php echo @htmlspecialchars($v->params['left']); ?>"/></td></tr>
<tr><td style="label"><?php echo trans('other CSS TAGS') ?></td><td style="value"><textarea name="params[other]"><?php echo @htmlspecialchars($v->params['other']); ?></textarea></td></tr>
</table>
</fieldset>
<input type="submit" value="<?php
	echo trans('SUBMIT');
?>"/>
<input type="hidden" name="pack" value="pages"/>
<input type="hidden" name="task" value="saveWidget"/>
<?php
	if(isset($v->id)){
		echo '<input type="hidden" name="id" value="'.$v->id.'"/>';
	}
	if($v->widget>0){
		echo '<input type="hidden" name="widget" value="'.htmlspecialchars($v->widget).'"/>';
	}
?>
<?php
	$pid=getvar('pid', '', '', 'int');
	echo '<input type="hidden" name="pid" value="'.htmlspecialchars($pid).'"/>';
?>
</form>
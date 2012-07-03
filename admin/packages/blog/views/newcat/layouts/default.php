<?php if(!defined('ARTA_VALID')){die('No access');}
$i=$this->get('cat');
if($i->id>0){
	echo ArtaTagsHtml::openTranslation($i->title,$i->id, 'blogcat');
}
?>
<form method="post" name="adminform" action="<?php echo ('index.php'); ?>">
<table class="admintable">
<tr>
	<td class="label"><?php echo trans('CATEGORY TITLE') ?></td><td class="value"><input type="text" name="title" value="<?php echo htmlspecialchars($i->title);?>" maxlength="255"/></td>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('CATEGORY TITLE ALIAS'), trans('CATEGORY TITLE ALIAS DESC')); ?></td><td class="value"><input type="text" name="sef_alias" value="<?php echo htmlspecialchars($i->sef_alias);?>" maxlength="255"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('CATEGORY PARENT') ?></td><td class="value">
	<?php
	$c=$this->get('cats');
	$r=array(trans('NO PARENT'));
	$is_child=false;
	$params=array();
	foreach($c as $k=>$v){
		$level=0;
		$j=0;
		while(@$v->title{$j}=='.'){
			$level++;
			$j++;
		}
		
		$level=$level/2;
		if($v->id==$i->id){
			$is_child=$level;
		}

		if($v->id!==$i->id && ($is_child==false || $level<=$is_child)){
			$is_child=false;
			$r[$v->id]=($v->title);
		}else{
			$r[$v->id]=($v->title);
			$params[$v->id]=array('style'=>'background:pink;');
		}
	}
	echo ArtaTagsHtml::select('parent', $r, $i->parent, 1, array('style'=>'height:100px;'),$params);
	if(count($params)){
		echo ArtaTagsHtml::WarningTooltip(trans('CATEGORY PARENT CANT BE FROM ITS CHILDS HIGHLIGHTED'));
	}
?>
	</td>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('CATEGORY DENIED'),trans('CATEGORY DENIED DESC')); ?></td><td class="value">
	<?php 
	 	echo $this->get('pc');
	 ?>
	</td>
</tr>
<tr>
	<td class="label"><?php echo trans('CATEGORY DESC') ?></td><td class="value" colspan="3"><?php
	echo ArtaTagsHtml::addEditor('desc', $i->desc);
?></td>
</tr>
</table>
<input type="hidden" name="pack" value="blog"/>
<input type="hidden" name="view" value="category"/>
<input type="hidden" name="task" value="saveCat"/>
<?php
$tmpl=ArtaLoader::Template();
$tmpl=$tmpl->getTmpl();
if($tmpl=='package'){
	?>
<input type="hidden" name="close" value="1"/><?php
}
$v=getVar('ids', array(0), '', 'array');
$v=ArtaFilterinput::clean(@array_shift($v), 'int');
	if($v>0){
?>
<input type="hidden" name="id" value="<?php
	echo $v;
?>"/>
<?php
	}
?>
</form>
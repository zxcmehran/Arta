<?php if(!defined('ARTA_VALID')){die('No access');}
$page=$this->get('data');
if($page->id>0){
	echo ArtaTagsHtml::openTranslation($page->title,$page->id, 'page');
}
?>
<script>
function setDynamic(i){
	if(i==0){
		$('for_dynamics').hide();
		$('widgets').hide();
		$('pagecontent').show();
	}else{
		$('for_dynamics').show();
		$('widgets').show();
		$('pagecontent').hide();
	}
}
</script>
<form name="adminform" action="<?php echo makeURL('index.php');?>" method="post">
<br />
 &nbsp;&nbsp;&nbsp;&nbsp;<?php echo trans('PAGE TYPE').': &nbsp;&nbsp;&nbsp;&nbsp; <input name="is_dynamic" type="radio" value="0"'.($page->is_dynamic?'':' checked="checked"').' id="is_dynamic_0" onclick="setDynamic(0)"><label for="is_dynamic_0">'.trans('STATIC').'</label> <input name="is_dynamic" type="radio" value="1"'.($page->is_dynamic?' checked="checked"':'').' id="is_dynamic_1" onclick="setDynamic(1)"><label for="is_dynamic_1">'.trans('DYNAMIC').'</label> &nbsp;'.ArtaTagsHtml::Tooltip('<img src="'.Imageset('info.png').'" alt="i"/>', trans('PAGE TYPE DESC'));?>
<fieldset>
<legend><?php echo trans('PAGE'); ?></legend>
<table class="admintable">
<tr>
	<td class="label"><?php echo trans('TITLE'); ?>: </td><td class="value"><input name="title" value="<?php echo htmlspecialchars($page->title); ?>" maxlength="255"/></td>
		<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('TITLE ALIAS'), trans('TITLE ALIAS DESC')); ?>: </td><td class="value"><input name="sef_alias" value="<?php echo htmlspecialchars($page->sef_alias); ?>" maxlength="255"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('DESCRIPTION'); ?>: </td><td class="value"><textarea name="desc"><?php echo htmlspecialchars($page->desc); ?></textarea></td>
	<td class="label"><?php echo trans('TAGS'); ?>: </td><td class="value"><input name="tags" value="<?php echo htmlspecialchars($page->tags); ?>" maxlength="255"/></td>
</tr>
<tr id="for_dynamics">
	<td class="label"><?php echo trans('CANVAS HEIGHT'); ?>: </td><td class="value"><input name="height" value="<?php echo @ArtaFilterinput::clean($page->params['height'], 'int'); ?>"/> <?php echo trans('PIXELS'); ?></td>
	<td class="label"><?php echo trans('CANVAS WIDTH'); ?>: </td><td class="value"><input name="width" value="<?php echo @ArtaFilterinput::clean($page->params['width'], 'int'); ?>"/> <?php echo trans('PIXELS').' &nbsp;&nbsp;&nbsp;&nbsp;'.trans('SET ZERO TO MAKE AUTO'); ?></td>
</tr>
<tr>
	<td class="label"><?php echo trans('PAGE TEMPLATE'); ?>: </td><td class="value"><?php

	if((string)@$page->params['template']==''){
		$page->params['template']='-';
	}

	$db=ArtaLoader::DB();
	$sql="SELECT * FROM #__templates WHERE client='site' ORDER BY id";
	$db->setQuery($sql);
	$temps=$db->loadObjectList();
	$result=array('-'=>trans('DEFAULT TEMPLATE'));
	foreach($temps AS $v){
		$result[$v->name]=$v->title;
	}
	$x=array();
	$path=ArtaURL::getSiteURL();
	$id=ArtaString::makeRandStr(8);
	foreach($result as $key=>$tmpl){
		if($key!=='-'){
			$tip=JSValue('<img style="position:absolute;z-index:20;" src="'.$path.'templates/'.$key.'/thumb.png"/>');
			$x[$key]['onmouseover']='$("tmpl_preview_'.$id.'").innerHTML= "'.$tip.'";';
		}
	}
	$preview='<span style="vertical-align:top;" id="tmpl_preview_'.$id.'"></span>';
	$sp=array('onmouseout'=>'$("tmpl_preview_'.$id.'").innerHTML= "";');
	
	echo ArtaTagsHtml::select('page_template', $result, (string)@$page->params['template'], 0, $sp, $x).$preview;
	 ?></td>
	 <td class="label"><?php echo trans('PUBLISHED'); ?>: </td><td class="value"><?php echo ArtaTagsHtml::radio('enabled', array(trans('NO'), trans('YES')), (int)$page->enabled); ?></td>
</tr>
</table>
</fieldset>
<fieldset>
<legend><?php echo trans('MODULES'); ?></legend>
<table class="admintable">
<tr>
	<td class="label"><?php echo trans('SELECTED MODULES TO SHOW'); ?>: </td><td class="value"><?php echo ArtaTagsHtml::select('mods[]', $this->getAllModules(), (array)explode(',',$page->mods),2, array('style'=>'height:100px')); ?></td>
	<td class="value">
		<?php echo ArtaTagsHtml::radio('deny_type', array(trans('show_these'),trans('show_others')),$page->deny_type); ?>
	</td>
</tr>
</table>
</fieldset>
<fieldset>
<legend><?php echo trans('PERMS'); ?></legend>
<table class="admintable">
<tr>
	<td class="label"><?php echo trans('DENIED'); ?>: </td><td class="value"><?php 
	if($page->denied==''){
		$page->denied=array();
	}else{
		$page->denied=(array)explode(',',$page->denied);
	}
	echo ArtaTagsHtml::preFormItem('denied', $page->denied, 'usergroups', '$options["select_type"]=2;$options["guest"]=1;'); ?></td>
	<td class="value">
		<?php echo ArtaTagsHtml::radio('denied_type', array(trans('deny_these'),trans('deny_others')),$page->denied_type); ?>
	</td>
</tr>
</table>
</fieldset>

<?php
	if($page->id!==0 && $page->is_dynamic==1){
?>
<fieldset id="widgets">
<legend><?php echo trans('WIDGETS'); ?></legend>
<?php
	$wids=$this->get('wids');
	if(@count($wids)>0){
		echo '<ol>';
			foreach($wids as $v){
				echo '<li>'.htmlspecialchars($v->title).'</li>';
			}
		echo '</ol>';
	}else{
		echo '<p>'.trans('NO WIDGETS FOUND').'</p>';
	}
?>
<a href="<?php
	echo ArtaURL::getSiteURL();
?>index.php?pack=pages&task=openenv&pid=<?php echo $page->id; ?>" target="_blank"><?php echo trans('EDIT WIDGETS'); ?></a> <?php
	echo ArtaTagsHtml::WarningTooltip(trans('WIDGETS EDIT NOTE'));
?>
</fieldset>
<?php
	}else{
?>
<fieldset style="display: none;" id="widgets">
<legend><?php echo trans('WIDGETS'); ?></legend>
	<?php echo trans('WIDGETS WILL BE EDITABLE AFTER SAVE'); ?>
</fieldset>
<?php
	}
?>
<fieldset id="pagecontent">
<legend><?php echo trans('CONTENT'); ?></legend>
	<?php echo ArtaTagsHtml::addEditor('content', $page->content); ?>
</fieldset>
<input type="hidden" name="pack" value="pages"/>
<input type="hidden" name="task" value="save"/>
<?php
	if($page->id>0){
?>
<input type="hidden" name="pid" value="<?php echo $page->id; ?>"/>
<?php
	}
?>
</form>
<script>setDynamic(<?php echo (int)$page->is_dynamic; ?>)</script>

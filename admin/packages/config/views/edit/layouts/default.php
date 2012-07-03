<?php 
if(!defined('ARTA_VALID')){die('No access');}
$set=$this->get('data');
$lang=ArtaLoader::Language();
$admin=array();
$site=array();
$both=array();
foreach($set as $k=>$v){
	$v->default= unserialize($v->default);
	$v->val=unserialize($v->value);
	if($v->client=='site'){
		$site[]=$v;
	}elseif($v->client=='admin'){
		$admin[]=$v;
	}else{
		$v->client='*';
		$both[]=$v;
	}
	
}
?>
<h3><?php printf(trans('CONFIGURATION OF_'),$this->get('title')); echo ' ('.trans(getVar('extype')).')';?></h3>
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" name="adminform">
<?php
	if(count($admin)!==0){
?>
<fieldset><legend><?php echo trans('ADMIN'); ?></legend>
<table class="admintable">

<tbody>
<?php
foreach($admin as $v){
?>	
<tr>
<td class="label"><?php 
if($lang->exists('S_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.strtoupper($v->var).'_D')){
	$desc=trans('S_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.strtoupper($v->var).'_D');
}else{
	$desc='';
}

echo ArtaTagsHtml::Tooltip(
	trans('S_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.strtoupper($v->var).'_L'), 
	$desc); ?></td>
<td class="value">
<?php 
	echo ArtaTagsHtml::PreFormItem('settings['.$v->var.'|'.$v->client.']', $v->val, $v->vartype, $v->vartypedata)."\n";
 ?>
 </td>
 </tr>
 <?php } ?>

</tbody>
</table>
</fieldset>
<?php
	}
?>


<?php
	if(count($site)!==0){
?>
<fieldset><legend><?php echo trans('SITE'); ?></legend>
<table class="admintable">

<tbody>
<?php
foreach($site as $v){
?>	
<tr>
<td class="label" style="width: 35%;"><?php 

if($lang->exists('S_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.strtoupper($v->var).'_D')){
	$desc=trans('S_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.strtoupper($v->var).'_D');
}else{
	$desc='';
}

echo ArtaTagsHtml::Tooltip(
	trans('S_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.strtoupper($v->var).'_L'), 
	$desc); ?></td>
<td class="value">
<?php
	echo ArtaTagsHtml::PreFormItem('settings['.$v->var.'|'.$v->client.']', $v->val, $v->vartype, $v->vartypedata)."\n";
 ?>
 </td>
 </tr>
 <?php } ?>

</tbody>
</table>
</fieldset>
<?php
	}
?>


<?php
	if(count($both)!==0){
?>
<fieldset><legend><?php echo trans('BOTH SITE AND ADMIN'); ?></legend>
<table class="admintable">

<tbody>
<?php
foreach($both as $v){
?>	
<tr>
<td class="label"><?php 

if($lang->exists('S_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.strtoupper($v->var).'_D')){
	$desc=trans('S_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.strtoupper($v->var).'_D');
}else{
	$desc='';
}

echo ArtaTagsHtml::Tooltip(
	trans('S_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.strtoupper($v->var).'_L'), 
	$desc); ?></td>
<td class="value">
<?php
	echo ArtaTagsHtml::PreFormItem('settings['.$v->var.'|'.$v->client.']', $v->val, $v->vartype, $v->vartypedata)."\n";
 ?>
 </td>
 </tr>
 <?php } ?>

</tbody>
</table>
</fieldset>
<?php
	}
?>


<?php
	/*if(getVar('tmpl')=='package'){
		echo '<input type="submit" value="'.trans('submit').'"/>';
		echo '<input type="reset" value="'.trans('reset').'"/>';
	}*/
?>
<input type="hidden" name="pack" value="config"/>
<input type="hidden" name="task" value="save"/>
<input type="hidden" name="view" value="edit"/>
<input type="hidden" name="extype" value="<?php echo getVar('extype','','','string'); ?>"/>
<input type="hidden" name="client" value="<?php echo $v->client; ?>"/>
<input type="hidden" name="extname" value="<?php echo $v->extname; ?>"/>
</form>
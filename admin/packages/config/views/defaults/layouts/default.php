<?php 
if(!defined('ARTA_VALID')){die('No access');}
$lang=ArtaLoader::Language();
?>
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" name="adminform">
<fieldset><legend><?php echo trans('DEFAULT SETTING PARAMETERS'); ?></legend>
<table class="admintable">

<tbody>
<?php
if(count($this->get('s')) == 0){
	echo '<tr><td colspan=2><p align="center">'.trans('NO SETTINGS').'</p></td></tr>';
}
foreach($this->get('s') as $v){
	$v->default= unserialize($v->default);
?>	
<tr>
<td class="label"><?php 
if($lang->exists('SETTING_'.strtoupper($v->var).'_DESC')){
	$desc=trans('SETTING_'.strtoupper($v->var).'_DESC');
}else{
	$desc='';
}

echo ArtaTagsHtml::Tooltip(trans('SETTING_'.strtoupper($v->var).'_LABEL'), $desc); ?></td>
<td class="value">
<?php
	echo ArtaTagsHtml::PreFormItem('fields['.addcslashes($v->var,'|').'|'.addcslashes($v->extname,'|').'|'.addcslashes($v->extype,'|').'|'.addcslashes($v->fieldtype,'|').']', $v->default, $v->vartype, '$scope="defaults";'.$v->vartypedata)."\n";
 ?>
 </td>
 </tr>
 <?php } ?>

</tbody>

</table>
</fieldset>
<fieldset><legend><?php echo trans('DEFAULT MISC PARAMETERS'); ?></legend>
<table class="admintable">
<tbody>
<?php
if(count($this->get('m')) == 0){
	echo '<tr><td colspan=2><p align="center">'.trans('NO SETTINGS').'</p></td></tr>';
}
foreach($this->get('m') as $v){
	$v->default= unserialize($v->default);
?>	
<tr>
<td class="label"><?php 

if($lang->exists('MISC_'.strtoupper($v->var).'_DESC')){
	$desc=trans('MISC_'.strtoupper($v->var).'_DESC');
}else{
	$desc='';
}

echo ArtaTagsHtml::Tooltip(trans('MISC_'.strtoupper($v->var).'_LABEL'), $desc); ?></td>
<td class="value">
<?php
	echo ArtaTagsHtml::PreFormItem('fields['.addcslashes($v->var,'|').'|'.addcslashes($v->extname,'|').'|'.addcslashes($v->extype,'|').'|'.addcslashes($v->fieldtype,'|').']', $v->default, $v->vartype, '$scope="defaults";'.$v->vartypedata)."\n";
	
	if($v->show_on_register==true){
		echo ' '.ArtaTagsHtml::Tooltip('<img src="'.imageset('info.png').'" alt="i" />', trans('WILL BE SHOWN ON REGISTER PAGE'));
	}
 ?>
 </td>
 </tr>
 <?php } ?>

</tbody>
</table>
</fieldset>
<input type="hidden" name="pack" value="config"/>
<input type="hidden" name="task" value="save_defaults"/>
<input type="hidden" name="view" value="defaults"/>
</form>
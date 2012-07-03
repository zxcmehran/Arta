<?php if(!defined('ARTA_VALID')){die('No access');}
$d=$this->get('data');
?>
<form name="adminform" action="<?php echo makeURL('index.php');?>" method="post">
<fieldset>
	<legend><?php echo trans('LINK DETAILS');?></legend>
	<table class="admintable">
		<tr><td class="label"><?php echo trans('TITLE'); ?></td><td class="value"><input name="title" value="<?php echo $d->title ?>" maxlength="255"/></td></tr>
		
		
		<tr><td class="label"><?php echo trans('link'); ?></td><td class="value"><input name="link" value="<?php echo $d->link ?>" maxlength="255"/></td></tr>
		
		<tr><td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('alttxt'), trans('alttxt DESC')); ?></td><td class="value"><textarea name="alt" cols="30" rows="5"><?php echo $d->alt ?></textarea></td></tr>
		
		<tr><td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('qimg'), trans('qimg DESC')); ?></td><td class="value" dir="ltr" align="<?php echo trans('_LANG_DIRECTION')=='ltr'?'left':'right'; ?>">
			<input type="checkbox" onclick="if(this.checked==true){$('relativeTxt').style.color='black';$('relativeTxt').style.textDecoration='none';}else{$('relativeTxt').style.color='gray';$('relativeTxt').style.textDecoration='line-through';}" name="relative" value="1" id="relativeBox" <?php
	if(substr($d->img,0,1)=='#'){
		$d->img=substr($d->img,1);
	}else{
		echo 'checked="checked"';
	}
	
	ArtaTagsHtml::addtoTmpl("<script>if($('relativeBox').checked==true){\$('relativeTxt').style.color='black';\$('relativeTxt').style.textDecoration='none';}else{\$('relativeTxt').style.color='gray';\$('relativeTxt').style.textDecoration='line-through';}</script>",'beforebodyend');
?>/>
			<label for="relativeBox" id="relativeTxt"><?php
	$template=ArtaLoader::Template();
	echo ArtaURL::getDir()."imagesets/".$template->getImgSetName()."/";
?></label>
			<input name="img" value="<?php echo htmlspecialchars($d->img); ?>" maxlength="255" /> <?php echo ArtaTagsHtml::WarningTooltip(trans('IMG LINK WARNING'));?></td></tr>
		
		<tr><td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('acckey'), trans('acckey DESC')); ?></td><td class="value"><?php
	$acc=explode('+', $d->acckey);
	$key=$acc[count($acc)-1];
	echo '<input name="key" type="text" maxlength="1" size="1" value="'.htmlspecialchars($key).'"/> + ';
	echo '<input name="key_ctrl" type="checkbox" '.(in_array('ctrl',$acc)?'checked="checked"':'').'/>Ctrl + ';
	echo '<input name="key_alt" type="checkbox" '.(in_array('alt',$acc)?'checked="checked"':'').'/>Alt + ';
	echo '<input name="key_shift" type="checkbox" '.(in_array('shift',$acc)?'checked="checked"':'').'/>Shift';
?></td></tr>
	</table>
</fieldset>
<input name="pack" value="quicklink_manager" type="hidden"/>
<input name="task" value="save" type="hidden"/>
<?php
if(@$d->id>0){
	echo '<input name="id" value="'.$d->id.'" type="hidden"/>';
}
?>
</form>

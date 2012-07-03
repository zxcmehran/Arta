<?php if(!defined('ARTA_VALID')){die('No access');}
$i=$this->get('var');
$this->setTitle(trans('PERM EDITOR'));
if(getVar('done',false)==false){
?>
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" name="adminform">
<?php
	echo trans('PERMS FOR').': '.trans('LINK').' - '.htmlspecialchars($i->title);
?>
<br/>
<?php
	echo trans('perms of links desc');
?>
<br/>
<table class="admintable"><tr>
<td><?php echo trans('DENIED') ?></td><td><?php

	if((string)$i->denied==''){
		$i->denied= array();
	}else{
		$i->denied=explode(',',$i->denied);
	}
	 echo ArtaTagsHtml::PreFormItem('denied', $i->denied, 'usergroups', '$options["select_type"]=2;$options["guest"]=1;'); ?></td><td><?php echo ArtaTagsHtml::select('denied_type', array(0=>trans('deny_these'), 1=>trans('deny_others')), (int)$i->denied_type); ?></td>
</tr>
<tr align="center"><td><input type="submit" value="<?php
	echo trans('SUBMIT');
?>" /></td></tr>
</table>

<input type="hidden" name="pid" value="<?php
	echo htmlspecialchars($i->id);
?>"/>
<input type="hidden" name="pack" value="links"/>
<input type="hidden" name="task" value="saveM"/>
</form>
<?php
	}else{
?>
<input type="button" value="<?php
	echo trans('OK');
?>" onclick="window.opener.reloadPage();" />
<?php
	}
?>
<br/><br/>
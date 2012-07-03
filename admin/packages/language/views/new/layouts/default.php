<?php if(!defined('ARTA_VALID')){die('No access');}
$data=$this->get('data');
$lang=$this->get('lang');
?>
<form name="adminform" action="<?php echo makeURL('index.php');?>" method="post">
<fieldset>
<legend><?php echo htmlspecialchars($lang->title); ?></legend>
<?php
	echo $data;
?>
</fieldset>
<input type="hidden" name="pack" value="language"/>
<input type="hidden" name="task" value="save"/>
<input type="hidden" name="id" value="<?php echo htmlspecialchars(getVar('id','','','int')); ?>"/>
<input type="hidden" name="lang" value="<?php echo htmlspecialchars(getVar('lang','','','int')); ?>"/>
<input type="hidden" name="group" value="<?php echo htmlspecialchars(getVar('group','','','string')); ?>"/>

</form>

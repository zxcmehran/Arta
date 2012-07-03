<?php if(!defined('ARTA_VALID')){die('No access');}?>
<fieldset>
<legend><?php echo trans('RULES'); ?></legend>
<?php
	echo $this->get('rules');
?>
</fieldset>
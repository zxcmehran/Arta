<?php if(!defined('ARTA_VALID')){die('No access');} echo $this->get('prefix');?>
<?php echo trans('NOT LOGGED IN'); ?> | 
<a href="<?php echo ('index.php?pack=user'); ?>"><?php echo trans('LOGIN'); ?></a> | 
<a href="<?php echo ('index.php?pack=user&view=register'); ?>"><?php echo trans('SMALL_REGISTER AT HERE'); ?></a>
<?php echo $this->get('suffix'); ?>

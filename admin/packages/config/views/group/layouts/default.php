<?php 
if(!defined('ARTA_VALID')){die('No access');}
?>
<?php echo trans('CONFIG INTRO'); ?> : <br/><br/>

<div class="grid">
<?php foreach($this->get('groups') as $v){ ?>
	<span>
		<a href="<?php echo ('index.php'.'?pack=config&'.$v[2]); ?>">
			<img src="<?php echo Imageset($v[0].'.png'); ?>"/>
			<span><?php echo $v[1]; ?></span>
		</a>
	</span>
<?php } ?>
</div>

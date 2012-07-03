<?php if(!defined('ARTA_VALID')){die('No access');} 
$d=$this->get('data');
if(is_array($d)){
	?>
<table class="admintable">

<?php
	if(getvar('infotype')=='cronlogs'){
		?>
	<thead>
	<tr><th><?php echo trans('cron_name');?></th><th><?php echo trans('cron_time');?></th><th><?php echo trans('cron_message');?></th></tr>
	</thead>
		<?php
	}elseif(getvar('infotype')=='adminalerts'){
		?>
	<thead>
	<tr><th><?php echo trans('alert_at');?></th><th><?php echo trans('alert_when');?></th><th><?php echo trans('alert_tip');?></th><th><?php echo trans('alert_time');?></th><th><?php echo trans('alert_times_occured');?></th></tr>
	</thead>
	<?php
	}
	foreach($d as $k=>$v){
?>
<tr>
	<td class="label" width="20%"><?php echo $k;?></td><td class="value"><?php echo  ($v);?></td>
</tr>
<?php
	}
?>
</table>
	<?php
}else{
	echo $d;
}
?>
<?php
	if(getvar('infotype')=='cronlogs'){
		?>
	<form action="<?php echo makeURL('index.php'); ?>" name="adminform">
	<input name="pack" value="info" type="hidden"/>
	<input name="task" value="emptycron" type="hidden"/>
	</form>
		<?php
	}elseif(getvar('infotype')=='adminalerts'){
		?>
	<form action="<?php echo makeURL('index.php'); ?>" name="adminform">
	<input name="pack" value="info" type="hidden"/>
	<input name="task" value="emptyalert" type="hidden"/>
	</form>
	<?php
	}
?>

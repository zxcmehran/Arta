<?php if(!defined('ARTA_VALID')){die('No access');} ?>
<table class="quicklink">
	<?php
	$items=$this->get('items');
	foreach($items as $v){ 
		?>
	<tr><td style="width:24px;height:24px;text-align:center;"><a href="<?php echo htmlspecialchars($v->link); ?>"<?php echo $v->alt || $v->acckey ? ' title="'.htmlspecialchars($v->alt.' ['.$v->acckey.']').'"' : '';?>><img class="quicklinkicon" src="<?php echo ($v->img{0}=='/'? substr($v->img,1) : Imageset($v->img)); ?>"/><br/></a></td><td><a href="<?php echo htmlspecialchars($v->link); ?>"<?php echo $v->alt || $v->acckey ? ' title="'.htmlspecialchars($v->alt.' ['.$v->acckey.']').'"' : '';?>><?php echo htmlspecialchars($v->title); ?></a></td></tr>
	<?php } ?>
</table>

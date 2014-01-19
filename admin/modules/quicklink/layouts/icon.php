<?php if(!defined('ARTA_VALID')){die('No access');} ?>
<table class="quicklink">
<tr>
	<?php
	$items=$this->get('items');
	$i=0;
	foreach($items as $v){ 
		if($i == 0){echo '</tr><tr>';}
		?>
	<td class="icon" style="text-align:center;"><a href="<?php echo ($v->link); ?>"<?php echo $v->alt || $v->acckey ? ' title="'.htmlspecialchars($v->alt.' ['.$v->acckey.']').'"' : '';?>><img class="quicklinkicon" src="<?php echo ($v->img{0}=='/'? substr($v->img,1) : Imageset($v->img)); ?>"/><br/><?php echo htmlspecialchars($v->title); ?></a></td>
	<?php if($i== 1){$i=0;}else{$i=1;}
		} ?>
</tr>
</table>

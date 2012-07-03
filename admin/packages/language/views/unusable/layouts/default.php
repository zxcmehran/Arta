 <?php if(!defined('ARTA_VALID')){die('No access');}?>
<form name="adminform" action="<?php echo ArtaURL::make('index.php'); ?>">
<table class="admintable">
<thead>
	<tr>
		<th width="16"><input class="idcheck" id="toggle" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), $('toggle'));"/></th>
		<th><?php echo trans('TITLE'); ?></th>
		<th width="25%"><?php echo trans('TYPE') ?></th>
		<th width="25%"><?php echo trans('LANG') ?></th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th><input class="idcheck" id="toggle2" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), $('toggle2'));"/></th>
		<th><?php echo trans('TITLE'); ?></th>
		<th><?php echo trans('TYPE') ?></th>
		<th><?php echo trans('LANG') ?></th>
	</tr>
</tfoot>
<tbody>
<?php $i=0; 
if(@!is_array($this->get('data')) || count($this->get('data'))==0){
	$msg=trans('NO RESULTS');
	echo '<tr><td colspan="4" align="center">'.$msg.'</td></tr>';
}else{
	$r=$this->get('data');
foreach($r as $k=>$v){ ?>
	<tr<?php echo ' class="row'.$i.'"' ?>>
		<td><input type="checkbox" name="ids[]" value="<?php echo $v->id; ?>" id="ids" class="idcheck"/></td>
		<td><?php echo htmlspecialchars($v->title); ?></td>
		<td align="center"><?php echo htmlspecialchars($v->group_title); ?></td>
		<td align="center"><?php echo htmlspecialchars($v->lang); ?></td>
	</tr>
<?php if($i==0){$i=1;}else{$i=0;} } }?>
</tbody>
</table>
<input type="hidden" name="pack" value="language"/>
<input type="hidden" name="task" value="deleteUnusable"/>
<input type="hidden" name="view" value="translations"/>
</form>
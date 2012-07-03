<?php 
if(!defined('ARTA_VALID')){die('No access');}

?>
<?php echo trans('EXTENSION INTRO'); ?> : <br/><br/>
<table class="admintable">
<thead>
<tr><th width="8%"></th><th><?php echo trans(getVar('extype')); ?></th></tr>
</thead>
<tbody>
<?php $rownum=0;
$model=$this->getModel();
if(count($this->get('data')) == 0 || $this->get('data')==null){
	echo '<tr><td colspan=2>'.ArtaTagsHtml::msgBox(trans('NO SETTINGS')).'</td></tr>';
}
$s=0;
foreach($this->get('data') as $v){
	if(@$v->c > 0){
		$s=1;
	?>
<tr class="row<?php echo $rownum;if($rownum==0){$rownum=1;}else{$rownum=0;} ?>">
	<td><center><img src="<?php echo $v->image{0}=='#' ? substr($v->image, 1) : imageset($v->image); ?>"/></center></td>
	<td><a href="<?php echo ('index.php'.'?pack=config&view=edit&extype='.getVar('extype').'&extname='.$v->name.'&client='.$v->client); ?>"><?php echo htmlspecialchars($v->title); ?></a></td>
</tr>
<?php } 
}
//if($s==0){echo '<tr><td colspan=2><p align="center">'.trans('NO SETTINGS').'</p></td></tr>';}
?>
</tbody>
</table>

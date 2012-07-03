<?php if(!defined('ARTA_VALID')){die('No access');}
$m=$this->getModel();
ArtaTagsHtml::addHeader('<script>var perm_editor; function reloadPage(){perm_editor.close();location.reload();}</script>');
echo '<table><tr><td>';
echo ArtaTagsHtml::SortControls(array('id'=>trans('id'),'title'=>trans('title'),'module'=>trans('name'),'location'=>trans('location'),'order'=>trans('order'),'enabled'=>trans('ENABILITY')), 'order');
echo ' </td><td> ';
echo ArtaTagsHtml::FilterControls('location', $m->getLocations(), trans('location'));
echo ' </td><td> ';
echo ArtaTagsHtml::FilterControls('client', array('admin'=>trans('ADMIN'), 'site'=>trans('SITE')), trans('CLIENT'));
echo '</td></tr></table>';
?>
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" name="adminform">
<table class="admintable">
<thead>
	<tr>
		<th width="1">
			<?php echo '#'; ?>
		</th>
		<th width="1" nowrap="nowrap">
			<?php echo ArtaTagsHtml::sortLink(trans('id'),'id'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::sortLink(trans('title'),'title'); ?> / 
			<?php echo trans('SHOWAT'); ?> /
			<?php echo trans('ALLOWED UGS'); ?> 
		</th>
		<th width="10%">
			<?php echo ArtaTagsHtml::sortLink(trans('name'),'module'); ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo ArtaTagsHtml::sortLink(trans('location'),'location'); ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo ArtaTagsHtml::sortLink(trans('client'),'client'); ?>
		</th>
		<th width="34" nowrap="nowrap">
			<?php echo ArtaTagsHtml::sortLink(trans('order'),'order'); ?>
		</th>
		<th width="17" nowrap="nowrap">
			<?php echo ArtaTagsHtml::sortLink(trans('enabled'),'enabled'); ?>
		</th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::sortLink(trans('id'),'id'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::sortLink(trans('title'),'title'); ?> / 
			<?php echo trans('SHOWAT'); ?> /
			<?php echo trans('ALLOWED UGS'); ?> 
		</th>
		<th>
			<?php echo ArtaTagsHtml::sortLink(trans('name'),'module'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::sortLink(trans('location'),'location'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::sortLink(trans('client'),'client'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::sortLink(trans('order'),'order'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::sortLink(trans('enabled'),'enabled'); ?>
		</th>
	</tr>
</tfoot>
<tbody>
	<?php
	$i=0;
	$dat=$m->getData();
	$align= trans('_LANG_DIRECTION')=='ltr'?'right':'left';
	if(!@count($dat)>0){
		echo '<tr><td colspan="9">'.ArtaTagsHtml::msgBox(trans('NO RESULTS')).'</td></tr>';
	}else{
	foreach($dat as $k=>$p){
		?>
		<tr <?php echo 'class=row'.$i;?>>
			<td><input type="radio" name="id" value="<?php echo htmlspecialchars($p->id) ?>" class="idcheck"/></td>
			<td><?php echo htmlspecialchars($p->id) ?></td>
			<td><table class="celltable"><tr><td colspan="2"><?php echo '<a href="index.php?pack=modules&view=edit&id='.$p->id.'">'.htmlspecialchars($p->title).'</a>'; ?></td></tr><tr>
			<td class="details"><?php if((string)$p->showat=='' || (string)$p->showat=='-'){
			echo trans('S_ALL');
		}else{
			echo '<b>'.trans('S_SOME').'</b>';
		} ?></td>
			<td class="details" align="<?php echo $align?>"><a href="#hand" onclick="perm_editor=window.open('index.php?pack=modules&view=perm_editor&tmpl=package&pid=<?php echo urlencode($p->id); ?>', 'perm_editor','scrollbars,resizable,location,height=200,width=500');"><?php if($p->denied=='-'){
			echo '<span style="color:red;">'.trans('P_NO').'</span>';
		}elseif((string)$p->denied==''){
			echo '<span style="color:green;">'.trans('P_ALL').'</span>';
		}else{
			echo '<span style="color: rgb(240,210,0);">'.trans('P_SOME').'</span>';
		} ?></a></td></tr></table></td>
			<td align="center"><?php echo (string)$p->module!==''&&(string)$p->module!=='linkviewer' ? htmlspecialchars($p->module) : (substr($p->content,0,5)=='MENU:'&&$p->module=='linkviewer' ? trans('LINKVIEWER MODULE') : trans('NA')); ?></td>
			<td style="text-align:center;"><?php echo htmlspecialchars($p->location) ?></td>
			<td style="text-align:center;"><?php echo trans($p->client) ?></td>
			<td style="text-align:center;"><?php echo ArtaTagsHtml::ReorderControlsUP(
			'index.php?pack=modules&task=reorder&pos='.($p->order-1).'&id='.$p->id.'&token='.ArtaSession::genToken(),
			 (@$dat[$k-1]->location==$p->location && @$dat[$k-1]->client==$p->client),
			  null, (getVar('order_by', 'order','','string')=='order' && getVar('order_dir', 'asc','','string')=='asc'));
			  
			  echo ArtaTagsHtml::ReorderControlsDOWN('index.php?pack=modules&task=reorder&pos='.($p->order+1).'&id='.$p->id.'&token='.ArtaSession::genToken(),
			   (@$dat[$k+1]->location==$p->location && @$dat[$k+1]->client==$p->client),
			    null, (getVar('order_by', 'order','','string')=='order' && getVar('order_dir', 'asc','','string')=='asc'));?>
				</td>
			<td style="text-align:center;"><?php echo ArtaTagsHtml::BooleanControls($p->enabled, 'index.php?pack=modules&task=activate&pid='.urlencode($p->id), 'index.php?pack=modules&task=deactivate&pid='.urlencode($p->id)); ?></td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
	}
?>
</tbody>
</table>
<input type="hidden" name="pack" value="modules"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="display"/>
</form>
<?php
echo ArtaTagsHtml::LimitControls($m->c);
?>


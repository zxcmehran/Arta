<?php if(!defined('ARTA_VALID')){die('No access');}
$m=$this->getModel();
ArtaTagsHtml::addtoTmpl('<script>
var perm_editor;
function openWin(url){
	perm_editor=window.open(url, \'pe\',\'scrollbars,resizable,location,height=200,width=500\');
}
function reloadPage(){perm_editor.close();location.reload();}</script>', 'beforebodyend');
echo '<table><tr><td>';
echo ArtaTagsHtml::SortControls(array('id'=>trans('id'),'title'=>trans('title'),'type'=>trans('LINKTYPE'),'group'=>trans('linkgroup'),'order'=>trans('order'),'enabled'=>trans('ENABLED')), 'order');
echo ' </td><td> ';
$locs=$m->getLocations();
echo ArtaTagsHtml::FilterControls('group', $locs, trans('linkgroup'));
echo '</td><td>';
echo ArtaTagsHtml::FilterControls('type', array('inner'=>trans('linktype_inner'),
												'outer'=>trans('linktype_outer'),
												'default'=>trans('linktype_default')),
								 trans('LINKTYPE'));
echo '</td><td>';
echo ArtaTagsHtml::FilterControls('enabled', array(trans('SHOW DISABLED'), trans('SHOW ENABLED')),
								 trans('ENABILITY'));
echo '</td></tr></table>';
?>
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" name="adminform">
<table class="admintable">
<thead>
	<tr>
		<th width="16">
			<?php echo '#'; ?>
		</th>
		<th width="5%" nowrap="nowrap">
			<?php echo ArtaTagsHtml::SortLink(trans('id'), 'id'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('TITLE'), 'title');  ?>
		</th>
		<th width="15%" nowrap="nowrap">
			<?php echo ArtaTagsHtml::SortLink(trans('LINKTYPE'), 'type'); ; ?>
		</th>
		<th width="15%" nowrap="nowrap">
			<?php echo ArtaTagsHtml::SortLink(trans('LINKGROUP'), 'group');  ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo ArtaTagsHtml::SortLink(trans('ORDER'), 'order');  ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo ArtaTagsHtml::SortLink(trans('ENABLED'), 'enabled');  ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo trans('ALLOWED UGS'); ?>
		</th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('id'), 'id'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('TITLE'), 'title');  ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('LINKTYPE'), 'type'); ; ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('LINKGROUP'), 'group');  ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('ORDER'), 'order');  ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('ENABLED'), 'enabled');  ?>
		</th>
		<th>
			<?php echo trans('ALLOWED UGS'); ?>
		</th>
	</tr>
</tfoot>
<tbody>
	<?php
	$i=0;
	$dat=$m->getData();
	if(@count($dat)==0){
		echo '<tr><td colspan="8">'.ArtaTagsHtml::msgBox(trans('NO RESULTS')).'</td></tr>';
	}else
	foreach($dat as $k=>$p){
		?>
		<tr <?php echo 'class=row'.$i;?>>
			<td width="3%" align="center"><input type="radio" name="id" value="<?php echo htmlspecialchars($p->id) ?>" class="idcheck"/></td>
			<td width="3%" align="center" nowrap="nowrap"><?php echo htmlspecialchars($p->id) ?></td>
			<td><?php echo '<a href="index.php?pack=links&view=edit&id='.$p->id.'">'.htmlspecialchars($p->title).'</a>'; ?></td>
			<td style="text-align:center;"><?php
			 echo trans('linktype_'.$p->type); 
			 ?></td>
			<td style="text-align:center;"><?php echo @htmlspecialchars($locs[$p->group]); ?></td>
			<td style="text-align:center;"><?php echo ArtaTagsHtml::ReorderControlsUP(
			'index.php?pack=links&task=reorder&pos='.($p->order-1).'&id='.$p->id.'&token='.ArtaSession::genToken(),
			 (@$dat[$k-1]->group==$p->group),
			  null, (getVar('order_by', 'order')=='order' && getVar('order_dir', 'asc')=='asc'));
			  
			  echo ArtaTagsHtml::ReorderControlsDOWN('index.php?pack=links&task=reorder&pos='.($p->order+1).'&id='.$p->id.'&token='.ArtaSession::genToken(),
			   (@$dat[$k+1]->group==$p->group),
			    null, (getVar('order_by', 'order')=='order' && getVar('order_dir', 'asc')=='asc'));?>
				</td>
			<td style="text-align:center;"><?php echo ArtaTagsHtml::BooleanControls($p->enabled, 'index.php?pack=links&task=activate&pid='.urlencode($p->id), 'index.php?pack=links&task=deactivate&pid='.urlencode($p->id)); ?></td>
			<td style="text-align:center;"><a href="#hand" onclick="openWin('index.php?pack=links&view=perm_editor&tmpl=package&pid=<?php echo urlencode($p->id); ?>');"><?php if($p->denied=='-'){
			echo '<div style="color:red;">'.trans('P_NO').'</div>';
		}elseif((string)$p->denied==''){
			echo '<div style="color:green;">'.trans('P_ALL').'</div>';
		}else{
			echo '<div style="color: rgb(240,210,0);">'.trans('P_SOME').'</div>';
		} ?></a>
			</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
<input type="hidden" name="pack" value="links"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="list"/>
</form>
<?php
echo ArtaTagsHtml::LimitControls($m->c);
?>


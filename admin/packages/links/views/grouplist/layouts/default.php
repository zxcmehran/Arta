<?php if(!defined('ARTA_VALID')){die('No access');}
$m=$this->getModel();
echo ArtaTagsHtml::SortControls(array('id'=>trans('id'),'title'=>trans('title'),'count'=>trans('LINKCOUNT')), 'id');
?>
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" name="adminform">
<table class="admintable">
<thead>
	<tr>
		<th width="5">
			<?php echo '#'; ?>
		</th>
		<th width="40" nowrap="nowrap">
			<?php echo ArtaTagsHtml::SortLink(trans('id'), 'id'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('TITLE'), 'title'); ?>
		</th>
		<th width="125" nowrap="nowrap">
			<?php echo ArtaTagsHtml::SortLink(trans('LINKCOUNT'), 'count'); ?>
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
			<?php echo ArtaTagsHtml::SortLink(trans('TITLE'), 'title'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('LINKCOUNT'), 'count'); ?>
		</th>
	</tr>
</tfoot>
<tbody>
	<?php
	$i=0;
	$dat=$m->getData();
	if(@count($dat)){
		foreach($dat as $k=>$p){
			?>
			<tr <?php echo 'class=row'.$i;?>>
				<td><input type="radio" name="id" value="<?php echo htmlspecialchars($p->id) ?>" class="idcheck"/></td>
				<td align="center"><?php echo htmlspecialchars($p->id) ?></td>
				<td><?php echo '<a href="index.php?pack=links&view=editgroup&id='.$p->id.'">'.htmlspecialchars($p->title).'</a>'; ?></td>
				<td style="text-align:center;"><a href="index.php?pack=links&where[group]=<?php echo $p->id ?>"><?php
				 echo $p->count; 
				 ?></a></td>
			</tr>
			<?php
			$i= $i==0 ? 1 : 0;
		}
	}else{
		echo '<tr><td colspan="4">'.ArtaTagsHtml::msgBox(trans('NO RESULTS')).'</td></tr>';
	}
?>
</tbody>
</table>
<input type="hidden" name="pack" value="links"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="display"/>
</form>


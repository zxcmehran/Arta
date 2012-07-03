<?php if(!defined('ARTA_VALID')){die('No access');}?>
<?php echo '<table><tr><td>'.ArtaTagsHtml::SortControls(array('id'=>trans('ID'), 'title'=>trans('TITLE'), 'is_dynamic'=>trans('PAGE TYPE'), 'added_by'=> trans('ADDED_BY'),'enabled'=>trans('PUBLISHED'), 'widcount'=>trans('WIDGET COUNT')), 'id', 'desc');
echo '</td><td>'.ArtaTagsHtml::FilterControls('is_dynamic', array(trans('STATIC'), trans('DYNAMIC')),trans('PAGE TYPE')).'</td><td>'.ArtaTagsHtml::FilterControls('added_by', $this->get('users'),trans('ADDED_BY')).'</td><td width="40%">'.trans('WIDGETS EDIT NOTE').'</td></tr></table>';
 ?>
<form method="post" name="adminform" action="<?php echo ArtaURL::make('index.php'); ?>">
<table class="admintable">
<thead>
	<tr>
		<th width="3%">#</th>
		<th width="2%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('TITLE'),'title').' / '.ArtaTagsHtml::SortLink(trans('PAGE TYPE'),'is_dynamic'); ?></th>
		<th width="10%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('PUBLISHED'),'enabled'); ?></th>
		<th width="5%" nowrap="nowrap" colspan="2"><?php echo ArtaTagsHtml::SortLink(trans('WIDGET COUNT'),'widcount'); ?></th>
		<th width="10%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('ADDED_BY'),'added_by'); ?></th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th>#</th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('TITLE'),'title').' / '.ArtaTagsHtml::SortLink(trans('PAGE TYPE'),'is_dynamic'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('PUBLISHED'),'enabled'); ?></th>
		<th colspan="2"><?php echo ArtaTagsHtml::SortLink(trans('WIDGET COUNT'),'widcount'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('ADDED_BY'),'added_by'); ?></th>
	</tr>
</tfoot>
<tbody>
<?php $i=0; 
if(@count($this->get('pagez'))==0){
	echo '<tr><td colspan="7">'.ArtaTagsHtml::msgBox(trans('NO RESULTS')).'</td></tr>';
}else{
	$r=$this->get('pagez');
foreach($r as $k=>$v){ ?>
	<tr<?php echo ' class="row'.$i.'"' ?>>
		<td align="center"><input type="radio" name="id" value="<?php echo $v->id; ?>" class="idcheck"/></td>
		<td><?php echo $v->id; ?></td>
		<td><table class="celltable"><tr><td><a href="<?php echo ('index.php?pack=pages&view=new&id='.$v->id); ?>"><?php echo htmlspecialchars($v->title); ?></a></td>
			<td align="<?php echo trans('_LANG_DIRECTION')=='rtl'?'left':'right'; ?>" class="details"><?php echo $v->is_dynamic?trans('DYNAMIC'):trans('STATIC');?></td></tr></table>
		</td>
		<td align="center"><?php
	echo ArtaTagsHtml::BooleanControls($v->enabled, 'index.php?pack=pages&task=activate&id='.$v->id, 'index.php?pack=pages&task=deactivate&id='.$v->id);
?></td>
		<td align="center"><?php
	echo $v->widcount;
?></td>
		<td style="width:16px;" align="center">
		<?php if($v->is_dynamic==1){ ?>
			<a href="<?php
		echo ArtaURL::getSiteURL();
	?>index.php?pack=pages&task=openenv&pid=<?php echo $v->id; ?>" target="_blank">
				<img src="<?php echo Imageset('edit_small.png') ?>" alt="E" title="<?php echo trans('EDIT WIDGETS'); ?>"/>
			</a>
		<?php }else{ echo '--';}?>
		</td>
		<td align="center"><?php echo '<a href="index.php?pack=user&view=new&ids[]='.$v->added_by.'">'.htmlspecialchars($v->username).'</a>'; ?></td>
	</tr>
<?php if($i==0){$i=1;}else{$i=0;} } }?>
</tbody>
</table>
<input type="hidden" name="pack" value="pages"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="pages"/>
</form>
<?php echo ArtaTagsHtml::LimitControls($this->get('count')); ?>
<?php if(!defined('ARTA_VALID')){die('No access');}?>
<?php echo ArtaTagsHtml::SortControls(array('id'=>trans('ID'), 'title'=>trans('TITLE'), 'link'=> trans('LINK'), 'order'=>trans('ORDER')), 'order', 'asc'); ?>
<br />
<form method="post" name="adminform" action="<?php echo ArtaURL::make('index.php'); ?>">
<table class="admintable">
<thead>
	<tr>
		<th width="2%" nowrap="nowrap">#</th>
		<th width="2%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('TITLE'),'title'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('LINK'),'link'); ?></th>
		<th width="15%" nowrap="nowrap"><?php echo trans('SHORTCUT KEY'); ?></th>
		<th width="40" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('ORDER'),'order'); ?></th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th>#</th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('TITLE'),'title'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('LINK'),'link'); ?></th>
		<th><?php echo trans('SHORTCUT KEY'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('ORDER'),'order'); ?></th>
	</tr>
</tfoot>
<tbody>
<?php $i=0; 
if(@count($this->get('linkz'))==0){
	echo '<tr><td colspan="6">'.ArtaTagsHtml::msgBox(trans('NO RESULTS')).'</td></tr>';
}else{
	$r=$this->get('linkz');
foreach($r as $k=>$v){ ?>
	<tr<?php echo ' class="row'.$i.'"' ?>>
		<td><input type="radio" name="id" value="<?php echo $v->id; ?>" class="idcheck"/></td>
		<td><?php echo $v->id; ?></td>
		<td><a href="<?php echo ('index.php?pack=quicklink_manager&view=new&id='.$v->id); ?>"><?php echo htmlspecialchars($v->title); ?></a></td>
		<td><a href="<?php echo htmlspecialchars($v->link); ?>" target="_blank"<?php
	echo strlen($v->link)>40 ? ' title="'.htmlspecialchars($v->link).'"' : '';
?>><?php echo strlen($v->link)>40 ? htmlspecialchars(substr($v->link,0,40)).' ...' : htmlspecialchars($v->link); ?></a></td>
		<td align="center"><?php $x=explode('+',$v->acckey); $x=array_map('ucfirst', $x); echo htmlspecialchars(implode(' + ', $x)); ?></td>
		<td align="center">
		<?php
		echo ArtaTagsHtml::ReorderControlsUP(
			'index.php?pack=quicklink_manager&task=reorder&token='.ArtaSession::genToken().'&pos='.($v->order-1).'&id='.$v->id,
			 ($v->order > 0),
			  null, (getVar('order_by', 'order','','string')=='order' && getVar('order_dir', 'asc','','string')=='asc'));
	    echo ArtaTagsHtml::ReorderControlsDOWN('index.php?pack=quicklink_manager&task=reorder&token='.ArtaSession::genToken().'&pos='.($v->order+1).'&id='.$v->id,
			   (isset($r[$k+1])),
			    null, (getVar('order_by', 'order','','string')=='order' && getVar('order_dir', 'asc','','string')=='asc'));
?>
		</td>
	</tr>
<?php if($i==0){$i=1;}else{$i=0;} } }?>
</tbody>
</table>
<input type="hidden" name="pack" value="quicklink_manager"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="links"/>
</form>
<?php echo ArtaTagsHtml::LimitControls($this->get('count')); ?>
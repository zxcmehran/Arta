<?php if(!defined('ARTA_VALID')){die('No access');}?>
<table><tr><td>
<?php echo ArtaTagsHtml::SortControls(array('id'=>trans('ID'), 'name'=>trans('GROUPNAME'), 'title'=> trans('GROUPTITLE'),'active'=>trans('ACTIVATION'), 'usercount'=>trans('USERCOUNT')), 'id', 'asc'); ?>
</td>
<?php 
$model=$this->getModel();
?></tr></table>
<form method="post" name="adminform" action="<?php echo ArtaURL::make('index.php'); ?>">
<table class="admintable">
<thead>
	<tr>
		<th width="5" nowrap="nowrap"><input id="toggle" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), $('toggle'));" class="idcheck"/></th>
		<th width="5" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('GROUPNAME'),'name'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('GROUPTITLE'),'title'); ?></th>
		<th width="10%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('IS ACTIVE'),'active'); ?></th>
		<th width="8%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('USERCOUNT'),'usercount'); ?></th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th><input id="toggle2" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), $('toggle2'));" class="idcheck"/></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('GROUPNAME'),'name'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('GROUPTITLE'),'title'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('IS ACTIVE'),'active'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('USERCOUNT'),'usercount'); ?></th>
	</tr>
</tfoot>
<tbody>
<?php $i=0; 
if(@count($this->get('usergroups'))==0){
	echo '<tr><td colspan="6" align="center">'.trans('NO RESULTS').'</td></tr>';
}else{
foreach($this->get('usergroups') as $k=>$v){ ?>
	<tr<?php echo ' class="row'.$i.'"' ?>>
		<td><input type="checkbox" name="ids[]" value="<?php echo $v->id; ?>" class="idcheck"/></td>
		<td><?php echo $v->id; ?></td>
		<td><a href="<?php echo ('index.php?pack=usergroup&view=new&ids[]='.$v->id);?>"><?php echo htmlspecialchars($v->name);?></a></td>
		<td><?php echo htmlspecialchars($v->title); ?></td>
		<td align="center"><?php if($v->name !== 'guest'){ echo ArtaTagsHtml::BooleanControls($v->active, 'index.php?pack=usergroup&task=activate&ids[]='.$v->id, 'index.php?pack=usergroup&task=deactivate&ids[]='.$v->id);}else{echo '<img src="'.Imageset('true.png').'">';} ?></td>
		<td align="center"><?php if($v->name == 'guest'){echo trans('NO USERS'); }else{?><a href="<?php echo ('index.php?pack=user&view=userlist&where[usergroup]='.$v->id); ?>"><?php echo $v->usercount ?></a><?php } ?></td>
	</tr>
<?php if($i==0){$i=1;}else{$i=0;} } }?>
</tbody>
</table>
<input type="hidden" name="pack" value="usergroup"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="grouplist"/>
</form>
<?php echo ArtaTagsHtml::LimitControls(count($this->get('usergroups'))); ?>
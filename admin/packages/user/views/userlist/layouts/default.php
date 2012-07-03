<?php if(!defined('ARTA_VALID')){die('No access');}?>
<table cellpadding="5"><tr><td>
<?php echo ArtaTagsHtml::SortControls(array('id'=>trans('ID'), 'username'=>trans('USERNAME'), 'email'=> trans('EMAIL'),'activation'=> trans('IS ACTIVE'),'usergrouptitle'=> trans('USERGROUP'), 'lastvisit_date'=>trans('LASTVISIT_DATE'), 'register_date'=>trans('REGISTER_DATE')), 'id', 'asc'); ?>
</td><td>
<?php 
$model=$this->get('model');
$ug=ArtaUserGroup::getItems();
$ugs=array();
foreach($ug as $k=>$v){
	if($v->name!=='guest'){
		$ugs[$v->id]=$v->title;	
	}
}
echo ArtaTagsHtml::FilterControls('usergroup', $ugs, trans('USERGROUP')); ?></td><td>
<?php
echo ArtaTagsHtml::FilterControls(
	'lastvisit_date',
	array( 
	date('Y-m-d', time()) =>trans('TODAY'),
	date('Y-m-d', time()-86400) =>trans('YESTERDAY'),
	date('Y-m-d', time()-86400*2)=>'2 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*5)=>'5 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*7)=>'7 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*14)=>'14 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*30)=>'30 '.trans('DAYS AGO')
	),
	trans('lastvisit_date')
);
?>
</td><td>
<?php echo ArtaTagsHtml::FilterControls(
	'register_date',
	array( 
	date('Y-m-d', time()) =>trans('TODAY'),
	date('Y-m-d', time()-86400) =>trans('YESTERDAY'),
	date('Y-m-d', time()-86400*2)=>'2 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*5)=>'5 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*7)=>'7 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*14)=>'14 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*30)=>'30 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*90)=>'90 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*180)=>'180 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*365)=>'365 '.trans('DAYS AGO')
	),
	trans('register_date')
); ?></td></tr></table>
<form method="post" name="adminform" action="<?php echo ArtaURL::make('index.php'); ?>">
<table class="admintable">
<thead>
	<tr>
		<th width="5" nowrap="nowrap"><input class="idcheck" id="toggle" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), 'toggle');"/></th>
		<th width="5" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th width="16" nowrap="nowrap"><?php echo trans('IS ONLINE'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('USERNAME'),'username'); ?></th>
		<th width="25%"><?php echo ArtaTagsHtml::SortLink(trans('EMAIL'),'email'); ?></th>
		<th width="5" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('IS ACTIVE'),'activation'); ?></th>
		<th width="15%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('USERGROUP'),'usergrouptitle'); ?></th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th><input class="idcheck" id="toggle2" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), 'toggle2');"/></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th><?php echo trans('IS ONLINE'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('USERNAME'),'username'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('EMAIL'),'email'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('IS ACTIVE'),'activation'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('USERGROUP'),'usergrouptitle'); ?></th>
	</tr>
</tfoot>
<tbody>
<?php $i=0; 
if(@count($this->get('users'))==0){
	echo '<tr><td colspan="7" align="center">'.trans('NO RESULTS').'</td></tr>';
}else{
foreach($this->get('users') as $k=>$v){ ?>
	<tr<?php echo ' class="row'.$i.'"' ?>>
		<td><input type="checkbox" name="ids[]" value="<?php echo $v->id; ?>" id="ids" class="idcheck"/></td>
		<td><?php echo $v->id; ?></td>
		<td align="center"><?php  $ug=$model->getOnline($v->id); $pos=$model->getPosition($v->id); if($ug=='true') echo '<a href="index.php?pack=user&view=userlist&layout=online_info&uid='.$v->id.'">'; echo ArtaTagsHtml::Tooltip('<img src="'.Imageset($ug.'.png').'">', $pos); if($ug=='true') echo '</a>' ?></td>
		<td>
		<table class="celltable">
			<tr><td><a href="<?php echo ('index.php?pack=user&view=new&ids[]='.$v->id); ?>"><?php echo ArtaTagsHtml::Tooltip(htmlspecialchars($v->username), '<b>'.trans('USERINFO').'</b><p><br/>'.trans('REGISTER_DATE').' : '.ArtaDate::_($v->register_date).'<br/>'.trans('LASTVISIT_DATE').' : '.($v->lastvisit_date == trans('never') ? $v->lastvisit_date : ArtaDate::_($v->lastvisit_date))); ?></a></td></tr>
			<tr><td class="details"><?php echo htmlspecialchars($v->name); ?></td></tr>
		</table>
		</td>
		<td><?php echo htmlspecialchars($v->email); ?></td>
		<td align="center"><?php $v->activation=(bool)$v->activation;echo ArtaTagsHtml::BooleanControls($v->activation ? false : true, 'index.php?pack=user&task=activate&ids[]='.$v->id, 'index.php?pack=user&task=deactivate&ids[]='.$v->id); ?></td>
		<td align="center"><a href="<?php echo ('index.php?pack=usergroup&view=new&ids[]='.$v->usergroup); ?>"><?php echo htmlspecialchars($v->usergrouptitle); ?></a></td>
	</tr>
<?php if($i==0){$i=1;}else{$i=0;} } }?>
</tbody>
</table>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="userlist"/>
</form>
<?php echo ArtaTagsHtml::LimitControls($model->getUsersCount()); ?>
<?php if(!defined('ARTA_VALID')){die('No access');}?>
<table cellpadding="5"><tr><td>
<?php echo ArtaTagsHtml::SortControls(array('id'=>trans('ID'), 'username'=>trans('USERNAME'), 'email'=> trans('EMAIL'), 'register_date'=>trans('REGISTER_DATE')), 'id', 'asc'); ?>
</td>
<?php 
$model=$this->get('model');
?>
<td>
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
		<th><?php echo ArtaTagsHtml::SortLink(trans('USERNAME'),'username'); ?></th>
		<th width="30%"><?php echo ArtaTagsHtml::SortLink(trans('EMAIL'),'email'); ?></th>
		<th width="20%"><?php echo ArtaTagsHtml::SortLink(trans('REGISTER_DATE'),'register_date'); ?></th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th><input id="toggle2" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), 'toggle2');" class="idcheck"/></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('USERNAME'),'username'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('EMAIL'),'email'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('REGISTER_DATE'),'register_date'); ?></th>
	</tr>
</tfoot>

<tbody>
<?php $i=0; 
if(@count($this->get('users'))==0){
	echo '<tr><td colspan="6" align="center">'.trans('NO RESULTS').'</td></tr>';
}else{
foreach($this->get('users') as $k=>$v){ ?>
	<tr<?php echo ' class="row'.$i.'"' ?>>
		<td><input type="checkbox" name="ids[]" value="<?php echo $v->id; ?>" id="ids" class="idcheck"/></td>
		<td><?php echo $v->id; ?></td>
		<td>
		<table class="celltable">
			<tr><td><a href="<?php echo ('index.php?pack=user&moderation=true&view=new&ids[]='.$v->id); ?>"><?php echo htmlspecialchars($v->username); ?></a></td></tr>
			<tr><td class="details"><?php echo htmlspecialchars($v->name); ?></td></tr>
		</table>
		</td>
		<td><?php echo htmlspecialchars($v->email); ?></td>
		<td align="center"><?php echo ArtaDate::_($v->register_date); ?></td>
	</tr>
<?php if($i==0){$i=1;}else{$i=0;} } }?>
</tbody>
</table>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="userlist"/>
<input type="hidden" name="moderation" value="true"/>
</form>
<?php echo ArtaTagsHtml::LimitControls($model->getUsersCount()); ?>
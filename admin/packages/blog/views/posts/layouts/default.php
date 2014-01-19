<?php if(!defined('ARTA_VALID')){die('No access');}?>

<table cellpadding="5"><tr><td rowspan="2">
<?php echo ArtaTagsHtml::SortControls(array('id'=>trans('ID'), 'title'=>trans('TITLE'), 'enabled'=> trans('ENABLED'), 'cattitle'=>trans('CATEGORY'), 'username'=>trans('AUTHOR'),'added_time'=>trans('ADDED_TIME'),'hits'=>trans('HITS'),'rating'=>trans('RATING')), 'added_time', 'desc'); ?>
</td><td>
<?php 
$u=$this->get('u');

echo ArtaTagsHtml::FilterControls('added_by', $u, trans('AUTHOR')); ?></td><td>
<?php 
$u=$this->getCategories();
$us=array();
foreach($u as $k=>$v){
	$us[$v->id]=($v->title);
}
echo ArtaTagsHtml::FilterControls('blogid', $us, trans('CATEGORY')); ?></td><td><?php
	echo ArtaTagsHtml::FilterControls('com', array(trans('SHOW WITH COMMENTS'), trans('SHOW WITH UNPUBLISHED'), trans('SHOW WITHOUT COMMENTS')), trans('COMMENTS STATUS'));
?></td></tr><tr><td>
<?php
echo ArtaTagsHtml::FilterControls(
	'added_time',
	array( 
	date('Y-m-d', time()) =>trans('TODAY'),
	date('Y-m-d', time()-86400) =>trans('YESTERDAY'),
	date('Y-m-d', time()-86400*2)=>'2 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*5)=>'5 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*7)=>'7 '.trans('DAYS AGO'),
	date('Y-m-d', time()-86400*14)=>'14 '.trans('DAYS AGO')
	),
	trans('added_time')
);
?>
</td><td>
<?php
echo ArtaTagsHtml::FilterFindControls('title',trans('title'));
?>
</td><td></td></tr></table>
<form method="get" name="adminform" action="<?php echo ('index.php'); ?>">
<table class="admintable">
<thead>
	<tr>
		<th width="3%"><input id="toggle" class="idcheck" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), $('toggle'));"/></th>
		<th width="5%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('TITLE'),'title'); ?> / <?php echo ArtaTagsHtml::SortLink(trans('ADDED_TIME'),'added_time'); ?> / <?php echo ArtaTagsHtml::SortLink(trans('HITS'),'hits'); ?></th>
		<th width="20%"><?php echo ArtaTagsHtml::SortLink(trans('CATEGORY'),'cattitle'); ?></th>
		<th width="10%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('ENABLED'),'enabled'); ?></th>
		<th width="10%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('AUTHOR'),'username'); ?></th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th><input id="toggle2" class="idcheck" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), $('toggle2'));"/></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('TITLE'),'title'); ?> / <?php echo ArtaTagsHtml::SortLink(trans('ADDED_TIME'),'added_time'); ?> / <?php echo ArtaTagsHtml::SortLink(trans('HITS'),'hits'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('CATEGORY'),'cattitle'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('ENABLED'),'enabled'); ?></th>
		<th><?php echo ArtaTagsHtml::SortLink(trans('AUTHOR'),'username'); ?></th>
	</tr>
</tfoot>
<tbody>
<?php
$i=0;
	if($this->get('posts')==null){
		echo '<tr><td colspan="6" align="center">'.ArtaTagsHtml::msgBox(trans('NO POSTS FOUND')).'</td></tr>';
	}else{
	foreach($this->get('posts') as $v){
?>
	<tr class="row<?php
	echo $i;$i=$i==1 ? 0 : 1; 
?>">
		<td><input type="checkbox" name="ids[]" value="<?php echo $v->id; ?>" id="ids" class="idcheck"/></td>
		<td align="center"><?php echo $v->id ?></td>
		<td>
			<?php
	$align= trans('_LANG_DIRECTION')=='ltr'?'right':'left';
?>
			<table class="celltable">
				<tr><td><a href="index.php?pack=blog&view=new&ids[]=<?php echo $v->id;?>"><?php echo htmlspecialchars($v->title) ?></a></td><td class="details" align="<?php echo $align?>"><?php echo trans('HITS').': '.$v->hits;?></td></tr>
				<tr><td class="details"><?php	echo ArtaDate::_($v->added_time)?></td><td align="<?php echo $align?>" class="details"><?php if(@$v->attach){echo '<a href="index.php?pack=blog&view=new&ids[]='.$v->id.'#attach"><img src="'.imageset('attachment.png').'" title="'.$v->attach.' '.trans('ATTACHMENTS').'"></a>';}
				if(@$v->comments){
					echo '<a href="index.php?pack=blog&view=comments&id='.$v->id.'">';
					if(!$v->newcomments){
						echo '<img src="'.imageset('comment.png').'" title="'.$v->comments.' '.trans('COMMENTS').'">';
					}else{
						echo '<img src="'.imageset('new_comment.png').'" title="'.$v->comments.' '.trans('COMMENTS').' ('.trans('UNPUBLISHED').': '.$v->newcomments.')">';
					}
					echo '</a>';} ?></td></tr>
			</table>
		</td>
		<td align="center"><a href="index.php?pack=blog&view=newcat&ids[]=<?php echo $v->blogid;?>"><?php echo htmlspecialchars($v->cattitle) ?></a></td>
		<td align="center"><?php $z=$v->unpub_time==trans('never') ? $v->unpub_time : ArtaDate::_($v->unpub_time); echo ArtatagsHtml::Tooltip(ArtaTagsHtml::BooleanControls($v->enabled, 'index.php?pack=blog&task=activate&ids[]='.$v->id, 'index.php?pack=blog&task=deactivate&ids[]='.$v->id), trans('pub_time').': '.ArtaDate::_($v->pub_time).'<br>'.trans('unpub_time').': '.$z) ?></td>
		<td align="center"><a href="index.php?pack=user&view=new&ids[]=<?php echo $v->added_by;?>"><?php echo htmlspecialchars($v->username)?></a></td>
	</tr>
<?php
	}}
?>
</tbody>
</table>
<input type="hidden" name="pack" value="blog"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="posts"/>
</form>
<?php echo ArtaTagsHtml::LimitControls($this->get('c')); ?>
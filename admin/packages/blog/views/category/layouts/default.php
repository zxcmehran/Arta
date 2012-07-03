<?php if(!defined('ARTA_VALID')){die('No access');}?>
<table cellpadding="5">
	<tr>
		<td><?php echo ArtaTagsHtml::SortControls(array('p'=>trans('PARENTS') ,'id'=>trans('ID'), 'title'=>trans('TITLE'), 'c'=> trans('POSTCOUNT')), 'p', 'DESC'); ?>
		</td>
		<td>
			<?php
	echo trans('ACCMASK LEGEND');
?>
		</td>
	</tr>
</table>
<form method="post" name="adminform" action="<?php echo ('index.php'); ?>">
	<table class="admintable">
	<thead>
		<tr>
			<th width="3%"><input id="toggle" class="idcheck" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), $('toggle'));"/>
			</th>
			<th width="5%"><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
			<th><?php echo ArtaTagsHtml::SortLink(trans('TITLE'),'title'); ?></th>
			<th width="10%" nowrap="nowrap"><?php echo ArtaTagsHtml::SortLink(trans('postcount'),'c'); ?></th>
			<th width="15%" nowrap="nowrap"><?php echo trans('ACCMASK'); ?></th>
		</tr>
	</thead>
	<tfoot>
		<tr>
			<th><input id="toggle2" class="idcheck" type="checkbox" onclick="AdminFormTools.checkToggle($$('.idcheck'), $('toggle2'));"/>
			</th>
			<th><?php echo ArtaTagsHtml::SortLink(trans('ID'), 'id'); ?></th>
			<th><?php echo ArtaTagsHtml::SortLink(trans('TITLE'),'title'); ?></th>
			<th><?php echo ArtaTagsHtml::SortLink(trans('postcount'),'c'); ?></th>
			<th><?php echo trans('ACCMASK'); ?></th>
		</tr>
	</tfoot>
	<tbody>
	<?php
	$i=0;
		if($this->get('cats')==null){
			echo '<tr><td colspan="5" align="center">'.ArtaTagsHtml::msgBox(trans('NO CATS FOUND')).'</td></tr>';
		}else{
			foreach($this->get('cats') as $v){
	?>
		<tr class="row<?php
		echo $i;$i=$i==1 ? 0 : 1; 
	?>">
			<td align="center"><input type="checkbox" name="ids[]" value="<?php echo $v->id; ?>" id="ids" class="idcheck"/></td>
			<td align="center"><?php echo $v->id ?></td>
			<td><a href="index.php?pack=blog&view=newcat&ids[]=<?php echo $v->id;?>"><?php echo htmlspecialchars($v->title) ?></a></td>
			<td align="center"><a href="index.php?pack=blog&view=posts&where[blogid]=<?php echo $v->id;?>"><?php echo ($v->c) ?></a></td>
			<td align="center"><a href="index.php?pack=blog&view=newcat&ids[]=<?php echo $v->id;?>#accmasks"><?php 
			if((string)$v->accmask==''){
				$style= $v->_accmask=='' ? 'color:green;' : 'background-color:#B4DAB4;color:green;';
				$x= '<span style="'.$style.'">&nbsp;'.trans('AM_ALL').'&nbsp;</span>';
			}else{
				$style= $v->_accmask=='' ? 'color:red;' : 'background-color:#FFC8C8;color:red;';
				$x= '<span style="'.$style.'">&nbsp;'.trans('AM_SOME').'&nbsp;</span>';
			}
			echo $x;
			 ?></a></td>
		</tr>
	<?php
			}
		}
	?>
	</tbody>
	</table>
	<input type="hidden" name="pack" value="blog"/>
	<input type="hidden" name="task" value="display"/>
	<input type="hidden" name="view" value="category"/>
</form>
<?php echo ArtaTagsHtml::LimitControls($this->get('c')); ?>
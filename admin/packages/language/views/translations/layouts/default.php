 <?php if(!defined('ARTA_VALID')){die('No access');}?>
<form action="index.php">
	<input type="hidden" name="pack" value="language"/>
<?php
	echo sprintf(trans('VIEW X ITEMS ON Y LANG AND SHOW Z'),
	ArtaTagsHtml::select('group', $this->get('group'), getVar('group', null)),
	ArtaTagsHtml::select('lang', $this->get('lang'), getVar('lang', null)),
	ArtaTagsHtml::select('show', array(trans('ALL'),trans('THOSE HAVENT'),trans('THOSE HAVE'), trans('THOSE HAVE UNPUBLISHED'),trans('THOSE OUTDATE')), (int)getVar('show', 0)));
?>
	<input type="submit" value="<?php echo trans('GO'); ?>"/>
</form>
<br />
<?php
	echo $this->get('controls');
?>
<br />
<form name="adminform" action="<?php echo ArtaURL::make('index.php'); ?>">
<table class="admintable">
<thead>
	<tr>
		<th width="3%">#</th>
		<th><?php echo trans('TITLE'); ?></th>
		<th><?php echo trans('TRANSLATION') ?></th>
		<th width="15%"><?php echo trans('MODIFIED BY') ?></th>
		<th width="20%"><?php echo trans('MODIFIED TIME') ?></th>
		<th width="8%"><?php echo trans('ENABLED') ?></th>
		<th width="8%"><?php echo trans('STATUS') ?></th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th>#</th>
		<th><?php echo trans('TITLE'); ?></th>
		<th><?php echo trans('TRANSLATION') ?></th>
		<th><?php echo trans('MODIFIED BY') ?></th>
		<th><?php echo trans('MODIFIED TIME') ?></th>
		<th><?php echo trans('ENABLED') ?></th>
		<th><?php echo trans('STATUS') ?></th>
	</tr>
</tfoot>
<tbody>
<?php $i=0; 
if(@!is_array($this->get('data')) || count($this->get('data'))==0){
	if(getVar('show',null)==null ||(int)getVar('show',null)==0){
		$msg=ArtaTagsHtml::msgBox(trans('NO RESULTS'));
	}else{
		$msg=ArtaTagsHtml::msgBox(trans('NO RESULTS IN THIS PAGE TRY OTHER PAGES'));
	}
	echo '<tr><td colspan="7">'.$msg.'</td></tr>';
}else{
	$title=$this->row_title;
	$id=$this->row_id;
	$r=$this->get('data');
foreach($r as $k=>$v){ ?>
	<tr<?php echo ' class="row'.$i.'"' ?>>
		<td><input type="radio" name="id" value="<?php echo $v->$id; ?>" id="ids" class="idcheck"/></td>
		<td><a href="<?php echo ('index.php?pack=language&view=new&lang='.urlencode(getVar('lang')).'&group='.urlencode(getVar('group')).'&id='.$v->$id); ?>"><?php echo htmlspecialchars($v->$title);?></a></td>
		<td><?php 
	echo @htmlspecialchars($v->__value[$title]['value']);
?></td>
		<td align="center"><?php echo isset($v->__info->transmod_by)?'<a href="index.php?pack=user&view=new&ids[]='.$v->__info->transmod_by.'">'.htmlspecialchars($v->__info->transmod_by_user).'</a>':'-'; ?></td>
		<td align="center"><?php echo isset($v->__info->transmod_time)?ArtaDate::_($v->__info->transmod_time):'-'; ?></td>
		<td align="center"><?php 
		if(count($v->__value)==0){
			echo '<img src="'.Imageset('null.png').'" title="'.trans('NO TRANSLATION AVAILABLE').'" alt="'.trans('NO TRANSLATION AVAILABLE').'"/>';
		}else{
			echo ArtaTagsHtml::BooleanControls($v->__info->enabled,'index.php?pack=language&task=activate&group='.getVar('group').'&lang='.getVar('lang').'&id='.$v->$id, 'index.php?pack=language&task=deactivate&group='.getVar('group').'&lang='.getVar('lang').'&id='.$v->$id);
		}
		 ?></td>
		 <td align="center"><?php 
		 if(count($v->__value)==0){
			echo '<img src="'.Imageset('null.png').'" title="'.trans('NO TRANSLATION AVAILABLE').'"/>';
			/*echo ArtaTagsHtml::Tooltip('<img src="'.Imageset('null.png').'">',trans('NO TRANSLATION AVAILABLE'));*/
		}else{
			echo $v->__info->invalid===true ? 
			'<img src="'.Imageset('false.png').'" title="'.trans('ORIGINAL CHANGED').'"/>' :
			'<img src="'.Imageset('true.png').'" title="'.trans('ALLOKAY').'"/>';
			/*echo $v->invalid===true ? 
			ArtaTagsHtml::Tooltip('<img src="'.Imageset('false.png').'">',trans('ORIGINAL CHANGED')) :
			ArtaTagsHtml::Tooltip('<img src="'.Imageset('true.png').'">',trans('OKAY'));*/
		}
		 ?></td>
	</tr>
<?php if($i==0){$i=1;}else{$i=0;} } }?>
</tbody>
</table>
<input type="hidden" name="pack" value="language"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="translations"/>
<?php
	
	if(@is_array($this->get('data'))){
		echo '<input type="hidden" name="group" value="'.htmlspecialchars(getvar('group', '', '', 'string')).'"/>';
		echo '<input type="hidden" name="lang" value="'.htmlspecialchars(getvar('lang', '', '', 'string')).'"/>';
	}
?>
</form>
<?php echo ArtaTagsHtml::LimitControls($this->get('count')); ?>
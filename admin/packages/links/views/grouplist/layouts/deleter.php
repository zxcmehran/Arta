<?php if(!defined('ARTA_VALID')){die('No access');}
ArtaTagsHtml::addHeader('<script type="text/javascript">
function setInside(power){
	if(power){
		$(\'GroupsInside\').show();
	}else{
		$(\'GroupsInside\').hide();
	}
}
</script>');
$m=$this->getModel();
?>
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" name="adminform">
<?php
	echo trans('YOU ARE GOING TO DELETE');
?>: <?php
	echo htmlspecialchars($m->getTitle());
?>
<br />
<fieldset style="width:50%;"><legend>
<?php
	echo trans('BEFORE DELETION')
?></legend> 
<?php
	echo ArtaTagsHtml::radio('todo', array('del'=>trans('DELETE LINKS INSIDE'), 'move'=>trans('MOVE LINK INSIDE')), null, array('move'=>array('onclick'=>'setInside(1)','class'=>'idcheck'),'del'=>array('onclick'=>'setInside(0)','class'=>'idcheck')));
?>
</fieldset>
<div id="GroupsInside" style="display:none;">
<fieldset style="width:50%;"><legend>
<?php
	echo trans('MOVE TO')
?></legend> 

<?php
	echo ArtaTagsHtml::select('to',$m->getGroups(), 0);
?>
</fieldset>
</div>
<input type="hidden" name="id" value="<?php
	echo getvar('id', '', '', 'int');
?>"/>
<input type="hidden" name="pack" value="links"/>
<input type="hidden" name="task" value="deleteGroup"/>
<input type="hidden" name="view" value="display"/>
</form>


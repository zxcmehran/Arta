<?php if(!defined('ARTA_VALID')){die('No access');}?>
<form name="adminform" action="<?php echo ('index.php'); ?>" method="post">
<?php 
$model=$this->getModel();
$vals=$model->getUsergroup(getVar('ids', array(), '','array'));
echo trans('YOU ARE GOING TO DELETE');
?> :
<br/>
<code>
<?php foreach($vals as $v){
	echo ' - '.htmlspecialchars($v->title)."(".htmlspecialchars($v->name).")"."<br/><input type=\"hidden\" name=\"ids[".$v->id."]\" value=\"".$v->id."\"/>";

} ?>
</code>
<br/>

<table><tr><td><?php echo trans('BEFORE DELETION'); ?> :</td><td><?php echo ArtaTagsHtml::radio('deltype', array('delall'=>trans('DELALL'),'delmove'=>trans('DELMOVE')), 'delall', 

array('delmove'=>array('onclick'=>'document.adminform.grouplist.style.display=\'inline\';', 'class'=>'idcheck'), 'delall'=>array('onclick'=>'alert(\''.JSValue(trans('WARNING DELETE USERS'), true).'\');document.adminform.grouplist.style.display=\'none\';', 'class'=>'idcheck'))

); 
?>
<?php echo ArtaTagsHtml::PreFormItem('grouplist', '', 'usergroups'); ?></td></tr></table>
<script>
document.adminform.grouplist.style.display='none';
</script>
<input type="hidden" name="pack" value="usergroup"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="grouplist"/>
</form>
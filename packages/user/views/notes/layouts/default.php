<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 3 $
 * @date		2009/3/18 13:26 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
?>
<fieldset>
<legend><?php
	echo trans('Your personal notes');
?></legend>
<form action="index.php?pack=user" method="post">
<textarea name="txt" style="width:90%; height:200px;"><?php
	echo htmlspecialchars($this->get('t'));
?></textarea> <a href="index.php?pack=user&view=notes&type=pdf" target="_blank"><img src="<?php
	echo Imageset('pdf_small.png');
?>" alt="PDF"/></a><br/><br/>
<?php 
if($this->get('m')!=false){
	echo trans('LAST MODIFIED').' '.ArtaDate::_($this->get('m'));
}
?>
<br/>
<input type="submit" value="<?php
	echo trans('submit')
?>"/>
</fieldset>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="task" value="saveNote"/>
<input type="hidden" name="uid" value="<?php
	$u=$this->getCurrentUser();
	echo $u->id;
?>"/>
</form>
<?php if(!defined('ARTA_VALID')){die('No access');}
?>

<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" name="adminform1">
<?php
	echo trans('MAIL DESC');
?>
<br />
<?php
	echo trans('EMAIL');
?>: <input name="mail"/>
<input type="submit" value="<?php
	echo trans('submit');
?>"/>
<input type="hidden" name="pack" value="tools"/>
<input type="hidden" name="task" value="diag_mail"/>
</form>
<br /><hr /><br />
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" enctype="multipart/form-data" name="adminform2">
<?php
	echo trans('UPLOAD DESC');
?>
<br />
<input type="file" name="myfile"/>
<input type="submit" value="<?php
	echo trans('submit');
?>"/>
<input type="hidden" name="pack" value="tools"/>
<input type="hidden" name="task" value="diag_upload"/>
</form>
<br /><hr /><br />

<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" enctype="multipart/form-data" name="adminform3">
<?php
	echo trans('FTP UPLOAD DESC');
?>
<br />
<input type="file" name="myfile"/>
<input type="submit" value="<?php
	echo trans('submit');
?>"/>
<input type="hidden" name="pack" value="tools"/>
<input type="hidden" name="task" value="diag_ftp"/>
</form>


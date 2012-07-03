<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/2/14 18:6 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

ArtaTagsHtml::addScript('packages/filemanager/scripts/filemanager.js');
?>
<a name="handle">&nbsp;</a>
<?php
	ArtaTagsHtml::addtoTmpl('<script>
AjaxOnFailureErrorMsg="'.JSValue(trans('AjaxOnFailureErrorMsg')).'";
DeleteFileConfirmationMsg="'.JSValue(trans('DeleteFileConfirmationMsg')).'";
RenameFileConfirmationMsg="'.JSValue(trans('RenameFileConfirmationMsg')).'";
AjaxOnUnauthorizedMsg="'.JSValue(trans('AjaxOnUnauthorizedMsg')).'";
FM_Mode="'.getVar('editor', 0).'";
TOKEN="'.ArtaSession::genToken().'";
</script>', 'beforebodyend');
?>
<noscript><?php
	// add it's translation to main langfile
	echo trans('JAVASCRIPT MUST BE ENABLED');
?></noscript>
<table>
<tr>
	<td rowspan="2" style="vertical-align:top; background:#eeeeee; width:150px; max-width:150px; min-width:150px; overflow:auto;">
		<div id="fm_tree_container" style="direction:ltr; text-align: left; width:150px; max-width:150px; min-width:150px; overflow:auto;">
			<ul id="fm_tree" style="padding-left: 25px; margin-left:0px; direction:ltr; text-align: left;">
				<li style="list-style: url(<?php echo Imageset('home_small.png'); ?>);">
					<a href="#handle" onclick="setURL('/');"><?php echo trans('CONTENT DIR'); ?></a>
					<ul style="padding-left: 8px; margin-left:8px; direction:ltr; text-align: left; list-style: url(<?php echo Imageset('folder_small.png'); ?>);" class="subtree1" id="fm_tree_/">
						<?php foreach($this->get('filez') as $n=>$f){
							echo '<li>
							<a href="#handle" onclick="setURL(\''.JSValue($f, true).'\');">'.htmlspecialchars($n).'</a>
							<ul style="padding-left: 8px; margin-left:8px; direction:ltr; text-align: left; list-style: url('.Imageset('folder_small.png').');" class="subtree2" id="fm_tree_'.htmlspecialchars($f).'"></ul>
						</li>';
						} ?>
					</ul>
				</li>
			</ul>
		</div>
	</td>
	<td style="height:10px;width:70%;vertical-align:top;">
		<div id="fm_address"><img src="<?php echo Imageset('home_small.png'); ?>"/></div>
	</td>
</tr>
<tr>
	<td style="vertical-align:top;">
		<div style="height:350px;overflow:auto;">
			<div id="fm_content">
				
			</div>
		</div>
	</td>
</tr>
<?php 
$filesize = $this->getSetting('allowed_filesize_to_upload',1024);
$filesize = $filesize < 1024 ? $filesize.' KB' : round($filesize/1024, 1).' MB';
$types=str_replace(',',', ',$this->getSetting('allowed_filetypes_to_upload','jpg,jpeg,gif,png,swf,flv,bmp,psd,mp3,wav,wma,wm,asf,mp4,mpg,mpeg,avi,wmv,mkv,3gp,rm,zip,rar,7z,gz,bz,tar,doc,pdf,xls,wri,docx,rtf,sis,jar,cab,apk,txt'));
 ?>
<tr><td colspan="2"><fieldset><legend><?php echo trans('UPLOAD'); ?></legend><form name="fm_uploadform" action="index.php?pack=filemanager&task=upload<?php if($v=getVar('editor', 0)){echo '&editor='.$v;} ?><?php if($v=getVar('tmpl', 0)){echo '&tmpl='.$v;} ?>" enctype="multipart/form-data" method="post"><input type="file" name="uploadedFile"/> <input type="submit" value="<?php echo trans('START UPLOAD'); ?>"/>
	<?php echo ArtaTagsHtml::Tooltip('<img src="'.Imageset('info.png').'" alt="i"/>', sprintf(trans('YOU CAN UPLOAD UP TO _'),$filesize).'<br/><p><b>'.trans('ALLOWED FILETYPES').':</b><br/> '.$types.'</p>'); ?>
	<input type="hidden" value="/" name="dest"/></form></fieldset>

<fieldset><legend><?php echo trans('make NEW FOLDER'); ?></legend>
<form name="fm_newform" action="index.php?pack=filemanager&task=newfolder<?php if($v=getVar('editor', 0)){echo '&editor='.$v;} ?><?php if($v=getVar('tmpl', 0)){echo '&tmpl='.$v;} ?>" enctype="multipart/form-data" method="post">
<input type="text" name="name" class="acceptRet"/>
<input type="submit" value="<?php echo trans('makeit'); ?>"/>
<input type="hidden" value="/" name="dest"/>
</form>
</fieldset>

</td></tr>
</table>
<?php
	ArtaTagsHtml::addtoTmpl('<script>
setURL(\'/\');
</script>', 'beforebodyend');
?>
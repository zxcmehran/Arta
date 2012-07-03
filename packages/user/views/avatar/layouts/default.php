<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/18 13:26 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
$u=$this->getCurrentUser();
?>
<form method="post" action="index.php?pack=user" name="edit_form" enctype="multipart/form-data">
<fieldset>
<legend><?php
	echo trans('AVATAR SETTINGS');
?></legend>
<table width="100%">
<tr>
	<td style="vertical-align:top;" width="90%">
	
	
	<table width="100%">
		<tr>
			<td>
			<input type="radio" name="av_type" value="none" id="radio_keep" onclick="setIn('none')" checked="checked"/><label for="radio_keep"><?php
	echo trans('KEEP AVATAR');
?></label><br/>
			<input type="radio" name="av_type" value="delete" id="radio_delete" onclick="setIn('delete')"/><label for="radio_delete"><?php
	echo trans('REMOVE AVATAR');
?></label><br/>
			<input type="radio" name="av_type" value="upload" id="radio_upload" onclick="setIn('upload')"/><label for="radio_upload"><?php
	echo trans('UPLOAD AVATAR');
?></label><br/>
			<input type="radio" name="av_type" value="link" id="radio_link" onclick="setIn('link')"/><label for="radio_link"><?php
	echo trans('LINK AVATAR');
?></label><br/>
			<input type="radio" name="av_type" value="gravatar" id="radio_gravatar" onclick="setIn('gravatar')"/><label for="radio_gravatar"><?php
	echo trans('USE GRAVATAR SERVICE');
?></label>
		</tr>
		<tr>
			<td><br/>
			<div id="uploadContainer" style="min-width:250px;">
			<?php
	$t=ArtaLoader::Template();
	$t->addtoTmpl('<script>
	function setIn(type){
		if(type==\'delete\'){
			new Effect.Highlight($("uploadContainer"));
			$("uploadContainer").innerHTML="'.trans('WILL BE DELETED').'";
		}
		if(type==\'upload\'){
			new Effect.Highlight($("uploadContainer"));
			$("uploadContainer").innerHTML="'.trans('WILL BE UPLOADED').'<br/><input type=\"file\" name=\"uploadFile\"/>";
		}
		if(type==\'link\'){
			new Effect.Highlight($("uploadContainer"));
			$("uploadContainer").innerHTML="'.trans('WILL BE LINKED').'<br/><input type=\"text\" name=\"linkFile\" size=\"25\"/>";
		}
		if(type==\'gravatar\'){
			new Effect.Highlight($("uploadContainer"));
			$("uploadContainer").innerHTML="'.trans('WILL BE USED').'";
		}
		if(type==\'none\'){
			$("uploadContainer").innerHTML="";
		}
	}
                </script>','afterbody');
?>
			</div>
			</td>
		</tr>
	</table>
	
	
	
	</td>
	<td width="200" style="vertical-align:top;"><table class="avatarTable"><tr><td class="avatarTable_image"><img id="av_image" src="index.php?pack=user&view=avatar&type=jpg&big=1&uid=<?php
	echo htmlspecialchars($u->id);
?>"/></td></tr><tr><td class="avatarTable_uname"><?php
	echo htmlspecialchars($u->username);
?></td></tr>
</table></td>
</tr>
</table>
</fieldset>

<br/>
<input type="submit" value="<?php
	echo trans('SAVE CHANGES');
?>"/>
<input type="hidden" name="pack" value="user"/>
<input type="hidden" name="task" value="avatar"/>
<input type="hidden" name="uid" value="<?php
	echo $u->id;
?>"/>
</form>
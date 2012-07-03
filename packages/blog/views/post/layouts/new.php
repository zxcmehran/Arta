<?php if(!defined('ARTA_VALID')){die('No access');}
ArtaTagsHtml::addHeader('<script>function setDis(val,val2){l=document.newcommentform.lang;if(l!=undefined){
	l.disabled=val;
	l.value=val2;
}}</script>');
?>
<a name="newcomment"></a>
<form method="post" action="index.php?pack=blog" name="newcommentform">
	<table width="99%">
        <tr>
            <td class="label"><?php echo trans('TITLE'); ?></td>
            <td class="value"><input type="text" name="title" maxlength="255" /> <span style="display:none;" id="replyto_msg"><?php
	echo trans('REPLY TO').': ';
?><span id="replyto_title"></span><img src="<?php
	echo Imageset('false.png');
?>" title="<?php
	echo trans('CANCEL REPLY AND GO FOR NEW MESSAGE');
?>" onclick="$('replyto_msg').hide();$('replyto_value').value='0';setDis(false);"/></span></td>
        </tr>

		<tr>
            <td class="label"><?php echo trans('COMMENT CONTENT'); ?></td>
            <td class="value"><textarea name="content" style="width:80%;height:100px;"></textarea></td>
        </tr>

        <tr>
            <td class="label"><?php echo trans('AUTHOR'); ?></td>
            <td class="value">
				<?php
			$u=ArtaLoader::User();
			$us=$u->getCurrentUser();
			if($us->id==0){
				echo '<input type="text" name="author" maxlength="255" />';
			}else{
				echo htmlspecialchars($us->username);
			}
?>
			</td>
        </tr>
<?php
$ml=$this->get('ml');
	if($ml){
?>
        <tr>
            <td class="label"><?php echo trans('LANGUAGE'); ?></td>
            <td class="value"><?php 
			$g=$u->getGuest();
			$s=unserialize($g->settings);
			echo ArtaTagsHtml::Preformitem('lang', $s->site_language,'languages','$options[\'client\']=\'site\';$options[\'return\']=\'id\';');?></td>
        </tr>
<?php
	}
?>
        <tr>
            <td class="label"><?php echo trans('AUTHORMAIL'); ?></td>

            <td class="value"><?php
	if($us->id==0){
?><input type="text" name="authormail" maxlength="255" /><?php
		echo ' '.trans('IT IS REQUIRED AND WILL NOT BE PUBLISHED');
	}else{
		echo htmlspecialchars($us->email);
	} 
?></td>
        </tr>
        
        <tr>
            <td class="label"><?php echo trans('AUTHORWEB'); ?></td>

            <td class="value"><?php
	$_m=unserialize($us->misc);
	if($us->id==0){
?><input type="text" name="authorweb" maxlength="255" /><?php
		echo ' '.trans('OPTIONAL');
	}elseif(@trim($_m->weburl)==''){
		echo trans('NONE');
	}else{
		echo htmlspecialchars($_m->weburl);
	} 
?></td>
        </tr>
        <tr>
            <td class="label"><?php echo trans('HUMAN VERIFICATION'); ?></td>

            <td class="value"><?php
	echo ArtaTagsHtml::CAPTCHA('captcha', 'comment_'.getVar('id', '', '', 'int'));
?></td>
        </tr>
        
    </table>

    <input type="hidden" value="<?php echo htmlspecialchars(getVar('id', '', '', 'int')); ?>" name="id" />
    <input type="hidden" value="0" name="replyto" id="replyto_value"/>
    <input type="hidden" value="blog" name="pack" />
    <input type="hidden" value="comments" name="controller" />
    <input type="hidden" value="savenew" name="task" />
    <input type="submit" value="<?php
	echo trans('SUBMIT');
?>" />
    </form>

<?php if(!defined('ARTA_VALID')){die('No access');}
$com=$this->get('comment');
$ml=$this->get('ml');
?>	
<form name="adminform" method="post" action="index.php">
<fieldset>
<legend><?php
 echo trans('EDIT COMMENT').': '.htmlspecialchars($com->title);
?></legend>
	
	<table class="admintable">
		<tr>
			<td class="label"><?php echo trans('TITLE'); ?></td>

			<td class="value"><input type="text" name="title" value="<?php echo htmlspecialchars($com->title); ?>" maxlength="255" /></td>
		</tr>

		<tr>
			<td class="label"><?php echo trans('COMMENT CONTENT'); ?></td>

			<td class="value"><textarea name="content" style="width:60%;height:100px;"><?php
	echo htmlspecialchars($com->content);
?></textarea></td>
		</tr>

		<tr>
			<td class="label"><?php echo trans('AUTHOR'); ?></td>

			<td class="value">
				<?php
	$u=ArtaLoader::User();
	$us=$u->getUser($com->added_by);
			if($us==null){
				$us=$u->getGuest();
			}
			if($us->id==0){
				echo '<input type="text" name="author" value="'.htmlspecialchars($com->author).'" maxlength="255" /> ('.htmlspecialchars($us->username).')';
			}else{
				echo htmlspecialchars($us->username);
			}
?>
			</td>
		</tr>
		<tr>
			<td class="label"><?php echo trans('ADDED_TIME'); ?></td>

			<td class="value"><input name="added_time" value="<?php
	echo ArtaDate::_($com->added_time, 'jscal');
?>" id="addedTime"/> <?php echo ArtaTagsHtml::Calendar('addedTime');?></td>
		</tr>
<?php
	if($ml){
?>
		<tr>
			<td class="label"><?php echo trans('LANGUAGE'); ?></td>

			<td class="value"><?php if($com->replyto==0 && (int)$com->childs==0){echo ArtaTagsHtml::Preformitem('lang', $com->language,'languages','$options[\'client\']=\'site\';$options[\'return\']=\'id\';');}else{echo trans('YOU CANNOT CHANGE LANG OF REPLY OR REPLIED MESSAGES');}?></td>
		</tr>
<?php
	}
?>

		<tr>
			<td class="label"><?php echo trans('PUBLISHED'); ?></td>

			<td class="value"><?php echo ArtaTagsHtml::Radio('published', array(trans('NO'), trans('YES')),(int)$com->published);?></td>
		</tr>
		
		<tr>
			<td class="label"><?php echo trans('POINTS'); ?></td>

			<td class="value"><input type="text" name="points" value="<?php echo $com->points; ?>" maxlength="11" /></td>
		</tr>
		
		<tr>
			<td class="label"><?php echo trans('AUTHORMAIL'); ?></td>

			<td class="value">
			<?php
			if($us->id==0){
				echo '<input type="text" name="authormail" value="'.htmlspecialchars($com->authormail).'" maxlength="255" />';
			}else{
				echo htmlspecialchars($us->email);
			}
?></td>
		</tr>
		
		<tr>
			<td class="label"><?php echo trans('AUTHORWEB'); ?></td>

			<td class="value"><?php
			if($us->id==0){
				echo '<input type="text" name="authorweb" value="'.htmlspecialchars($com->authorweb).'" maxlength="255" />';
			}else{
				$m=unserialize($us->misc);
				if(@trim($m->weburl)!=''){
					echo htmlspecialchars($m->weburl);
				}else{
					echo trans('NONE');	
				}
			}
?></td>
		</tr>
		
	</table>
	</fieldset>
	<input type="hidden" value="<?php echo htmlspecialchars($com->id); ?>" name="cid" />
	<input type="hidden" value="<?php echo htmlspecialchars($com->postid); ?>" name="id" />
	<input type="hidden" value="blog" name="pack" />
	<input type="hidden" value="comments" name="controller" />
	<input type="hidden" value="save" name="task" />
	</form>

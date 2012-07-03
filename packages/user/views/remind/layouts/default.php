<?php if(!defined('ARTA_VALID')){die('No access');} ?>
<form action="<?php echo ('index.php'); ?>?pack=user" method="post">
<?php echo trans('REMIND INTRO'); ?><p>
<table>
<tr><td><?php echo trans('EMAIL'); ?> : </td><td><input name="email"/></td></tr>
<tr><td><?php echo trans('HUMAN VERIFICATION'); ?> : </td><td><?php echo ArtaTagsHtml::CAPTCHA('remind', 'user_remind'); ?></td></tr>
</table>
<input type="submit" value="<?php echo trans('SEND') ?>"/>
<input type="hidden" name="token" value="<?php echo ArtaSession::genToken(); ?>"/>
<input type="hidden" name="task" value="gen_remember"/>
<input type="hidden" name="pack" value="user"/>
</form>
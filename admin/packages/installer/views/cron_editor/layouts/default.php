<?php if(!defined('ARTA_VALID')){die('No access');}
$i=$this->get('var');
if(getVar('done',false)==false){
?>
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>">
<?php
	echo trans('TIMINGS FOR').': '.trans($i->type).' - '.htmlspecialchars($i->title);
?>
<br/>
<br/>
<table class="admintable"><tr>
<td>
<?php echo trans('next run') ?></td><td><input name="nextrun" value="<?php echo $i->nextrun==9999999999?'':ArtaDate::_($i->nextrun, 'jscal', true); ?>" id="nextruncal"/><?php
	 echo ArtaTagsHtml::Calendar('nextruncal'); echo ' '.ArtaTemplate::WarningTooltip(trans('THIS CRON WILL NEVER RUN')); ?></td>
</tr>
<tr>
<td>
<?php echo trans('run loop time') ?></td><td><input name="runloop" value="<?php echo round($i->runloop/3600, 2); ?>"/> <?php echo ArtaTagsHtml::Tooltip('<img src="'.Imageset('info.png').'" alt="i"/>', trans('SET ZERO TO MAKE ONCE')) ?></td>
</tr>
<tr align="center"><td><input type="submit" value="<?php
	echo trans('SUBMIT');
?>" /></td></tr>
</table>

<input type="hidden" name="pid" value="<?php
	echo htmlspecialchars($i->id);
?>"/>
<input type="hidden" name="pack" value="installer"/>
<input type="hidden" name="task" value="saveTimings"/>
</form>
<?php
	}else{
?>
<input type="button" value="<?php
	echo trans('OK');
?>" onclick="window.opener.reloadPage();" />
<?php
	}
?>
<br/><br/>
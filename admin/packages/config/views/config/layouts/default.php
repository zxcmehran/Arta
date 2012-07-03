<?php 
if(!defined('ARTA_VALID')){die('No access');}
$c=new ArtaConfig;
$db=ArtaLoader::DB();
$db->setQuery('SELECT id,title FROM #__languages WHERE client=\'site\'');
$raw=$db->loadObjectList();
$langs=array(trans('SELECT LANGUAGE'));
foreach($raw as $v){
	$langs[$v->id]=$v->title;
}
$_id=ArtaString::makeRandStr();
$r= '<form id="trans_'.$_id.'" action="index.php">';
$r.=sprintf(trans('SHOW _ TRANS IN'), trans('CONFIG')).': '.ArtaTagsHtml::select('lang', $langs, 0,'',array('onchange'=>'if(this.value!=\'0\'){$(\'trans_'.$_id.'\').submit();}'));
$r.='<input type="hidden" name="pack" value="language">'
.'<input type="hidden" name="view" value="translations">'
.'<input type="hidden" name="group" value="config">'
.'<input type="hidden" name="show" value="0">'
.ArtaTagsHtml::WarningTooltip(trans('ONLY FEW CHANGABLE'))
.'</form>';
echo $r;
?>
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" name="adminform">
<fieldset>
<legend><?php echo trans('GENERAL'); ?></legend>

<table class="admintable">
<tbody>
<tr>
	<td class="label" width="25%"><?php echo ArtaTagsHtml::Tooltip(trans('SITE_NAME'), trans('SITE_NAME_DESC')); ?></td><td class="value"><input name="data[site_name]" value="<?php echo htmlspecialchars($c->site_name) ?>"/></td>
</tr>
<tr>
	<td class="label" width="25%"><?php echo ArtaTagsHtml::Tooltip(trans('HOMEPAGE_TITLE'), trans('HOMEPAGE_TITLE_DESC')); ?></td><td class="value"><input name="data[homepage_title]" value="<?php echo htmlspecialchars($c->homepage_title) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('DESCRIPTION'), trans('DESCRIPTION_DESC')); ?></td><td class="value"><textarea name="data[description]"><?php echo htmlspecialchars($c->description) ?></textarea></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('KEYWORDS'), trans('KEYWORDS_DESC')); ?></td><td class="value"><textarea name="data[keywords]"><?php echo htmlspecialchars($c->keywords) ?></textarea></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('GZIP_OUTPUT'), trans('GZIP_OUTPUT_DESC')); ?></td><td class="value"><input type="radio" value="1" name="data[gzip_output]"<?php if($c->gzip_output==1){echo ' checked="checked"';} ?>/><?php echo trans('ON') ;?> <input type="radio" value="0" name="data[gzip_output]"<?php if($c->gzip_output==0){echo ' checked="checked"';} ?>/><?php echo trans('OFF') ;?></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('LIST_LIMIT'), trans('LIST_LIMIT_DESC')); ?></td><td class="value"><input name="data[list_limit]" value="<?php echo $c->list_limit ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('TIME_FORMAT'), trans('TIME_FORMAT_DESC')); ?></td><td class="value"><input name="data[time_format]" value="<?php echo htmlspecialchars($c->time_format) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('TIME_OFFSET'), trans('TIME_OFFSET_DESC')); ?></td><td class="value"><?php echo ArtaTagsHtml::getTimezones('data[time_offset]', htmlspecialchars($c->time_offset)); ?></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('CAL_TYPE'), trans('CAL_TYPE_DESC')); ?></td><td class="value">
		<select name="data[cal_type]">
		<option value="gregorian"<?php if($c->cal_type=='gregorian'){echo ' selected="selected"';} ?>><?php echo trans('CAL_TYPE_GREGORIAN'); ?></option>		
		<option value="jalali"<?php if($c->cal_type=='jalali'){echo ' selected="selected"';} ?>><?php echo trans('CAL_TYPE_JALALI'); ?></option>		
		</select> <?php echo ArtaTagsHtml::WarningTooltip(trans('CAL_TYPE_WARNING')); ?> </td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('DEBUG'), trans('DEBUG_DESC')); ?></td><td class="value"><input type="radio" value="1" name="data[debug]"<?php if($c->debug==1){echo ' checked="checked"';} ?>/><?php echo trans('ON') ;?> <input type="radio" value="0" name="data[debug]"<?php if($c->debug==0){echo ' checked="checked"';} ?>/><?php echo trans('OFF') ;?></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('DEBUG_MODE'), trans('DEBUG_MODE_DESC')); ?></td><td class="value"><input type="radio" value="echo" name="data[debug_mode]"<?php if($c->debug_mode=='echo'){echo ' checked="checked"';} ?>/><?php echo trans('ECHOMODE') ;?> <input type="radio" value="file" name="data[debug_mode]"<?php if($c->debug_mode=='file'){echo ' checked="checked"';} ?>/><?php echo trans('FILEMODE') ;?></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('CACHE'), trans('CACHE_DESC')); ?></td><td class="value"><input type="radio" value="1" name="data[cache]"<?php if($c->cache==1){echo ' checked="checked"';} ?>/><?php echo trans('ON') ;?> <input type="radio" value="0" name="data[cache]"<?php if($c->cache==0){echo ' checked="checked"';} ?>/><?php echo trans('OFF') ;?></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('CACHE_LIFETIME'), trans('CACHE_LIFETIME_DESC')); ?></td><td class="value"><input name="data[cache_lifetime]" value="<?php echo $c->cache_lifetime ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('ADMINALERT'), trans('ADMINALERT_DESC')); ?></td><td class="value"><input type="radio" value="1" name="data[admin_alert]"<?php if($c->admin_alert==1){echo ' checked="checked"';} ?>/><?php echo trans('ON') ;?> <input type="radio" value="0" name="data[admin_alert]"<?php if($c->admin_alert==0){echo ' checked="checked"';} ?>/><?php echo trans('OFF') ;?></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('ADMINALERT_LIFETIME'), trans('ADMINALERT_LIFETIME_DESC')); ?></td><td class="value">
		<select name="data[admin_alert_lifetime]">
		<option value="86400"<?php if($c->admin_alert_lifetime=='86400'){echo ' selected="selected"';} ?>>1 <?php echo trans('DAY'); ?></option>		
		<option value="172800"<?php if($c->admin_alert_lifetime=='172800'){echo ' selected="selected"';} ?>>2 <?php echo trans('DAYS'); ?></option>		
		<option value="345600"<?php if($c->admin_alert_lifetime=='345600'){echo ' selected="selected"';} ?>>4 <?php echo trans('DAYS'); ?></option>		
		<option value="604800"<?php if($c->admin_alert_lifetime=='604800'){echo ' selected="selected"';} ?>>7 <?php echo trans('DAYS'); ?></option>		
		<option value="1209600"<?php if($c->admin_alert_lifetime=='1209600'){echo ' selected="selected"';} ?>>14 <?php echo trans('DAYS'); ?></option>		
		<option value="2592000"<?php if($c->admin_alert_lifetime=='2592000'){echo ' selected="selected"';} ?>>30 <?php echo trans('DAYS'); ?></option>		
		</select>
	</td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('CRON_LOG_LIFETIME'), trans('CRON_LOG_LIFETIME_DESC')); ?></td><td class="value">
		<select name="data[cron_log_lifetime]">
		<option value="86400"<?php if($c->cron_log_lifetime=='86400'){echo ' selected="selected"';} ?>>1 <?php echo trans('DAY'); ?></option>		
		<option value="172800"<?php if($c->cron_log_lifetime=='172800'){echo ' selected="selected"';} ?>>2 <?php echo trans('DAYS'); ?></option>		
		<option value="345600"<?php if($c->cron_log_lifetime=='345600'){echo ' selected="selected"';} ?>>4 <?php echo trans('DAYS'); ?></option>		
		<option value="604800"<?php if($c->cron_log_lifetime=='604800'){echo ' selected="selected"';} ?>>7 <?php echo trans('DAYS'); ?></option>		
		<option value="1209600"<?php if($c->cron_log_lifetime=='1209600'){echo ' selected="selected"';} ?>>14 <?php echo trans('DAYS'); ?></option>		
		<option value="2592000"<?php if($c->cron_log_lifetime=='2592000'){echo ' selected="selected"';} ?>>30 <?php echo trans('DAYS'); ?></option>		
		</select>
	</td>
</tr>
</tbody>
</table>
</fieldset>


<fieldset>
<legend><?php echo trans('SITE OFFLINE'); ?></legend>
<table class="admintable">
<tbody>
<tr>
	<td class="label" width="25%"><?php echo ArtaTagsHtml::Tooltip(trans('OFFLINE'), trans('OFFLINE_DESC')); ?></td><td class="value"><input type="radio" value="1" name="data[offline]"<?php if($c->offline==1){echo ' checked="checked"';} ?>/><?php echo trans('YES') ;?> <input type="radio" value="0" name="data[offline]"<?php if($c->offline==0){echo ' checked="checked"';} ?>/><?php echo trans('NO') ;?></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('OFFLINE_MSG'), trans('OFFLINE_MSG_DESC')); ?></td><td class="value"><textarea name="data[offline_msg]"><?php echo htmlspecialchars($c->offline_msg) ?></textarea></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('OFFLINE_PASS'), trans('OFFLINE_PASS_DESC')); ?></td><td class="value"><input name="data[offline_pass]" value="" type="password"/> <?php echo ArtaTagsHtml::WarningTooltip(trans('PASS_CHANGE_WARNING')); ?> </td>
</tr>
</tbody>
</table>
</fieldset>

<fieldset>
<legend><?php echo trans('COOKIES'); ?></legend>
<table class="admintable">
<tbody>
<tr>
	<td class="label" width="25%"><?php echo ArtaTagsHtml::Tooltip(trans('COOKIE_PATH'), trans('COOKIE_PATH_DESC')); ?></td><td class="value"><input name="data[cookie_path]" value="<?php echo htmlspecialchars($c->cookie_path) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('COOKIE_DOMAIN'), trans('COOKIE_DOMAIN_DESC')); ?></td><td class="value"><input name="data[cookie_domain]" value="<?php echo htmlspecialchars($c->cookie_domain) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('SESSION_LIFETIME'), trans('SESSION_LIFETIME_DESC')); ?></td><td class="value"><input name="data[session_lifetime]" value="<?php echo $c->session_lifetime ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('SESSION_TYPE'), trans('SESSION_TYPE_DESC')); ?></td><td class="value">
		<select name="data[session_type]">
		<option value="db"<?php if($c->session_type=='db'){echo ' selected="selected"';} ?>><?php echo trans('SESSION_TYPE_DB'); ?></option>		
		<option value="file"<?php if($c->session_type=='file'){echo ' selected="selected"';} ?>><?php echo trans('SESSION_TYPE_FILE'); ?></option>		
		</select> </td>
</tr>
</tbody>
</table>
</fieldset>

<fieldset>
<legend><?php echo trans('DATABASE'); ?></legend>
<table class="admintable">
<tbody>
<tr>
	<td class="label" width="25%"><?php echo ArtaTagsHtml::Tooltip(trans('DB_HOST'), trans('DB_HOST_DESC')); ?></td><td class="value"><input name="data[db_host]" value="<?php echo htmlspecialchars($c->db_host) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('DB_USER') ?></td><td class="value"><input name="data[db_user]" value="<?php echo htmlspecialchars($c->db_user) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('DB_PASS') ?></td><td class="value"><input type="password" name="data[db_pass]" value=""/> <?php echo ArtaTagsHtml::WarningTooltip(trans('PASS_CHANGE_WARNING')); ?> </td>
</tr>
<tr>
	<td class="label"><?php echo trans('DB_NAME') ?></td><td class="value"><input name="data[db_name]" value="<?php echo htmlspecialchars($c->db_name) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('DB_PREFIX'), trans('DB_PREFIX_DESC')); ?></td><td class="value"><input name="data[db_prefix]" value="<?php echo htmlspecialchars($c->db_prefix) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('DB_TYPE') ?></td><td class="value">
		<select name="data[db_type]">
		<option value="mysql"<?php if($c->db_type=='mysql'){echo ' selected="selected"';} ?>>MySQL</option>		
		<option value="mysqli"<?php if($c->db_type=='mysqli'){echo ' selected="selected"';} ?>>MySQLi</option>	
		</select>
		<?php 
			if(function_exists('mysql_connect')){$mysql='<span style="color:green;">'.trans('IS AVAILABLE').'</span>';}else{$mysql='<span style="color:red;">'.trans('IS UNAVAILABLE').'</span>';}
			if(function_exists('mysqli_connect')){$mysqli='<span style="color:green;">'.trans('IS AVAILABLE').'</span>';}else{$mysqli='<span style="color:red;">'.trans('IS UNAVAILABLE').'</span>';}
			echo ArtaTagsHtml::Tooltip('<img src="'.Imageset('warning.png').'"/>', sprintf('<b>MySQL %s<br/>MySQLi %s</b>', $mysql, $mysqli));
		?>
	</td>
</tr>
</tbody>
</table>
</fieldset>

<fieldset>
<legend><?php echo trans('PATHS AND URLS'); ?></legend>
<table class="admintable">
<tbody>
<tr>
	<td class="label" width="25%"><?php echo trans('FORCE WEBSITE TO SSL'); ?></td><td class="value"><input type="radio" value="0" name="data[secure_site]"<?php if($c->secure_site=='0'){echo ' checked="checked"';} ?>/><?php echo trans('NO');?> <input type="radio" value="1" name="data[secure_site]"<?php if($c->secure_site=='1'){echo ' checked="checked"';} ?>/><?php echo trans('YES');?></td>
</tr>
<tr>
	<td class="label"><?php echo trans('FORCE ADMIN TO SSL'); ?></td><td class="value"><input type="radio" value="0" name="data[secure_admin]"<?php if($c->secure_admin=='0'){echo ' checked="checked"';} ?>/><?php echo trans('NO');?> <input type="radio" value="1" name="data[secure_admin]"<?php if($c->secure_admin=='1'){echo ' checked="checked"';} ?>/><?php echo trans('YES');?></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('SEF'), trans('SEF_DESC')); ?></td><td class="value"><input type="radio" value="0" name="data[sef]"<?php if($c->sef=='0'){echo ' checked="checked"';} ?>/><?php echo ArtaTagsHtml::Tooltip(trans('OFF'), trans('NOSEF_DESC')) ;?> <input type="radio" value="1" name="data[sef]"<?php if($c->sef=='1'){echo ' checked="checked"';} ?>/><?php echo ArtaTagsHtml::Tooltip(trans('SEF1'), trans('SEF1_DESC')) ;?> <input type="radio" value="2" name="data[sef]"<?php if($c->sef=='2'){echo ' checked="checked"';} ?>/><?php echo ArtaTagsHtml::Tooltip(trans('SEF2'), trans('SEF2_DESC')) ;?></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('SEF_REWRITE'), trans('SEF_REWRITE_DESC')); ?></td><td class="value"><input type="radio" value="1" name="data[sef_rewrite]"<?php if($c->sef_rewrite==1){echo ' checked="checked"';} ?>/><?php echo trans('YES') ;?> <input type="radio" value="0" name="data[sef_rewrite]"<?php if($c->sef_rewrite==0){echo ' checked="checked"';} ?>/><?php echo trans('NO') ;?> <?php echo ArtaTagsHtml::WarningTooltip(trans('SEF_REWRITE_WARNING')); if(function_exists('apache_get_modules')){@$mods=apache_get_modules();}else{$mods=array();} echo ' ' ;if(@in_array('mod_rewrite',$mods) || isset($_SERVER['IIS_UrlRewriteModule'])){echo ArtaTagsHtml::Tooltip('<img src="'.Imageset('true.png').'">', trans('SEF_REWRITE_ENABLED'));}else{echo ArtaTagsHtml::Tooltip('<img src="'.Imageset('false.png').'">', trans('SEF_REWRITE_DISABLED'));}?></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('MAIN_DOMAIN'), trans('MAIN_DOMAIN_DESC')); ?></td><td class="value"><input name="data[main_domain]" value="<?php echo htmlspecialchars($c->main_domain) ?>" size="30"/></td>
</tr>
</tbody>
</table>
</fieldset>

<fieldset>
<legend><?php echo trans('MAILING'); ?></legend>
<table class="admintable">
<tbody>
<tr>
	<td class="label" width="25%"><?php echo trans('MAIL_ADMIN'); ?></td><td class="value"><input name="data[mail_admin]" value="<?php echo htmlspecialchars($c->mail_admin) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('MAIL_TYPE'), trans('MAIL_TYPE_DESC')); ?></td><td class="value"><input type="radio" value="smtp" name="data[mail_type]"<?php if($c->mail_type=='smtp'){echo ' checked="checked"';} ?>/><?php echo trans('SMTP') ;?> <input type="radio" value="php" name="data[mail_type]"<?php if($c->mail_type=='php'){echo ' checked="checked"';} ?>><?php echo trans('PHP MAIL FUNCTION') ;?></td>
</tr>
<tr>
	<td class="label"><?php echo trans('MAIL_SMTP_HOST'); ?></td><td class="value"><input name="data[mail_smtp_host]" value="<?php echo htmlspecialchars($c->mail_smtp_host) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('MAIL_USERNAME'); ?></td><td class="value"><input name="data[mail_username]" value="<?php echo htmlspecialchars($c->mail_username) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('MAIL_PASSWORD'); ?></td><td class="value"><input type="password" name="data[mail_password]" value=""/> <?php echo ArtaTagsHtml::WarningTooltip(trans('PASS_CHANGE_WARNING')); ?> </td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('MAIL_POP3_AUTH_HOST'), trans('MAIL_POP3_AUTH_HOST_DESC')); ?></td><td class="value"><input name="data[mail_pop3_auth_host]" value="<?php echo htmlspecialchars($c->mail_pop3_auth_host) ?>"/></td>
</tr>
</tbody>
</table>
</fieldset>

<fieldset>
<legend><?php echo trans('FTP ACCESS'); ?></legend>
<table class="admintable">
<tbody>
<tr>
	<td class="label" width="25%"><?php echo ArtaTagsHtml::Tooltip(trans('FTP_ENABLED'), trans('FTP_ENABLED_DESC')); ?></td><td class="value"><input type="radio" value="1" name="data[ftp_enabled]"<?php if($c->ftp_enabled=='1'){echo ' checked="checked"';} ?>/><?php echo trans('YES') ;?> <input type="radio" value="0" name="data[ftp_enabled]"<?php if($c->ftp_enabled=='0'){echo ' checked="checked"';} ?>/><?php echo trans('NO') ;?></td>
</tr>
<tr>
	<td class="label"><?php echo trans('FTP_HOST'); ?></td><td class="value"><input name="data[ftp_host]" value="<?php echo htmlspecialchars($c->ftp_host) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('FTP_PORT'); ?></td><td class="value"><input name="data[ftp_port]" value="<?php echo htmlspecialchars($c->ftp_port) ?>" size="7"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('FTP_USERNAME'); ?></td><td class="value"><input name="data[ftp_user]" value="<?php echo htmlspecialchars($c->ftp_user) ?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('FTP_PASSWORD'); ?></td><td class="value"><input name="data[ftp_pass]" type="password" value="<?php /*echo htmlspecialchars($c->ftp_pass) */?>"/> <?php echo ArtaTagsHtml::WarningTooltip(trans('PASS_CHANGE_WARNING')); ?> </td>
</tr>
<tr>
	<td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('FTP_PATH'), trans('FTP_PATH_DESC')); ?></td><td class="value"><input name="data[ftp_path]" value="<?php echo htmlspecialchars($c->ftp_path) ?>"/></td>
</tr>
</tbody>
</table>
</fieldset>
<input type="hidden" name="pack" value="config"/>
<input type="hidden" name="task" value="config_save"/>
</form>
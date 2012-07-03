<?php 
if(!defined('ARTA_VALID')){die('No access');}

function deny_these_ips(){
	$plg=ArtaLoader::Plugin();
	$setting = $plg->getSetting('blocked_ips','', 'ipblocker');
	if((string)$setting!==''){
		$setting=explode("\n", $setting);
	}
	if(CLIENT=='admin' && (string)$plg->getSetting('block_admin',0, 'ipblocker')=='0'){
		return true;
	}
	if(is_array($setting)){
		$setting=array_map('trim', $setting);
		if(in_array($_SERVER['REMOTE_ADDR'], $setting)){
			$url=$plg->getSetting('redirection_url','', 'ipblocker');
			if((string)$url!==''){
				redirect($url);
			}else{
				ArtaError::show(403, 'Your IP is banned from this website.');
			}
		}
	}
	return true;
}

?>
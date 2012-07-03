<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/27 14:16 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class InfoModelInfo extends ArtaPackageModel{
	
	function getInfo($type){
		switch($type){
			case 'sys':
			default:
				$db=ArtaLoader::DB();
				if(isset($_SERVER['SERVER_SOFTWARE'])){
					$s= $_SERVER['SERVER_SOFTWARE'];
				}elseif(($sf = getenv('SERVER_SOFTWARE'))){
					$s= $sf;
				}else{
					$s= trans('NA');
				}
				$output=array(
				trans('ARTA VERSION')=>ArtaVersion::getCredits(true, true),
				trans('PHP Running on')=>php_uname(),
				trans('Database Version')=>$db->getVersion(),
				trans('Web Server Software')=>$s,
				trans('Web Server to PHP Interface')=>php_sapi_name(),
				trans('User Agent')=>@$_SERVER['HTTP_USER_AGENT'],
				);
			break;
			case 'php':
				ob_start();
				phpinfo(INFO_GENERAL);
				$phpinfo = ob_get_contents();
				ob_end_clean();

				preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpinfo, $output);
				$output = preg_replace('#<table#', '<table class="admintable" align="center"', $output[1][0]);
				$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
				$output = preg_replace('#border="0" cellpadding="3" width="600"#', 'border="0" cellspacing="1" cellpadding="4" width="95%"', $output);
				$output = preg_replace('#<hr />#', '', $output);
				$output = str_replace('<div class="center">', '', $output);
				$output1 = str_replace('</div>', '', $output);
				$output=null;
				ob_start();
				phpinfo(INFO_CONFIGURATION);
				$phpinfo = ob_get_contents();
				ob_end_clean();

				preg_match_all('#<body[^>]*>(.*)</body>#siU', $phpinfo, $output);
				$output = preg_replace('#<table#', '<table class="admintable" align="center"', $output[1][0]);
				$output = preg_replace('#(\w),(\w)#', '\1, \2', $output);
				$output = preg_replace('#border="0" cellpadding="3" width="600"#', 'border="0" cellspacing="1" cellpadding="4" width="95%"', $output);
				$output = preg_replace('#<hr />#', '', $output);
				$output = str_replace('<div class="center">', '', $output);
				$output2 = str_replace('</div>', '', $output);
				$output=$output1.$output2;
				
			break;
			case 'dir':
				$writeable= '<b><font color="green">'. trans( 'WRITEABLE' ) .'</font></b>';
				$unwriteable= '<b><font color="red">'. trans( 'UNWRITEABLE' ) .'</font></b>';
				$l=array(
				'admin',
				'admin/backup',
				'admin/help',
				'admin/imagesets',
				'admin/includes',
				'admin/languages',
				'admin/modules',
				'admin/packages',
				'admin/plugins',
				'admin/templates',
				'admin/tmp',
				'content',
				'crons',
				'imagesets',
				'includes',
				'languages',
				'library',
				'library/external',
				'media',
				'modules',
				'packages',
				'plugins',
				'templates',
				'tmp',
				'webservices',
				'widgets');
				$output=array();
				foreach($l as $v){
					if(is_writeable(ARTAPATH_BASEDIR.'/'.$v)){
						$output[$v]=$writeable;
					}else{
						$output[$v]=$unwriteable;
					}
				}
			break;
			case 'cronlogs':
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__cronslog ORDER BY `time` DESC');
				$r=$db->loadObjectList();
				$cache=array();
				if($r==null){
					$output='<b align="center">'.ArtaTagsHtml::msgBox(trans('NOTHING LOGGED HERE')).'</b>';
				}else{
					$output=array();
					foreach($r as $k=>$v){
						if(!isset($cache[$v->cron])){
							$db->setQuery('SELECT title FROM #__crons WHERE id='.$db->Quote($v->cron));
							$cache[$v->cron]=$db->loadResult();
						}
						$output[$cache[$v->cron].'</td><td class="value" style="width:120px;">'.ArtaDate::_($v->time).'<!-- '.ArtaString::makeRandStr().' -->']=htmlspecialchars($v->text);
					}
				}
			break;
			case 'adminalerts':
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__admin_alerts ORDER BY `last_time` DESC');
				$r=$db->loadObjectList();
				if($r==null){
					$output='<b align="center">'.trans('NOTHING LOGGED HERE').'</b>';
				}else{
					$output=array();
					foreach($r as $k=>$v){
						$output[$v->at.'</td><td class="value" style="width:150px;">'.$v->when.'<!-- '.ArtaString::makeRandStr().' -->']=htmlspecialchars($v->tip).'</td><td class="value" style="width:120px;">'.ArtaDate::_($v->last_time).'</td><td class="value" style="width:10px; text-align:center;">'.$v->times;
					}
				}
			break;
		}
		return $output;
	} 
	
}

?>
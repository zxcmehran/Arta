<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ModuleTopcontrolsHelper extends ArtaModuleHelper{
	function getInfo(){
		
		$u=ArtaLoader::User();
		$u=$u->getCurrentUser();
		
		$config=ArtaLoader::Config();
		
		$time=ArtaDate::convertOutput(time(), 'Y-m-d H:i').' '.($config->time_offset > 0 && $config->time_offset{0}!=='+' ? '+'.$config->time_offset:$config->time_offset);
		
		$s_time=ArtaDate::convertOutput($config->install_time, 'Y-m-d H:i');
		
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT COUNT(*) FROM #__sessions WHERE userid IS NOT NULL AND userid !=0 AND userid!=\'\' AND client=\'site\'');
		$site=$db->loadResult();
		
		$db->setQuery('SELECT COUNT(*) FROM #__sessions WHERE userid IS NOT NULL AND userid !=0 AND userid!=\'\' AND client=\'admin\'');
		$admin=$db->loadResult();
		
		$db->setQuery('SELECT COUNT(*) FROM #__sessions WHERE userid IS NULL OR userid =0 OR userid=\'\'');
		$guest=$db->loadResult();
		
		$db->setQuery('SELECT COUNT(*) FROM #__users');
		$uc=$db->loadResult();
		
		$r='<table>';
		$r.='<tr><td>'.trans('LOGGED IN AS').':</td><td>'.htmlspecialchars($u->username).'</td></tr>';
		$r.='<tr><td>'.trans('SERVER TIME').':</td><td>'.$time.'</td></tr>';
		$r.='<tr><td>'.trans('ONLINE GUESTS').':</td><td>'.$guest.'</td></tr>';
		$r.='<tr><td>'.trans('ONLINE USERS').' ('.trans('SITE').'):</td><td>'.$site.'</td></tr>';
		$r.='<tr><td>'.trans('ONLINE USERS').' ('.trans('ADMIN').'):</td><td>'.$admin.'</td></tr>';
		$r.='<tr><td>'.trans('TOTAL USERS').':</td><td>'.$uc.'</td></tr>';
		$r.='<tr><td>'.trans('INSTALL DATE').':</td><td>'.$s_time.'</td></tr>';
		$r.='</table>';
		
		return $r;
	}
}
?>
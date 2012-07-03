<?php 
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 18:44 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
if(!function_exists('getDataOfLink')){
	function getDataOfLink($g){
		$db = ArtaLoader::DB();
		$c = ArtaLoader::Config();
		if($c->cache==false){
			$db->setQuery('SELECT * FROM #__links WHERE `enabled`=1 AND `group`='.$db->Quote($g).' ORDER BY `order`');
			$r = $db->loadObjectList();
			if(is_null($r)){
				$r=array();
			}
		}else{
			if(ArtaCache::isUsable('links','items_bygroup')){
				$r=ArtaCache::getData('links','items_bygroup');
			}else{
				$db->setQuery('SELECT * FROM #__links WHERE `enabled`=1 ORDER BY `order`');
				$r = ArtaUtility::keyByChild((array)$db->loadObjectList(), 'group',true);
				ArtaCache::putData('links','items_bygroup', $r);
			}
			$r= (array)@$r[$g];
		}
		foreach($r as $k=>$v){
			if(ArtaUsergroup::processDenied($v->denied)==false){
				unset($r[$k]);
			}
		}
		return $r;
	}
	function addtoUListForLink($d){
		$r='<ul class="linkviewer">';
		$plug=ArtaLoader::Plugin();
		foreach($d as $k=>$v){
			$plug->trigger('onPrepareContent', array(&$v, 'link'));
			if($v->newwin==1){
				$s=' target="_blank"';
			}else{
				$s='';
			}
			if($v->type=='default'){
				$v->link='index.php';
			}
			$r.='<li><a href="'.$v->link.'"'.$s.'>'.htmlspecialchars($v->title).'</a></li>';
		}
		$r.='</ul>';

		return $r;		
	}
}
$ResOfLinks=getDataOfLink($g);
if(count($ResOfLinks)==0){
	$content[$k] .= trans('NO LINKS FOUND TO VIEW');
}else{
	$content[$k] .= '<nav>'.addtoUListForLink($ResOfLinks).'</nav>';
}
unset($ResOfLinks);

?>
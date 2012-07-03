<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 18:53 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class ModuleBlogmostHelper extends ArtaModuleHelper{
	function addtoUList($d){
		if($d==null){
			return trans('NO POSTS FOUND TO VIEW');
		}
		$r='<ul>';
		$plug=ArtaLoader::Plugin();
		foreach($d as $k=>$v){
			$plug->trigger('onPrepareContent', array(&$v, 'blogpost'));
			if($this->getSetting('show_hits_count', true)){
				$suf=' <span style="color: rgb(170,170,170)">('.$v->hits.')</span>';
			}else{
				$suf='';
			}
			$r.='<li><a href="index.php?pack=blog&view=post&id='.$v->id.'">'.htmlspecialchars($v->title).'</a>'.$suf.'</li>';
		}
		$r.='</ul>';
		return $r;		
	}
}

?>
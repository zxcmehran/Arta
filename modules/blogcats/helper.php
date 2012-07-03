<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 18:53 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class ModuleBlogcatsHelper extends ArtaModuleHelper{
	function makeCatList(){
		$r=$this->getChilds(0);
		return $r;
	}
	
	function getChilds($p){
		$m=$this->getModel();
		$x=$m->getPCat($p);
		$c=$this->getSetting('link_type', 'index');
		if(is_array($x) && count($x)>0){
			$r='<ul>';
			foreach($x as $v){
				$r.='<li><a href="index.php?pack=blog&view='.$c.'&blogid='.$v->id.'">'.htmlspecialchars($v->title).'</a>';
				$r.=$this->getChilds($v->id);
				$r.='</li>';
			}
			$r.='</ul>';
			return $r;
		}else{
			return null;
		}
	}
}

?>
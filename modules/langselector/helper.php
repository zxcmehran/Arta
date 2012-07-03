<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 18:53 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class ModuleLangselectorHelper extends ArtaModuleHelper{
	function makeList(){
		$image=$this->getSetting('view_icons', '1');
		$first=true;
		$m=$this->getModel();
		$data=$m->getLanguages();
		$q=ArtaURL::getQuery();
		$q=ArtaURL::breakupQuery($q);
		$r='<div style="display:block;padding: 4px; direction: ltr;">';
		$code='';
		foreach($data as $k=>$v){
			if(isset($v->default)){
				if(IS_HOMEPAGE==true){
					$q=array();
				}else{
					if(isset($q['language'])){
						unset($q['language']);
					}
				}
			}else{
				if(IS_HOMEPAGE==true){
					$q=array('language'=>$v->name);
				}else{
					$q['language']=$v->name;
				}
			}
			if(!$image)
			if(!$first){
				$r .= ' | ';
			}else{
				$first = false;
			}
			$r.='<a href="#index.php?'.ArtaURL::makeupQuery($q).'" hreflang="'.htmlspecialchars($v->name).'">';
			if($image)
			$r.='<img src="languages/'.htmlspecialchars($v->name).'/icon.png" title="'.htmlspecialchars($v->title).'" alt="'.htmlspecialchars($v->title).'" style="cursor:pointer; display:inline;" /></a> ';
			else
			$r.=htmlspecialchars($v->title).'</a> ';
		}
		$r.='</div>';

		return $r;
	}

}

?>
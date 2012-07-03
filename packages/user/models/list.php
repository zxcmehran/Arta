<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/21 12:29 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class UserModelList extends ArtaPackageModel{
	function getUsers(){
		$db=ArtaLoader::DB();
		$l=getVar('l', '#');
		if($l!=='*' && $l!=='#'){
			$s=' WHERE LOWER(username) LIKE '.$db->Quote($db->getEscaped($l,true).'%', false).' ';
		}elseif($l=='*' && $l!=='#'){
			$alpha='A B C D E F G H I J K L M N O P Q R S T U V W X Y Z';
			$alpha=explode(' ', $alpha);
			foreach($alpha as $k=>$v){
				$alpha[$k]=' LOWER(username) NOT LIKE '.$db->Quote($db->getEscaped($v,true).'%', false);
			}
			$s=' WHERE'.implode(' AND ',$alpha);
		}else{
			$s='';
		}
		$db->SetQuery('SELECT SQL_CALC_FOUND_ROWS username,id,usergroup,lastvisit_date,avatar,register_date FROM #__users'.$s.ArtaTagsHtml::SortResult('username').ArtaTagsHtml::LimitResult());
		$r=$db->loadObjectList();
		$db->SetQuery('SELECT FOUND_ROWS() AS C');
		$this->c=$db->loadObject();
		$this->c=$this->c->C;
		return $r;
	}
	
	function getUG($ug){
		if(!isset($this->ugs)){
			$this->ugs=ArtaUtility::keyByChild(ArtaUsergroup::getItems(), 'id');
		}
		if(isset($this->ugs[$ug])){
			return $this->ugs[$ug]->title;
		}else{
			return '???';
		}
	}
	
	function getOnline($id){
		if(!isset($this->onlines)){
			$db=ArtaLoader::DB();
			$db->SetQuery('SELECT userid FROM #__sessions WHERE client=\'site\' AND userid IS NOT NULL AND userid!=0 AND userid!=\'\'');
			$this->onlines=ArtaUtility::keyByChild($db->loadObjectList(), 'userid');
		}
		if(isset($this->onlines[$id])){
			return true;
		}else{
			return false;
		}		
	}
}

?>
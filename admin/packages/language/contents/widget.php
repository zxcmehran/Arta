<?php
if(!defined('ARTA_VALID')){die('No access');}
/**
 * Widget Language Translation Handler.
 */
class LanguageTranslationWidget implements LanguageTranslation{
	
	function getIDRowName(){
		return 'id';
	}
	
	function getTitleRowName(){
		return 'title';
	}
	
	function getTitle(){
		return trans('WIDGETS');
	}

	function getRows(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__pages_widgets'.ArtaTagsHtml::FilterResult('`content`!=\'\' AND `content` IS NOT NULL').' ORDER BY `pageid` DESC '.ArtaTagsHtml::LimitResult());
		$return= $db->loadObjectList();
		$pages=array();
		foreach($return as $k=>$v){
			$pages[]=$v->pageid;
		}

		$db->setQuery('SELECT id,title FROM #__pages WHERE id IN ('.implode(',', $pages).') AND is_dynamic=1');
		$pages=(array)$db->loadObjectList('id');
		foreach($return as $k=>&$v){
			$v->title=$pages[$v->pageid]->title.' - '.$v->title;
		}
		return $return;
	}

	function getRowsCount(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT FOUND_ROWS()');
		return $db->loadResult();
	}

	function getControls(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT DISTINCT pageid FROM #__pages_widgets');
		$pages=$db->loadResultArray();
		$db->setQuery('SELECT id,title FROM #__pages WHERE id IN ('.implode(',', $pages).') AND is_dynamic=1');
		$pagez=$db->loadObjectList();
		if($pagez!==null){
			$pagez=ArtaUtility::keybyChild($pagez, 'id');
		}
		$x=array();
		foreach($pages as $a){
			if(isset($pagez[$a])){
				$x[$a]=$pagez[$a]->title;
			}else{
				$x[$a]=$a;
			}
		}
		return ArtaTagsHtml::FilterControls('pageid',$x,trans('PAGE'));
	}

	function addLanguage(){
		$l=ArtaLoader::Language();
		$l->addtoNeed('pages','package');
	}

	function getRow($id){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__pages_widgets WHERE id='.$db->Quote($id));
		return $db->loadObject();
	}

	function getRowExistence($id){
		if(!isset($GLOBALS['CACHE']['language.widgets.ids'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id FROM #__pages_widgets');
			$GLOBALS['CACHE']['language.widgets.ids']=(array)$db->loadResultArray();
		}
		return (in_array($id, $GLOBALS['CACHE']['language.widgets.ids']));
	}

	function checkInput($v, $row){
		$v=ArtaFilterinput::clean($v, array('title'=>'string', 'content'=>'safe-html'));
		$v=ArtaFilterinput::trim($v);
		$v=ArtaFilterinput::array_limit($v, array('title'=>255));
		
		return $v;
	}

}

?>
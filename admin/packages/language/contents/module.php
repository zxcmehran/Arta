<?php
if(!defined('ARTA_VALID')){die('No access');}
/**
 * Modules Language Translation Handler.
 */
class LanguageTranslationModule implements LanguageTranslation{

	function getIDRowName(){
		return 'id';
	}
	
	function getTitleRowName(){
		return 'title';
	}
	
	function getTitle(){
		return trans('MODULES');
	}

	function getRows(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__modules'.ArtaTagsHtml::FilterResult('client=\'site\'').' ORDER BY location,`order` ASC '.ArtaTagsHtml::LimitResult());
		return $db->loadObjectList();
	}

	function getRowsCount(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT FOUND_ROWS()');
		return $db->loadResult();
	}
	
	function getControls(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT DISTINCT location FROM #__modules WHERE client=\'site\'');
		$g=(array)$db->loadResultArray();
		$x=array();
		foreach($g as $v){
			$x[$v]=$v;
		}
		
		return ArtaTagsHtml::FilterControls('location',$x,trans('LOCATION'));
	}
	

	function addLanguage(){
		$l=ArtaLoader::Language();
		$l->addtoNeed('modules','package');
	}
	

	function getRow($id){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__modules WHERE client=\'site\' AND id='.$db->Quote($id));
		return $db->loadObject();
	}

	function getRowExistence($id){
		if(!isset($GLOBALS['CACHE']['language.modules.ids'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id FROM #__modules WHERE client=\'site\'');
			$GLOBALS['CACHE']['language.modules.ids']=(array)$db->loadResultArray();
		}
		return (in_array($id, $GLOBALS['CACHE']['language.modules.ids']));
	}

	function checkInput($v, $row){
		$v=ArtaFilterinput::clean($v, array('title'=>'string', 'content'=>'safe-html'));
		if(substr($row->content, 0,5)=='MENU:'){
			$v['content']=$row->content;
		}

		$v=ArtaFilterinput::trim($v);
		$v=ArtaFilterinput::array_limit($v, array('title'=>255));
		return $v;
	}

}
?>
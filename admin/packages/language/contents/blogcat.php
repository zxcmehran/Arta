<?php
if(!defined('ARTA_VALID')){die('No access');}
/**
 * Blog Categories Language Translation Handler.
 */
class LanguageTranslationBlogcat implements LanguageTranslation{
	
	function getIDRowName(){
		return 'id';
	}
	
	function getTitleRowName(){
		return 'title';
	}
	
	function getTitle(){
		return trans('BLOG CATS');
	}
	
	function getRows(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__blogcategories ORDER BY `id` DESC '.ArtaTagsHtml::LimitResult());
		return $db->loadObjectList();
	}
	
	function getRowsCount(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT FOUND_ROWS()');
		return $db->loadResult();
	}
	
	function getControls(){
		return null;
	}
	
	function addLanguage(){
		$l=ArtaLoader::Language();
		$l->addtoNeed('blog','package');
	}
	
	function getRow($id){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__blogcategories WHERE id='.$db->Quote($id));
		return $db->loadObject();
	}
	
	function getRowExistence($id){
		if(!isset($GLOBALS['CACHE']['language.blogcats.ids'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id FROM #__blogcategories');
			$GLOBALS['CACHE']['language.blogcats.ids']=(array)$db->loadResultArray();
		}
		return (in_array($id, $GLOBALS['CACHE']['language.blogcats.ids']));
	}
	
	function checkInput($v,$row){
		$v=ArtaFilterinput::clean($v, array('title'=>'string', 'desc'=>'string'));
		$v=ArtaFilterinput::trim($v);
		$v=ArtaFilterinput::array_limit($v, array('title'=>255));
		return $v;
	}

}
?>
<?php
if(!defined('ARTA_VALID')){die('No access');}
/**
 * Links Language Translation Handler.
 */
class LanguageTranslationLink implements LanguageTranslation{

	function getIDRowName(){
		return 'id';
	}
	
	function getTitleRowName(){
		return 'title';
	}
	
	function getTitle(){
		return trans('LINKS');
	}

	function getRows(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__links'.ArtaTagsHtml::FilterResult().' ORDER BY `id` ASC '.ArtaTagsHtml::LimitResult());
		return $db->loadObjectList();
	}

	function getRowsCount(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT FOUND_ROWS()');
		return $db->loadResult();
	}
	
	function getControls(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT id,title FROM #__link_groups');
		$g=(array)$db->loadObjectList();
		
		$x=array();
		foreach($g as $a){
			$x[$a->id]=$a->title;
		}
		
		return '<table><tr><td>'.ArtaTagsHtml::FilterControls('type',array('inner'=>trans('LINKTYPE_INNER'), 'outer'=> trans('LINKTYPE_OUTER'), 'default'=>trans('LINKTYPE_DEFAULT')),trans('LINKTYPE')).'</td><td>'.ArtaTagsHtml::FilterControls('group', $x, trans('LINKGROUP')).'</td></tr></table>';
	}
	

	function addLanguage(){
		$l=ArtaLoader::Language();
		$l->addtoNeed('links','package');
	}
	

	function getRow($id){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__links WHERE id='.$db->Quote($id));
		return $db->loadObject();
	}

	function getRowExistence($id){
		if(!isset($GLOBALS['CACHE']['language.links.ids'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id FROM #__links');
			$GLOBALS['CACHE']['language.links.ids']=(array)$db->loadResultArray();
		}
		return (in_array($id, $GLOBALS['CACHE']['language.links.ids']));
	}

	function checkInput($v, $row){
		$v=ArtaFilterinput::clean($v, array('title'=>'string'));
		$v=ArtaFilterinput::trim($v);
		$v=ArtaFilterinput::array_limit($v, array('title'=>255));
		return $v;
	}

}
?>
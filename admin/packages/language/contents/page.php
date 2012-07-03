<?php
if(!defined('ARTA_VALID')){die('No access');}
/**
 * Page Language Translation Handler.
 */
class LanguageTranslationPage implements LanguageTranslation{
	
	function getIDRowName(){
		return 'id';
	}
	
	function getTitleRowName(){
		return 'title';
	}
	
	function getTitle(){
		return trans('PAGES');
	}
	
	function getRows(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__pages'.ArtaTagsHtml::FilterResult().' ORDER BY `id` DESC '.ArtaTagsHtml::LimitResult());
		$rets= $db->loadObjectList();
		return $rets;
	}

	function getRowsCount(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT FOUND_ROWS()');
		return $db->loadResult();
	}

	function getControls(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT DISTINCT added_by FROM #__pages');
		$authors=$db->loadResultArray();
		$db->setQuery('SELECT id,username FROM #__users WHERE id IN ('.implode(',', $authors).')');
		$userz=$db->loadObjectList();
		if($userz!==null){
			$userz=ArtaUtility::keybyChild($userz, 'id');
		}
		$x=array();
		foreach($authors as $a){
			if(isset($userz[$a])){
				$x[$a]=$userz[$a]->username;
			}else{
				$x[$a]=$a;
			}
		}
		return ArtaTagsHtml::FilterControls('added_by',$x,trans('ADDED_BY')).' '.ArtaTagsHtml::FilterControls('is_dynamic',array(trans('STATIC'), trans('DYNAMIC')),trans('PAGE TYPE'));
	}

	function addLanguage(){
		$l=ArtaLoader::Language();
		$l->addtoNeed('pages','package');
	}

	function getRow($id){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($id));
		$ret=  $db->loadObject();
		if($ret->is_dynamic){
			$ret->content='--';
		}
		return $ret;
	}
	
	function getRowExistence($id){
		if(!isset($GLOBALS['CACHE']['language.pages.ids'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id FROM #__pages');
			$GLOBALS['CACHE']['language.pages.ids']=(array)$db->loadResultArray();
		}
		return (in_array($id, $GLOBALS['CACHE']['language.pages.ids']));
	}
	
	function checkInput($v, $row){
		$v=ArtaFilterinput::clean($v, array('title'=>'string', 'desc'=>'string', 'content'=>'safe-html', 'tags'=>'array'));
		$v=ArtaFilterinput::trim($v);
		$v=ArtaFilterinput::array_limit($v, array('title'=>255));
		
		if($row->is_dynamic){
			$v['content']='';
		}
		
		$tgx=explode(',', $row->tags);
		foreach($v['tags'] as $tgk=>&$tg){
			$tg=ArtaFilterinput::clean($tg, 'string');
			if(trim($tg)==''){
				$tg=$tgx[$tgk];
			}
		}
		$v['tags']=implode(',',$v['tags']);
		return $v;
	}

}
?>
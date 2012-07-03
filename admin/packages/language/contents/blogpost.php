<?php
if(!defined('ARTA_VALID')){die('No access');}
/**
 * Blogpost Language Translation Handler.
 */
class LanguageTranslationBlogpost implements LanguageTranslation{
	
	/**
	 * Identify column of original content row.
	 * default: id
	 */
	function getIDRowName(){
		return 'id';
	}
	
	/**
	 * Title column of original content row.
	 * default: title
	 */
	function getTitleRowName(){
		return 'title';
	}
	
	/**
	 * Title of this Translation script e.g. "Blog posts" or "Links" or "Banners" or...
	 */
	function getTitle(){
		return trans('BLOG POSTS');
	}
	
	
	/**
	 * Return your original content from this method
	 */
	function getRows(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT SQL_CALC_FOUND_ROWS * FROM #__blogposts'.ArtaTagsHtml::FilterResult().' ORDER BY `added_time` DESC '.ArtaTagsHtml::LimitResult());
		return $db->loadObjectList();
	}
	
	/**
	 * Return your contents count from this method.
	 */
	function getRowsCount(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT FOUND_ROWS()');
		return $db->loadResult();
	}
	
	/**
	 * Return controls that you want to show on top of content list. For example Filters or Sorting Controls.
	 */
	function getControls(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT DISTINCT added_by FROM #__blogposts');
		$authors=$db->loadResultArray();
		$db->setQuery('SELECT id,username FROM #__users WHERE id IN ('.implode(',', $authors).')');
		$userz=$db->loadObjectList('id');
		
		$x=array();
		foreach($authors as $a){
			if(isset($userz[$a])){
				$x[$a]=$userz[$a]->username;
			}else{
				$x[$a]=$a;
			}
		}
		return ArtaTagsHtml::FilterControls('added_by',$x,trans('AUTHOR'));
	}
	
	/**
	 * Import languages you need for getControls() method.
	 */
	function addLanguage(){
		$l=ArtaLoader::Language();
		$l->addtoNeed('blog','package');
	}
	
	/**
	 * Return identified row completely. 
	 */
	function getRow($id){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__blogposts WHERE id='.$db->Quote($id));
		return $db->loadObject();
	}
	
	/**
	 * Does this row exists?
	 */
	function getRowExistence($id){
		if(!isset($GLOBALS['CACHE']['language.blogpost.ids'])){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id FROM #__blogposts');
			$GLOBALS['CACHE']['language.blogpost.ids']=(array)$db->loadResultArray();
		}
		return (in_array($id, $GLOBALS['CACHE']['language.blogpost.ids']));
	}
	
	/**
	 * Check input sent from translation edit form.
	 * $v	input
	 * $row	default row
	 */
	function checkInput($v, $row){
		$v=ArtaFilterinput::clean($v, array('title'=>'string', 'introcontent'=>'safe-html', 'morecontent'=>'safe-html', 'tags'=>'array'));
		$v=ArtaFilterinput::trim($v);
		$v=ArtaFilterinput::array_limit($v, array('title'=>255));
		
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
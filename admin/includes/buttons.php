<?php
/**
 * Arta Admin Buttons container.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */ 
 
if(!defined('ARTA_VALID')){die('No access');}
// load language file
$l=ArtaLoader::Language();
$l->addtoNeed('buttons', 'module');

/**
 * Helps you to add entries to adminbutton module.
 * @static
 */
class ArtaAdminButtons{
	static function addButton($name, $img, $params=array()){
		if(!isset($params['href'])){
			$params['href']='#buttons_hand';
		}
		$a='';
		foreach($params as $k=>$v){
			$a .=' '.$k.'="'.htmlspecialchars($v).'"';
		}
		if(!isset($GLOBALS['_BUTTONS'])){
			$GLOBALS['_BUTTONS']=array();
		}
		$GLOBALS['_BUTTONS'][]='<div class="mod_button"><a'.$a.'><img src="'.htmlspecialchars($img).'"/><div>'.$name.'</div></a></div>';
	}

	static function addSave($formvars='', $prefix='', $method="post"){
		if(!is_array($formvars)){$formvars=array('task'=>'save');}else{
			$formvars=ArtaUtility::array_extend($formvars,array('task'=>'save'));
		}
		$s='';
		foreach($formvars as $k=>$v){
			$s .="AdminFormTools.setVar('{$k}','{$v}');";
		}
		ArtaAdminButtons::addButton(trans('SAVE'), Imageset('save.png'), array(
		'onclick'=>$prefix.$s."AdminFormTools.setMethod('{$method}');AdminFormTools.submitForm();"));
	}

	static function addNew($formvars='', $prefix='', $method='get'){
		if(!is_array($formvars)){$formvars=array('view'=>'new');}else{
			$formvars=ArtaUtility::array_extend($formvars,array('view'=>'new'));
		}
		$s='';
		foreach($formvars as $k=>$v){
			$s .="AdminFormTools.setVar('{$k}','{$v}');";
		}
		ArtaAdminButtons::addButton(trans('NEW'), Imageset('new.png'), array('onclick'=>$prefix.$s."AdminFormTools.uncheckAll($$('.idcheck'));AdminFormTools.setMethod('{$method}');AdminFormTools.submitForm();"));
	}

	static function addEdit($formvars='', $prefix='',$method='get'){
		if(!is_array($formvars)){$formvars=array('view'=>'new', 'method'=>'post');}else{
			$formvars=ArtaUtility::array_extend($formvars,array('view'=>'new'));
		}
		$s='';
		foreach($formvars as $k=>$v){
			$s .="AdminFormTools.setVar('{$k}','{$v}');";
		}
		ArtaAdminButtons::addButton(trans('EDIT'), Imageset('edit.png'), array('onclick'=>$prefix.$s."
		if(AdminFormTools.hasChecked($$('.idcheck'))==true){
			AdminFormTools.setMethod('{$method}');
			AdminFormTools.submitForm();
		}else{
			alert('".trans('PLEASE SELECT A ROW')."');
		}"));
	}

	static function addCancel($url=false){
		if($url==false){
			ArtaAdminButtons::addButton(trans('CANCEL'), Imageset('cancel.png'), array('onclick'=>"w=window.opener==null?window.parent:window.opener;if(w!=window){window.close();return false;}else{history.back();return false;}"));
		}else{
			ArtaAdminButtons::addButton(trans('CANCEL'), Imageset('cancel.png'), array('href'=>$url));
		}
	}

	static function addReset(){
		ArtaAdminButtons::addButton(trans('RESET'), Imageset('reset.png'), array('onclick'=>"AdminFormTools.resetForm();"));
	}

	static function addDelete($formvars='', $prefix='', $method='post'){
		if(!is_array($formvars)){$formvars=array('task'=>'delete');}else{
			$formvars=ArtaUtility::array_extend($formvars,array('task'=>'delete'));
		}
		$s='';
		foreach($formvars as $k=>$v){
			$s .="AdminFormTools.setVar('{$k}','{$v}');";
		}
		ArtaAdminButtons::addButton(trans('DELETE'), Imageset('delete.png'), array('onclick'=>$prefix.$s."if(AdminFormTools.hasChecked($$('.idcheck'))==true){
			if(confirm('".trans('ARE YOU SURE TO DELETE')."')){
				AdminFormTools.setMethod('{$method}');
				AdminFormTools.submitForm();
			}
		}else{
			alert('".trans('PLEASE SELECT A ROW')."');
		}"));
	}
	
	static function addSetting($extname, $extype='package'){
		if(!isset($GLOBALS['_BUTTONS'])){
			$GLOBALS['_BUTTONS']=array();
		}
		$GLOBALS['_BUTTONS'][]=ArtaTagsHtml::Window('<div class="mod_button"><img src="'.(Imageset('config.png')).'"/><div>'.trans('CHANGE SETTINGS').'</div></div>', 'index.php?pack=config&view=edit&extype='.$extype.'&client=site&tmpl=package&extname='.$extname, trans('CHANGE SETTINGS'));
	} 
	

}
?>
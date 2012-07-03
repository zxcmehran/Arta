<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/21 12:29 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class PagesModelEnvironment_editor extends ArtaPackageModel{
	
	function __construct(){
		if(ArtaUsergroup::getPerm('can_addedit_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT PAGES'));
		}
		
		$data=$this->getEditing();
		$u=$this->getCurrentUser();
		if($data->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
		}
		
		$db=ArtaLoader::DB();
		$lang=ArtaLoader::Language();
		$id=getVar('id', '', '', 'string');
		if($id!=null){
			$id=base64_decode($id);
			//page
			
			$id=substr($id, 7);
			$db->setQuery('SELECT * FROM #__pages_widgets WHERE id='.$db->Quote($id));
			$r=$db->loadObject();
			if($r==null){
				ArtaError::show(404);
			}
			
			if((int)$r->pageid!=(int)$data->id){
				ArtaError::show(403);
			}
			// rewrite $r->widget if changed by user form
			$widname=getVar('new_widget',(int)$r->widget, '', 'int');
			if($widname>0){
				$db->setQuery('SELECT * FROM #__pages_widgets_resource WHERE id='.$db->Quote($widname));
				$wid=$db->loadObject();
				if($wid==null){
					$r->widget=0;
				}else{
					$lang->addtoNeed($wid->filename, 'widget');
					
					$r->widget=$wid->id;
				}
			}elseif($widname===0){
				$r->widget=0;
			}
			
			
			$this->widget=$r;
		}else{
			$r=new stdClass;
			$r->title=null;
			$r->content=null;
			$r->pageid=null;
			$r->params=null;
			$r->widget=0;
			$r->new=true;
			
			// rewrite $r->widget if changed by user form
			$widname=getVar('new_widget',null, '', 'int');
			if($widname>0){
				$db->setQuery('SELECT * FROM #__pages_widgets_resource WHERE id='.$db->Quote($widname));
				$wid=$db->loadObject();
				if($wid==null){
					$r->widget=0;
				}else{
					$lang->addtoNeed($wid->filename, 'widget');
					$r->widget=$wid->id;
				}
			}
			$this->widget=$r;
		}
	}
	
	function getWidget(){
		$this->widget->params=unserialize($this->widget->params);
		$this->widget->params=ArtaUtility::array_extend((array)$this->widget->params, array('width'=>'auto', 'height'=>'auto'));
		return $this->widget;
	}
		
	function getEditing(){
		$id=getVar('pid', '', '', 'int');
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($id));
		$r=$db->loadObject();
		if($r==null){
			ArtaError::show();
		}
		if(strlen($r->mods) && $r->mods{0}=='-'){
			$r->mods=substr($r->mods,1);
			$r->deny_type=1;
		}else{
			$r->deny_type=0;
		}
		return $r;
	}
	
	function getSettings(){
		$db=ArtaLoader::DB();
		$id=$this->widget->widget;
		$db->setQuery('SELECT * FROM #__pages_widgets_resource WHERE id='.$db->Quote($id));
		$w=$db->loadObject();
		if($w==null){
			ArtaError::show();
		}
		$sets=$this->widget->params;
		if(!is_array($sets)){
			$sets=unserialize($sets);
		}
		$sets=@$sets['settings'];
		$db->setQuery('SELECT * FROM #__settings WHERE extype=\'widget\' AND extname='.$db->Quote($w->filename));
		$r= (array)$db->loadObjectList();
		foreach($r as $k=>$v){
			if(isset($sets[$v->var])){
				$r[$k]->value=$sets[$v->var];
			}else{
				$r[$k]->value=unserialize($r[$k]->value);
			}
		}
		return $r;
		
		
	}
	
	function getWidgets(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT id,`title` FROM #__pages_widgets_resource');
		$r=(array)$db->loadObjectList();
		$x=new stdClass;
		$x->id=0;
		$x->title=trans('CUSTOM WIDGET');
		return array_merge(array($x),$r);
	}
	
}

?>
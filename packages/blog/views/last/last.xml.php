<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewLast extends ArtaPackageView{
	
	function Display(){
		$m=$this->getModel();
		$data=$m->getRSSItems(getVar('blogid',false, '', 'int'));
		$this->assign('items', $data);
		$b=$m->getBlogID(getVar('blogid',false, '', 'int'));
		
		if(!$b){
			$b=new stdClass;
			$b->id=0;
			$b->title=trans('BLOG');
			$b->desc='';
			$b->parent=0;
			$b->denied=0;
		}else{
			$plugin=ArtaLoader::Plugin();
			$plugin->trigger('onPrepareContent', array(&$b, 'blogcat'));
		}
		
		if((string)getVar('tagname', '', '', 'string')!=''){
			$title=' - '.(trans('POSTS TAGGED').' "'.getVar('tagname', '','','string')).'"';
		}else{
			$title='';
		}
		$b->title.=$title;
		
		$this->assign('blog', $b);
		
		$this->setLayout('rss');
		$this->render();
	}
	
	
}
?>
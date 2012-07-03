<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewIndex extends ArtaPackageView{
	
	function Display(){
		
		
		$m=$this->getModel();
		$data=$m->getItems();
		
		$blogid=getVar('blogid',0,'','int');
		
		$cats=@$this->getCategoryPath($blogid, $m);
		
		$this->addPath(trans('BLOG INDEX'), 'index.php?pack=blog&view=index');
		
		$lastk=0;
		foreach($cats as $k=>$v){
			$this->addPath($v, 'index.php?pack=blog&view=last&blogid='.$k);
			$lastk=$k;
		}
		
		$last=array_pop($cats);
		
		if((string)getVar('tagname', '', '', 'string')!=''){
			$title=' - '.(trans('POSTS TAGGED').' "'.getVar('tagname', '','','string')).'"';
			$this->addPath(trans('POSTS TAGGED').' "'.getVar('tagname', '','','string').'"', 
			($lastk!=0?'index.php?pack=blog&view=last&blogid='.$lastk:'index.php?pack=blog&view=last').
			'&tagname='.urlencode(getVar('tagname', '','','string')));
		}else{
			$title='';
		}
		
		if($blogid>0){
			$this->setTitle($last.$title);
		}else{
			$this->setTitle(trans('BLOG INDEX').$title);
		}
		
		$this->assign('cats', $m->getCategories());
		$this->assign('items', $data);
		$this->assign('m', $m);
		$this->assign('count', $m->getCount());
		$this->render();
	}
	
	function getCategoryPath($id, $m){
		$p=array();
		$p_ids=array();

		$r=$m->getBlogID($id);
		while(is_numeric($r->parent) && $r->parent!=='0'){
			$p[]=htmlspecialchars($r->title);
			$p_ids[]=$r->id;
			$r=$m->getBlogID($r->parent);
		}
		$p[]=htmlspecialchars($r->title);
		$p_ids[]=$r->id;
		$r=array();
		foreach($p as $k=>$v){
			$r[$p_ids[count($p)-($k+1)]]=$p[count($p)-($k+1)];
		}
		return $r;
	}
	
}
?>
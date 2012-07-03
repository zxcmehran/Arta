<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewNew extends ArtaPackageView{
	
	var $list='';
	
	function Display(){
		if(getVar('keep_alive',false)!=false){
			echo '<res>keep_alive</res>';
			return;
		}
                $m=$this->getModel();
                
                if(getVar('catlist', false)==true){
                         $cats = $this->popit($m->getCategories());
                         $i = getVar('id',false);
                         echo ArtaTagsHtml::select('blogid', $cats, $i);
                }else 
                
		if(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog')){
			$item=$m->getTags();
			$this->assign('item',$item);
			$this->setLayout('tags');
			$this->render();
			
		}else{
			redirect('index.php', trans('YOU CANNOT ADDEDIT BLOG POSTS'), 'error');
		}
	}
        
        function popit($m){
		$a=array();
		foreach($m as $k=>$v){
			$a[$v->id]=$v->title;
		}
		return $a;
	}
	
}
?>
<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewNew extends ArtaPackageView{
	
	var $list='';
	
	function Display(){
		if(getVar('keep_alive',false)!=false){
			echo '<res>keep_alive</res>';
			return;
		}
		if(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog')){
			$m=$this->getModel();
			$item=$m->getTags();
			$this->assign('item',$item);
			$this->setLayout('tags');
			$this->render();
		}else{
			redirect('index.php', trans('YOU CANNOT ADDEDIT BLOG POSTS'), 'error');
		}
	}
	
}
?>
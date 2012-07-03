<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewNew extends ArtaPackageView{
	
	var $list='';
	
	function Display(){
		if(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog')){
			$m=$this->getModel();
			$id=getVar('id', 0, '', 'int');
			$item=$m->getItem($id);
			$u=ArtaLoader::User();
			$u=$u->getCurrentUser();			
			if($item->added_by!==null && $item->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog')==false){
				ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS BLOG POSTS'));
			}
			if($id>0){
				$title=trans('EDIT POST').': '.$item->title;
				
			}else{
				$title=trans('NEW POST');
			}
			
			$this->setTitle($title);
			$this->addPath($title);
			
			$this->assign('title',$title);
			$this->assign('item',$item);
			ArtaTagsHtml::addHeader('<script>
			new PeriodicalExecuter(function(pe) {
				new Ajax.Request(client_url+\'index.php?pack=blog&view=new&type=xml&keep_alive=1\', {method: \'get\'});
			}, 300);
			</script>');
			$this->render();
		}else{
			redirect('index.php', trans('YOU CANNOT ADDEDIT BLOG POSTS'), 'error');
		}
	}
	
}
?>
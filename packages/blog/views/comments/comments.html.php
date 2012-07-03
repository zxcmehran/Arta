<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewComments extends ArtaPackageView{
	
	function display(){
		$id=getVar('id', false,'','int');
		if($id<=0){
			redirect('index.php?pack=blog');
		}
		$cid=getVar('cid', false,'','int');
		$ml=$this->getSetting('multilingual_comments',0, 'blog');
		
		$canedit=ArtaUsergroup::getPerm('can_edit_post_comments', 'package', 'blog');
		$caneditothers=ArtaUsergroup::getPerm('can_edit_others_comments', 'package', 'blog');
		
		$model=$this->getModel();
		$this->setTitle(trans('EDIT COMMENT'));
		$com=$model->getComment($cid, $id);
		$this->assign('comment', $com);
		$user=ArtaLoader::User();
		$u=$user->getUser($com->added_by);
		$cu=$this->getCurrentUser();
		
		if($canedit==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT COMMENTS'));
		}
		
		if(@(($u->id==$cu->id && $u->id!=0) || $caneditothers)==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS COMMENTS'));
		}
		$this->assign('ml', $ml);
		if($ml){
			$this->assign('langs', $model->getLanguages());
		}
		$this->render('edit');
		
	}

}
?>
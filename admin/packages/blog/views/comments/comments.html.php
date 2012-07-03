<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewComments extends ArtaPackageView{
	
	function display(){
		if(ArtaUsergroup::getPerm('can_access_post_comments', 'package', 'blog')==false){
			ArtaError::show(403, trans('YOU CANNOT ACCESS POST COMMENTS'));
		}
		$id=getVar('id', false,'','int');
		if($id<=0){
			redirect('index.php?pack=blog');
		}
		$cid=getVar('cid', false,'','int');
		$ml=$this->getSetting('multilingual_comments',0, 'blog', 'site');
		if($cid<=0){
			ArtaAdminTips::addTip(trans('COMMENTS TIP'));
			$model=$this->getModel();
			$this->setTitle(trans('POST COMMENTS'));
			
			$this->assign('comments', $model->getComments($id));
			
			$this->assign('ml', $ml);
			if($ml){
				$this->assign('langs', $model->getLanguages());
			}
			$this->render();
			
		}else{
			if(ArtaUsergroup::getPerm('can_edit_post_comments', 'package', 'blog')==false){
				ArtaError::show(403, trans('YOU CANNOT EDIT COMMENTS'));
			}
			ArtaAdminTips::addTip(trans('EDIT COMMENTS TIP'));
			ArtaAdminButtons::addSave();
			ArtaAdminButtons::addCancel();
			$model=$this->getModel();
			$this->setTitle(trans('EDIT COMMENT'));
			
			$this->assign('comment', $model->getComment($cid, $id));

			$this->assign('ml', $ml);
			if($ml){
				$this->assign('langs', $model->getLanguages());
			}
			$this->render('edit');
		}
	}

}
?>
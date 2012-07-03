<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewNew extends ArtaPackageView{
	
	function display(){
		
		if(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog')){
			$ids=getVar('ids', false,'','array');
			if($ids==false){$title=trans('NEW post');}else{$title=trans('EDIT post');}
			$this->setTitle($title);
			$model=$this->getModel();
			$x=$model->getPost($ids);
			$u=$this->getCurrentUser();
			if($x->added_by!==null && $x->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog')==false){
				redirect('index.php?pack=blog', trans('YOU CANNOT EDIT OTHERS BLOG POSTS'), 'error');
			}
			$this->assign('post', $x);
			$this->assign('title', $title);
			ArtaAdminButtons::addSave('', "
			f=AdminFormTools.getForm();
			if(f.title.value.match(/^ *$/g)!==null){
				alert('".JSValue(trans('NO POST TITLE SPECIFIED'))."');
				return false;
			}
			");
			ArtaAdminButtons::addCancel();
	
			ArtaTagsHtml::addHeader('<script>
			new PeriodicalExecuter(function(pe) {
				new Ajax.Request(client_url+\'index.php?pack=blog&view=new&type=xml&keep_alive=1\', {method: \'get\'});
			}, 300);
			</script>');
	
			$this->render();
		}else{
			redirect('index.php?pack=blog', trans('YOU CANNOT ADDEDIT BLOG POSTS'), 'error');
		}
		
	}

}
?>
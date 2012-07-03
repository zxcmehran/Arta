<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/18 13:9 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
 
class UserViewEdit extends ArtaPackageView{
	function display(){
		$u=$this->getCurrentUser();
		if($u->id!=0){
			$this->setTitle(trans('EDIT YOUR DETAILS'));
			$this->addPath(trans('EDIT YOUR DETAILS'), 'index.php?pack=user&view=edit');
			$this->render();
		}else{
			redirect('index.php?pack=user&view=login&redirect='.base64_encode('index.php?pack=user&view=edit'), trans('YOU ARE NOT LOGGED IN'), 'warning');
		}
	}
}

?>
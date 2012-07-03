<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/18 13:9 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
 
class UserViewAvatar extends ArtaPackageView{
	function display(){
		$u=$this->getCurrentUser();
		if($u->id!=0){
			$this->setTitle(trans('AVATAR SETTINGS'));
			$this->addPath(trans('AVATAR SETTINGS'), 'index.php?pack=user&view=avatar');
			$this->render();
		}else{
			redirect('index.php?pack=user&view=login&redirect='.base64_encode('index.php?pack=user&view=avatar'), trans('YOU ARE NOT LOGGED IN'), 'warning');
		
		}
	}
}

?>
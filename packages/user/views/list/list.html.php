<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/18 13:9 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
 
class UserViewlist extends ArtaPackageView{
	function display(){
		if($this->getSetting('list_enabled', 1)==true){
			if(ArtaUsergroup::getPerm('can_access_memberlist', 'package', 'user')){
				$this->setTitle(trans('MEMBERS LIST'));
				$this->addPath(trans('MEMBERS LIST'), 'index.php?pack=user&view=list');
				$m=$this->getModel();
				$this->assign('users', $m->getUsers());
				$this->assign('c', $m->c);
				$this->render();
			}else{
				ArtaError::show(403);
			}
		}else{
			ArtaError::show(403, trans('USERLIST DISABLED BY ADMIN'));
		}
		
	}
}

?>
<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/18 13:9 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
 
class UserViewOpenid extends ArtaPackageView{
	function display(){
		if($this->getSetting('allow_openid_usage', true)!=true){
			ArtaError::show(403, 'OpenID is disabled.');
		}
		$u=$this->getCurrentUser();
		if($u->id!==0){
			$this->setTitle(trans('OPENID ACCOUNTS'));
			$this->addPath(trans('OPENID ACCOUNTS'), 'index.php?pack=user&view=openid');
			$m=$this->getModel();
			$this->assign('d', $m->getOpenIDs());
			$this->render();
		}else{
			ArtaError::show('403');
		}
	}
}

?>
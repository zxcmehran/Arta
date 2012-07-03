<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/18 13:9 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
 
class UserViewNotes extends ArtaPackageView{
	function display(){
		$u=$this->getCurrentUser();
		if($u->id!==0){
			$this->setTitle(trans('Personal notes'));
			$this->addPath(trans('Personal notes'), 'index.php?pack=user&view=notes');
			$this->assign('t', ArtaUserHelper::getText($u->id, 'id'));
			$this->assign('m', ArtaUserHelper::getModified($u->id, 'id'));
			$this->render();
		}else{
			ArtaError::show('403');
		}
	}
}

?>
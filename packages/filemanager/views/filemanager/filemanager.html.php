<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/2/14 17:41 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class FilemanagerViewFilemanager extends ArtaPackageView{
	
	function displayFilemanager(){
		$this->setTitle(trans('FILE MANAGER'));
		$list=ArtaFile::listDir(ARTAPATH_BASEDIR.'/content');
		$f=array();
		foreach($list as $v){
			if(is_dir(ARTAPATH_BASEDIR.'/content/'.$v)){
				$f[$v]='/'.$v.'/';
			}
		}
		ksort($f);
		$this->assign('filez',$f);
		$this->render();
	}
	
}

?>
<?php 
class CphomeController extends ArtaPackageController{
		function display(){
			$view = $this->getView('index', getVar('type', 'html','','string'));
			$view->display();
		}

		function saveuser(){
			$d=getVar('usernote', '', '', 'string');
			if($d==''){
				$d=' ';
			}
			$user=ArtaLoader::User();
			$u=$user->getCurrentUser();
			ArtaLoader::Import('user->helper');
			if(ArtaUserHelper::setText($d,$u->username)){
				redirect('index.php?pack=cphome', trans('saved succ'));
			}else{
				ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=cphome');
			}
		}
		
		function saveadmin(){
			$d=getVar('adminnote', '', '', 'string');
			if($d==''){
				$d=' ';
			}
			
			$data='<?php if(!defined(\'ARTA_VALID\')){die(\'No access\');} $msg=\''.addslashes(base64_encode(convert_uuencode($d))).'\'; ?>';
			
			if(ArtaFile::write(ARTAPATH_PACKDIR.'/data/data.php', $data)){
				redirect('index.php?pack=cphome', trans('saved succ'));
			}else{
				ArtaError::show(500, trans('ERROR IN FILE'), 'index.php?pack=cphome');
			}
		}
}

?>
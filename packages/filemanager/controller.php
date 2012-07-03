<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/2/14 17:38 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class FilemanagerController extends ArtaPackageController{
	function display(){
		$v=$this->getView('filemanager', getVar('type', 'html', '', 'string'));
		$funcname = getVar('do', 'displayFilemanager', '', 'funcname');
		eval('$v->'.$funcname.'();');
	}
	
	function upload(){
		$v=ArtaRequest::getVars('request', 'object');
		if(!$v->dest){
			ArtaError::show(400);
		}
		if($v->editor==1){
			$e='&editor=1';
		}else{
			$e='';
		}
		if(ArtaUsergroup::getPerm('can_upload_files', 'package', 'filemanager')){
			$types=$this->getSetting('allowed_filetypes_to_upload','jpg,jpeg,gif,png,swf,flv,bmp,psd,mp3,wav,wma,wm,asf,mp4,mpg,mpeg,avi,wmv,mkv,3gp,rm,zip,rar,7z,gz,bz,tar,doc,pdf,xls,wri,docx,rtf,sis,jar,cab,apk,txt');
			
			$errmsg=ArtaFilterinput::UploadErr($_FILES['uploadedFile']['error']);
			if($errmsg!==false){
				redirect('index.php?pack=filemanager'.$e, $errmsg, 'error');
			}
			$types=explode(',',$types);
			$types=array_map('trim', $types);
			$types=array_map('strtolower', $types);
			$ext=strtolower(ArtaFile::getExt($_FILES['uploadedFile']['name']));
			if(!in_array($ext, $types) || $_FILES['uploadedFile']['name']=='.htaccess'){
				
				redirect('index.php?pack=filemanager'.$e, trans('INVALID_FILETYPE'), 'error');
			}
			if($_FILES['uploadedFile']['size']>($this->getSetting('allowed_filesize_to_upload',1024)*1024)){
				redirect('index.php?pack=filemanager'.$e, trans('INVALID_FILESIZE'), 'error');
			}		/** INVALID allowed_filesize_to_upload VALUE */
			$v->dest=str_replace('..', '', $v->dest);
			if(is_uploaded_file($_FILES['uploadedFile']['tmp_name']) && ArtaFile::rename($_FILES['uploadedFile']['tmp_name'], ARTAPATH_BASEDIR.'/content/'.$v->dest.$_FILES['uploadedFile']['name'])){
				redirect('index.php?pack=filemanager'.$e, trans('UPLOADED_MOVED_SUCC'));
			}else{
				ArtaError::addAdminAlert('Site Filemanager', 'Uploading a file', 'The uploaded file couldn\'t be moved to "content'.$v->dest.'". It maybe because of bad permissions of destination folder. Try to setting it\'s permission to "0755".');
				redirect('index.php?pack=filemanager'.$e, trans('UPLOADED_NOTMOVED'), 'error');
			}
		}else{
			redirect('index.php?pack=filemanager'.$e, trans('YOU ARE NOT AUTHORIZED'), 'warning');
		}
	}
	
	function newfolder(){
		
		$v=ArtaRequest::getVars('post', 'object');
		if(!$v->dest || !$v->name){
			ArtaError::show(400);
		}
		if(ArtaUsergroup::getPerm('can_upload_files', 'package', 'filemanager')){
			$v->name=ArtaFilterinput::clean( $v->name,'filename');
			if(ArtaFile::mkdir(ARTAPATH_BASEDIR.'/content/'.$v->dest.$v->name)){
				if($v->editor==1){
					$e='&editor=1';
				}else{
					$e='';
				}
				redirect('index.php?pack=filemanager'.$e, trans('MAKED_SUCC'));
			}else{
				ArtaError::addAdminAlert('Site Filemanager', 'Making a directory', 'The following directory could\'t be maked: "content'.$v->dest.'". It maybe because of bad permissions of destination folder. Try to setting it\'s permission to "0777".');
				if($v->editor==1){
					$e='&editor=1';
				}else{
					$e='';
				}
				redirect('index.php?pack=filemanager'.$e, trans('NOTMAKED'), 'error');
			}
		}else{
			redirect('index.php?pack=filemanager'.$e, trans('YOU ARE NOT AUTHORIZED'), 'warning');
		}
	}
}
?>
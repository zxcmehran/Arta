<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ToolsController extends ArtaPackageController{

	function display(){
		$v=$this->getView(getVar('view','diagnostic', '','string'));
		$v->Display();
	}

	function optimizeTables(){
		if(ArtaUsergroup::getPerm('can_optimize_repair_tables', 'package', 'tools')==false){
			ArtaError::show(403);
		}
		$m=$this->getModel('optimize');
		$v=$this->getView('optimize');
		$res=$m->getResult();
		
		$v->assign('res', $res);
		$v->generateLog();
	}
	
	function repairTables(){
		if(ArtaUsergroup::getPerm('can_optimize_repair_tables', 'package', 'tools')==false){
			ArtaError::show(403);
		}
		$m=$this->getModel('repair');
		$v=$this->getView('repair');
		$res=$m->getResult();
		
		$v->assign('res', $res);
		$v->generateLog();
	}
	
	function cleanCache(){
		if(ArtaUsergroup::getPerm('can_clean_cache', 'package', 'tools')==false){
			ArtaError::show(403);
		}
		$m=$this->getModel('cache');
		$v=$this->getView('cache');
		$res=$m->getResult();
		
		$v->assign('res', $res);
		$v->generateLog();
	}
	
	function diag_mail(){
		if(ArtaUsergroup::getPerm('can_use_diags', 'package', 'tools')==false){
			ArtaError::show(403);
		}
		$address=getVar('mail');
		if(strlen($address)<3 || strpos($address,'@')===false){
			redirect('index.php?pack=tools&view=diagnostic&task=display', trans('INVALID MAIL ADDRESS'), 'error');
		}
		$mail=ArtaLoader::Mail();
		$res=$mail->mail($address, 'Mail sending test', 'This is an e-mail message sent automatically by Arta while testing the Mail Function of your website. ');
		if($res==false){
			redirect('index.php?pack=tools&view=diagnostic&task=display', trans('MAILERROR'), 'error');
		}else{
			redirect('index.php?pack=tools&view=diagnostic&task=display', trans('MESSAGE SENT CHECK INBOX'));
		}
	}
	
	function diag_upload(){
		if(ArtaUsergroup::getPerm('can_use_diags', 'package', 'tools')==false){
			ArtaError::show(403);
		}
		$res=($_FILES['myfile']['error']==0);
		if($res==true){
			$res=move_uploaded_file($_FILES['myfile']['tmp_name'], ARTAPATH_BASEDIR.'/tmp/uploadtest.tmp');
			if($res==true){
				$res=($_FILES['myfile']['size']==filesize(ARTAPATH_BASEDIR.'/tmp/uploadtest.tmp'));
			}
		}
		if(is_file(ARTAPATH_BASEDIR.'/tmp/uploadtest.tmp')){
			ArtaFile::Delete(ARTAPATH_BASEDIR.'/tmp/uploadtest.tmp');
		}
		if($res==false){
			redirect('index.php?pack=tools&view=diagnostic&task=display', 
			trans('UPERROR').(
				$_FILES['myfile']['error']==UPLOAD_ERR_OK?
					'':
					' ('.ArtaFilterinput::UploadErr($_FILES['myfile']['error']).')'
			), 'error');
		}else{
			redirect('index.php?pack=tools&view=diagnostic&task=display', trans('NO UPLOAD ERRORS FOUND'));
		}
		
	}
	
	function diag_ftp(){
		if(ArtaUsergroup::getPerm('can_use_diags', 'package', 'tools')==false){
			ArtaError::show(403);
		}
		$res=($_FILES['myfile']['error']==0);
		if($res==true){
			$res=move_uploaded_file($_FILES['myfile']['tmp_name'], ARTAPATH_BASEDIR.'/tmp/uploadtest.tmp');
			if($res==true){
				$res=($_FILES['myfile']['size']==filesize(ARTAPATH_BASEDIR.'/tmp/uploadtest.tmp'));
			}
		}
		if($res==false){
			redirect('index.php?pack=tools&view=diagnostic&task=display', trans('WEB UPLOAD ERROR'), 'error');
		}
		$res=ArtaFile::ftp_write(ARTAPATH_BASEDIR.'/tmp/ftptest.tmp',file_get_contents(ARTAPATH_BASEDIR.'/tmp/uploadtest.tmp'));
		if($res){
			$res=file_exists(ARTAPATH_BASEDIR.'/tmp/ftptest.tmp');
			if($res){
				$data=ArtaFile::ftp_read(ARTAPATH_BASEDIR.'/tmp/ftptest.tmp');
				$res=(strlen($data)==$_FILES['myfile']['size']);
			}
		}
		
		ArtaFile::Delete(ARTAPATH_BASEDIR.'/tmp/ftptest.tmp');
		ArtaFile::Delete(ARTAPATH_BASEDIR.'/tmp/uploadtest.tmp');
		
		if($res==false){
			redirect('index.php?pack=tools&view=diagnostic&task=display', trans('FTP ERROR'), 'error');
		}else{
			redirect('index.php?pack=tools&view=diagnostic&task=display', trans('NO FTP ERRORS FOUND'));
		}
		
	}

}
?>
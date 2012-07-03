<?php
if(!defined('ARTA_VALID')){die('No access');}
class LanguageViewMissing extends ArtaPackageView{
	
	function display(){
		$filename=getVar('fn','','','string');
		$filename=base64_decode($filename);
		$f = explode('|', $filename);
		if($f[0]=='site'){
			$pth=ARTAPATH_BASEDIR.'/languages/en-US/';
		}else{
			$pth=ARTAPATH_ADMIN.'/languages/en-US/';
		}
		$f[1]=ArtaFilterinput::clean($f[1], 'filename');
		if(!is_file($pth.$f[1])){
			ArtaError::show(404,$pth.$f[1]);
		}
		header('Content-disposition: attachment; filename='.$f[1]);
		readfile($pth.$f[1]);
	}

}
?>
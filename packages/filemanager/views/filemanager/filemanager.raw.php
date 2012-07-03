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
		header("Last-Modified: ".gmdate("D, d M Y H:i:s", 1235555658-(86400*365)) . ' GMT');
		header("Expires: ".gmdate("D, d M Y H:i:s", time()+(86400*2)) . ' GMT');
		header('Cache-Control: public');
		header('Pragma: cache');
		$f=getVar('img', false,'','string');
		if($f==false){
			header('Content-Type: image/png');
			$t=ArtaLoader::Template();
			echo file_get_contents(ARTAPATH_BASEDIR.'/imagesets/'.$t->getImgSetName().'/file.png');
			return true;
		}else{
			$f=base64_decode($f);
		}
		if(!file_exists(ARTAPATH_BASEDIR.'/content'.ArtaFile::replaceSlashes($f)) && !is_dir(ARTAPATH_BASEDIR.'/content/'.ArtaFile::replaceSlashes($f))){
			header('Content-Type: image/png');
			$t=ArtaLoader::Template();
			echo file_get_contents(ARTAPATH_BASEDIR.'/imagesets/'.$t->getImgSetName().'/file.png');
			return true;
		}
		$ext=strtolower(ArtaFile::getExt($f));
		switch($ext){
			case 'png':
				$img=imagecreatefrompng(ARTAPATH_BASEDIR.'/content/'.ArtaFile::replaceSlashes($f));
			break;
			case 'jpg':
			case 'jpeg':
				$img=imagecreatefromjpeg(ARTAPATH_BASEDIR.'/content/'.ArtaFile::replaceSlashes($f));
			break;
			case 'gif':
				$img=imagecreatefromgif(ARTAPATH_BASEDIR.'/content/'.ArtaFile::replaceSlashes($f));
			break;
			default:
				if(file_exists(ARTAPATH_BASEDIR.'/content/'.ArtaFile::replaceSlashes($f))&&!is_dir(ARTAPATH_BASEDIR.'/content/'.ArtaFile::replaceSlashes($f))){
					echo file_get_contents(ARTAPATH_BASEDIR.'/content/'.ArtaFile::replaceSlashes($f));
				}elseif(is_dir(ARTAPATH_BASEDIR.'/content/'.ArtaFile::replaceSlashes($f))){
					header('Content-Type: image/png');
					$t=ArtaLoader::Template();
					echo file_get_contents(ARTAPATH_BASEDIR.'/imagesets/'.$t->getImgSetName().'/folder.png');
				}else{
					header('Content-Type: image/png');
					$t=ArtaLoader::Template();
					echo file_get_contents(ARTAPATH_BASEDIR.'/imagesets/'.$t->getImgSetName().'/file.png');
				}
				return true;
			break;
		}
		$w=imagesx($img);
		$h=imagesy($img);
		$hh=$h*80/$w;

		header('Content-Type: image/jpeg');
		if($w>80||$h>80){
			$des=imagecreatetruecolor(80, $hh);
			imagecopyresampled($des, $img, 0,0,0,0,80, $hh,$w, $h);

			imagejpeg($des, "", 50);
		}else{
			imagejpeg($img, "", 50);
		}
	}
	
}

?>
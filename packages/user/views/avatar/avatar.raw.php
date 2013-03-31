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
		$GLOBALS['_DISABLE_POSITION_LOGGING']=true;
		$uid=getVar('uid', null, 'default', 'int');
		$big=getVar('big', 0, 'default', 'bool');
		$big= $big==true?'/big':'';
		header('Content-Type: image/jpeg');
		
		$u=ArtaLoader::User();
		$u=$u->getUser($uid);
		
		if(!is_null($u)){
			if((string)$u->avatar!=='' && file_exists(ARTAPATH_BASEDIR.'/media/avatars'.$big.'/'.$u->avatar)){
				
				$file=(ARTAPATH_BASEDIR.'/media/avatars'.$big.'/'.$u->avatar);
			}elseif((string)$u->avatar=='gravatar'){
				ArtaRequest::cacheByETag(md5($u->avatar), 300);
				redirect('http://www.gravatar.com/avatar/'.md5($u->email).'.jpg?size='.($big=='/big'?200:100).'&d='.urlencode(ArtaURL::getSiteURL().'media/avatars'.$big.'/unknown.jpg'));
			}else{
				$file=(ARTAPATH_BASEDIR.'/media/avatars'.$big.'/'.'unknown.jpg');
			}
		}else{
			$file=(ARTAPATH_BASEDIR.'/media/avatars'.$big.'/'.'unknown.jpg');
		}

		$md5 = @md5_file($file);
		if($md5) ArtaRequest::cacheByETag($md5,300);
			
		readfile($file);
	}
}

?>
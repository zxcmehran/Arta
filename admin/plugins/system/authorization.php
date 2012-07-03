<?php 
if(!defined('ARTA_VALID')){die('No access');}

function authorize(){
	global $artamain;
	$u = $artamain->user->getCurrentUser();

	if($u->id == 0 && $artamain->package->getPackage() !=='login' && $artamain->package->getPackage() !=='cphome'){
		ArtaError::show(401,null,'index.php?pack=login&redirect='.base64_encode(ArtaURL::getURL()),3);
	}elseif($u->id == 0 && $artamain->package->getPackage() !=='login' && $artamain->package->getPackage() =='cphome'){
		if($_SERVER['IS_HOMEPAGE']){
			redirect('index.php?pack=login');
		}else{
			redirect('index.php?pack=login&redirect='.base64_encode(ArtaURL::getURL()));
		}
	}

	if($u->id > 0 && ArtaUserGroup::getPerm('can_login_admin_side', 'plugin', 'authorization', 'admin', $u->usergroup) == false){
		ArtaError::show(401, trans('YOU CANNOT LOGIN ADMINSIDE'));
	}
}
?>
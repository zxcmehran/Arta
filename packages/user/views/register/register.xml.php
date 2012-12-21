<?php 
if(!defined('ARTA_VALID')){die('No access');}
class UserViewRegister extends ArtaPackageView{

	function display(){
		$this->setLayout('checker');
		$subject = getVar('subject', false, '', 'string');
		$data = getvar('data', false, '', 'string');
		if($subject && $data){
			$subject=@base64_decode($subject);
			$valid=array('username','password','email');
			if(!in_array($subject, $valid)){
				die();
			}
			$data=@base64_decode($data);
			eval('$res = $this->check_'.$subject.'($data);');
			$this->assign('res', $res);
		}
		$this->render();
	}

	function check_username($data){
		$userlength = $this->getSetting('username_min_length', 6);
		if(ArtaUTF8::strlen($data) < $userlength){
			return sprintf(trans('USERNAME MUST BE AT LEAST _ CHARS'), $userlength);
		}
		$uc=ArtaLoader::User();
		$username=$uc->getUser($data , 'username');
		if(isset($username->id) && is_numeric($username->id) && $username->id > 0){
			return trans("USERNAME_EXISTS");
		}
		$denied = $this->getSetting('denied_username', array());
		if(!in_array(' ',$denied)){
			$denied[]=' ';
		}
		foreach($denied as $k=>$v){
			if(is_numeric(strpos($data, $v))==true){
				return sprintf(trans('DO NOT USE SPACE IN UNAME'), htmlspecialchars($data), htmlspecialchars($v));
			}
		}

		return 'true';
	}
	function check_email($data){
		if(!ArtaFilterinput::isEmail($data)){
			return trans('INVALID MAIL');
		}
		$uc=ArtaLoader::User();
		$username=$uc->getUser($data , 'email');
		if(isset($username->id) && is_numeric($username->id) && $username->id > 0){
			return trans("EMAIL_EXISTS");
		}
		return 'true';
	}
	function check_password($data){
		$userlength = $this->getSetting('password_min_length', 6);
		if($data < $userlength){
			return sprintf(trans('PASSWORD MUST BE AT LEAST _ CHARS'), $userlength);
		}
		return 'true';
	}
}
?>
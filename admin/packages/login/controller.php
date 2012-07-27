<?php 
if(!defined('ARTA_VALID')){die('No access');}
class LoginController extends ArtaPackageController{

	function display(){
		$module=ArtaLoader::Module();
		$module->enabled=false;
		$view = $this->getView('login');
		$view->display();
	}

	function login(){
		
		$user = ArtaLoader::User();
		$user = $user->getCurrentUser();
		if($user->id > 0 ){
			redirect('index.php', trans('YOU ARE ALREADY LOGGED IN'), 'warning');
		}
		
		ArtaLoader::Import('user->helper');
		$vars =ArtaRequest::getVars('post', 'object');

		ArtaUtility::denyBruteForce('login_admin', '', 'loginMsg1');
		
		
		if($vars->username == false || $vars->password == false || $vars->token == false){
			redirect('index.php?pack=login', trans("FORM ISNT COMPLETE"), 'warning');
		}

		if($vars->token !== ArtaSession::genToken()){
			redirect('index.php?pack=login', trans("INVALID TOKEN"), 'warning');
		}

		if(@$vars->remember == true){
			$config =ArtaLoader::Config();
			setcookie('arta_uname', $vars->username, time()+'604800', $config->cookie_path, $config->cookie_domain);	
		}
		$res = ArtaUserHelper::Login($vars->username, $vars->password);
		$redirect= $vars->redirect ? base64_decode($vars->redirect) : 'index.php';

		if($res == 'COMPLETE'){
                        if(substr($redirect, 0, 9)=='index.php'){
                            if(trim((string)$vars->f_language)){
                                ArtaRequest::addVar('language', (string)$vars->f_language);
                                $language = ArtaLoader::Language();
                                $language->addtoNeed('login','package',ARTAPATH_CLIENTDIR, (string)$vars->f_language);
                            }else{
                                $redirect='#'.$redirect;
                            }
                            $redirect = makeURL($redirect);
                            $made = true;
                        }
			if(isset($vars->unsecure)&&(substr($redirect,0,6)=='index.' || isset($made))){
				$redirect=ArtaURL::getURL(array('protocol'=>'http://', 'path'=>'','path_info'=>'', 'query'=>'')).(isset($made)?$redirect:makeURL($redirect));
			}
			redirect($redirect, trans('YOU LOGGED IN SUCC'));
		}else{
			ArtaUtility::addBruteForce('login_admin', 1, 5, 600);
			redirect('index.php?pack=login', trans('INCORRECT UNAME OR PASS'), 'error');
		}
	}

	function logout(){
		$user = ArtaLoader::User();
		$user = $user->getCurrentUser();
		if($user->id <= 0 ){
			redirect('index.php');	
		}
		ArtaLoader::Import('user->helper');
		if(ArtaUserHelper::Logout(session_id())){
			redirect('index.php');
		}else{
			redirect('index.php', trans('ERROR IN LOGGING OUT'), 'error');
		}
	}
	
}
function loginMsg1($time, $timeout){
		redirect('index.php?pack=login', sprintf(trans("LOGINMISS_EXPIRED"),(int)(($time+$timeout-time())/60)+1), 'warning');
}
?>
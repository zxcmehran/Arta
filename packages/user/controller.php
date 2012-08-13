<?php 
if(!defined('ARTA_VALID')){die('No access');}
class UserController extends ArtaPackageController{

	function display(){
		$viewname = getVar('view', 'login', '', 'string');
		$db = ArtaLoader::DB();
		if($viewname == 'register' || $viewname == 'captcha' || $viewname == 'avatar' || $viewname == 'notes'){
			$type=getVar('type', 'html', '', 'string');
		}else{$type='html';}
		$view = $this->getView($viewname,$type);
		$view->display();
	}
	
	
	function del_oi(){
		if($this->getSetting('allow_openid_usage', true)!=true){
			ArtaError::show(400);
		}
		$u=$this->getCurrentUser();
		if(ArtaSession::checkToken()==false || $u->id==0){
			ArtaError::show(400);
		}
		$db=ArtaLoader::DB();
		if(@$u->password{0}=='_'){
			$db->setQuery('SELECT COUNT(*) FROM #__openid_map WHERE `userid`='.$u->id);
			$r=$db->loadResult();
			if(@(int)$r<=1){
				redirect('index.php?pack=user&view=openid', trans('ITS YOUR ONLY WAY'), 'warning');
			}
		}
		$id=getVar('id', 'false');
		if($id<1){
			ArtaError::show(400);
		}
		$db->setQuery('DELETE FROM #__openid_map WHERE `id`='.$id);
		if($db->query()){
			redirect('index.php?pack=user&view=openid', trans('DELETED SUCC'));
		}else{
			redirect('index.php?pack=user&view=openid', trans('ERROR IN DB'), 'error');
		}
	}
	
	function save_oi(){
		if($this->getSetting('allow_openid_usage', true)!=true){
			ArtaError::show(400);
		}
		$u=$this->getCurrentUser();
		if(ArtaSession::checkToken()==false || $u->id==0){
			ArtaError::show(400);
		}
		$this->OpenIDLogin(getVar('user_openid', false), true);
	}
	
	
	
	function finish_openid_adding(){
		if($this->getSetting('allow_openid_usage', true)!=true){
			ArtaError::show(400);
		}
		$_old_ipath=ini_get('include_path');
		ini_set('include_path', ARTAPATH_LIBRARY.'/openid');
		define('Auth_OpenID_RAND_SOURCE',null);
		
		ArtaLoader::Import('openid->Auth->OpenID->Consumer');
		ArtaLoader::Import('openid->Auth->OpenID->FileStore');
		ArtaLoader::Import('openid->Auth->OpenID->SReg');
		ArtaLoader::Import('openid->store');
		
	    $consumer = new Auth_OpenID_Consumer(new Auth_OpenID_ArtaStore);
		$return_to=ArtaURL::getSiteURL().'index.php?pack=user&task=finish_openid_adding';
	     
	 /*   var_dump($return_to);
	    die();*/
	    $response = $consumer->complete($return_to);
		
	    if ($response->status == Auth_OpenID_CANCEL) {
	    	redirect('index.php?pack=user&view=openid', trans('VERIFICATION CANCELLED'), 'warning');
	    } else if ($response->status == Auth_OpenID_FAILURE) {
	    	redirect('index.php?pack=user&view=openid', trans('AUTH FAILED').': '.$response->message, 'warning');
	    } else if ($response->status == Auth_OpenID_SUCCESS) {
	        $openid = $response->getDisplayIdentifier();
	        $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
	        $sreg = $sreg_resp->contents();
	        $u=$this->getCurrentUser();
	        $db=ArtaLoader::DB();
	        $db->setQuery('SELECT server_url FROM #__openid_map WHERE `userid`='.$u->id.' AND `server_url`='.$db->Quote($openid));
	        $r = $db->loadResult();
	        if($r!=null){
	        	redirect('index.php?pack=user&view=openid', trans('OPENID IN USE'), 'warning');
	        }
	        $db->setQuery('INSERT INTO #__openid_map VALUES(NULL, '.$u->id.', '.$db->Quote($openid).')');
	        if($db->query()){
	        	redirect('index.php?pack=user&view=openid', trans('SAVED SUCC'));
	        }else{
	        	redirect('index.php?pack=user&view=openid', trans('ERROR IN DB'));
	        }
	    }
	    ini_set('include_path', $_old_ipath);
	}
	
	
	private function OpenIDLogin($vars, $is_adding=false){
		if($this->getSetting('allow_openid_usage', true)!=true){
			ArtaError::show(400);
		}
		if($is_adding){
			$openid=$vars;
			$vars=new stdClass;
		}else{
			$openid=$vars->openid_box;
		}
		$_old_ipath=ini_get('include_path');
		ini_set('include_path', ARTAPATH_LIBRARY.'/openid');
		define('Auth_OpenID_RAND_SOURCE',null);
		
		ArtaLoader::Import('openid->Auth->OpenID->Consumer');
		ArtaLoader::Import('openid->Auth->OpenID->FileStore');
		ArtaLoader::Import('openid->Auth->OpenID->SReg');
		ArtaLoader::Import('openid->store');
		
	    $consumer = new Auth_OpenID_Consumer(new Auth_OpenID_ArtaStore);
	    
	    $auth_request = $consumer->begin($openid);
	
		if(!isset($vars->redirect)){
			$vars->redirect = 'index.php';
			if($is_adding){
				$vars->redirect = 'index.php?pack=user&view=openid';
			}
		}else{
			$vars->redirect = base64_decode($vars->redirect);
		}
	
	    if (!$auth_request) {
	    	redirect($vars->redirect, trans('INVALID OPENID'), 'error');
	    }

	    $sreg_request = Auth_OpenID_SRegRequest::build(
	                                     // Required
	                                     array('nickname','fullname', 'email'));
	
	    if ($sreg_request) {
	        $auth_request->addExtension($sreg_request);
	    }
		
		$redirect_to=ArtaURL::getSiteURL().'index.php?pack=user&task=finish_openid';
		if($is_adding){
			$redirect_to=ArtaURL::getSiteURL().'index.php?pack=user&task=finish_openid_adding';
		}
		if($vars->redirect!='index.php' && $vars->redirect!='index.php?pack=user&view=openid'){
			$redirect_to.='&redirect='.urlencode(base64_encode($vars->redirect));
		}

	    if ($auth_request->shouldSendRedirect()) {
	        $redirect_url = $auth_request->redirectURL(
	        	ArtaURL::getSiteURL(),
				$redirect_to
			);

	        if (Auth_OpenID::isFailure($redirect_url)) {
	        	redirect($vars->redirect, trans('COULDNT REDIRECT TO OPENID').': '.
										$redirect_url->message, 'error');
	        } else {
	            header("Location: ".$redirect_url);
	        }
	    } else {
	        $form_id = 'openid_message';
	        $form_html = $auth_request->htmlMarkup(
			ArtaURL::getSiteURL(),
			$redirect_to,
	        false, array('id' => $form_id));

	        if (Auth_OpenID::isFailure($form_html)) {
	        	redirect($vars->redirect, trans('COULDNT REDIRECT TO OPENID').': '.
										$form_html->message, 'error');
	        } else {
	            print $form_html;
	        }
	    }
	    ini_set('include_path', $_old_ipath);
	    die();
	}
	
	function finish_openid(){
		if($this->getSetting('allow_openid_usage', true)!=true){
			ArtaError::show(400);
		}
		$_old_ipath=ini_get('include_path');
		ini_set('include_path', ARTAPATH_LIBRARY.'/openid');
		define('Auth_OpenID_RAND_SOURCE',null);
		
		ArtaLoader::Import('openid->Auth->OpenID->Consumer');
		ArtaLoader::Import('openid->Auth->OpenID->FileStore');
		ArtaLoader::Import('openid->Auth->OpenID->SReg');
		ArtaLoader::Import('openid->store');
		
	    $consumer = new Auth_OpenID_Consumer(new Auth_OpenID_ArtaStore);
		$vars=ArtaRequest::getVars('', 'object');
		if(!isset($vars->redirect)){
			$vars->redirect = 'index.php';
		}else{
			$vars->redirect = base64_decode($vars->redirect);
		}

		$redirect_to=ArtaURL::getSiteURL().'index.php?pack=user&task=finish_openid';
		if($vars->redirect!='index.php'){
			$redirect_to.='&redirect='.urlencode(base64_encode($vars->redirect));
		}
	    $return_to = $redirect_to;
	 /*   var_dump($return_to);
	    die();*/
	    $response = $consumer->complete($return_to);
	
	    if ($response->status == Auth_OpenID_CANCEL) {
	    	redirect($vars->redirect, trans('VERIFICATION CANCELLED'), 'warning');
	    } else if ($response->status == Auth_OpenID_FAILURE) {
	    	redirect($vars->redirect, trans('AUTH FAILED').': '.$response->message, 'warning');
	    } else if ($response->status == Auth_OpenID_SUCCESS) {
	        $openid = $response->getDisplayIdentifier();
	        $sreg_resp = Auth_OpenID_SRegResponse::fromSuccessResponse($response);
	        $sreg = $sreg_resp->contents();
	        
	        $u=ArtaLoader::User();
	        $exists=$u->getUser($sreg['nickname'], 'username', false);
	        ArtaLoader::Import('user->helper');
	        
	        if($exists!=false){
	        	$res=ArtaUserHelper::Login($exists->username, $openid, true);
	        	
	        	if($res == 'COMPLETE'){
					if($vars->redirect==('index.php?pack=pages&view=environment')){
						// friendship with pages package!
						$m='';
					}else{
						$m=trans('YOU LOGGED IN SUCC');
					}
					redirect($vars->redirect, $m);
				}elseif($res == 'NOT_ACTIVE'){
					redirect($vars->redirect, trans('YOU ARE NOT ACTIVE'));
				}elseif($res == 'INVALID_OPENID'){
					redirect($vars->redirect, trans('INVALID OPENID FOR USER'), 'error');
				}elseif($res == 'BANNED'){
					redirect($vars->redirect, trans('YOU ARE BANNED').ArtaUserHelper::getBanReason($exists->username, 'username'));
				}else{
					redirect('index.php?pack=user&view=login&redirect='.base64_encode($vars->redirect), $res, 'error');
				}
	        	
	        }else{
	        	// Register user.
	        	$ug = $this->getSetting('default_ug', 1);
	        	$res=ArtaUserHelper::Register($sreg['fullname'], $sreg['nickname'], $sreg['email'], $openid, $ug, null, true);
	        	
	        	if($res == 'INCOMPLETE'){
					redirect('index.php?pack=user&view=register', trans("INCOMPLETE DATA RECIEVED FROM SERVER"), 'warning');
				}elseif($res == 'USERNAME_EXISTS'){
					redirect('index.php?pack=user&view=register', trans("USERNAME_EXISTS_OPENID"), 'error');
				}elseif($res == 'EMAIL_EXISTS'){
					redirect('index.php?pack=user&view=register', trans("EMAIL_EXISTS_OPENID"), 'error');
				}elseif($res == 'ERROR'){
					redirect('index.php?pack=user&view=register', trans("REGISTER_ERROR"), 'error');
				}elseif($res == 'COMPLETE'){
					$res=ArtaUserHelper::Login($sreg['nickname'], $openid, true);
					redirect($vars->redirect, trans("REGISTER_COMPLETE_OPENID_LOGGED_IN"));	
				}else{
					redirect('index.php?pack=user&view=register', $res, 'error');
				}	        	
	        	
	        }
	    }
	    ini_set('include_path', $_old_ipath);
	}
	
	

	function login(){
		$user = ArtaLoader::User();
		$user = $user->getCurrentUser();
		if($user->id > 0 ){
			redirect('index.php?pack=user&view=logout', trans('YOU ARE ALREADY LOGGED IN'), 'warning');
		}
		ArtaLoader::Import('user->helper');
		$vars =ArtaRequest::getVars('post', 'object');
		if($vars->openid_box!=''){
			if($this->getSetting('allow_openid_usage', true)==true){
				return $this->OpenIDLogin($vars);
			}
		}
		if($vars->password_box!==''&&$vars->password==''){
			$vars->password=md5($vars->password_box);
		}

		if(isset($vars->remember) == false){
			$vars->remember=0;
		}

		$times = $this->getSetting('loginmiss_times', 4);
		$time = $this->getSetting('loginmiss_timeout', 15);
		
		ArtaUtility::denyBruteForce('login_site', '', 'loginMsg1');

		if($vars->username == false || $vars->password == false || $vars->token == false){
			redirect('index.php?pack=user&view=login', trans("FORM ISNT COMPLETE"), 'warning');
		}

		if($vars->token !== ArtaSession::genToken()){
			redirect('index.php?pack=user&view=login', trans("INVALID TOKEN"), 'warning');
		}

		if(!isset($vars->redirect)){
			$vars->redirect = 'index.php';
		}else{
			$vars->redirect = base64_decode($vars->redirect);
		}

		if(isset($vars->remember) && $vars->remember==true){
			$config =ArtaLoader::Config();
			setcookie('arta_uname', $vars->username, time()+604800, $config->cookie_path, $config->cookie_domain);
		}

		$res = ArtaUserHelper::Login($vars->username, $vars->password);

		if($res !== 'COMPLETE'){
			ArtaUtility::addBruteForce('login_site', 1, $times, $time*60);
		}

		if($res == 'COMPLETE'){
			if(isset($vars->unsecure)&&substr($vars->redirect,0,6)=='index.'){
				$vars->redirect=ArtaURL::getURL(array('protocol'=>'http://', 'path'=>'','path_info'=>'', 'query'=>'')).makeURL($vars->redirect);
			}
			if($vars->redirect==('index.php?pack=pages&view=environment')){
				// friendship with pages package!
				$m='';
			}else{
				$m=trans('YOU LOGGED IN SUCC');
			}
			redirect($vars->redirect, $m);
		}elseif($res == 'NOT_ACTIVE'){
			redirect($vars->redirect, trans('YOU ARE NOT ACTIVE'));
		}elseif($res == 'BANNED'){
			redirect($vars->redirect, trans('YOU ARE BANNED').ArtaUserHelper::getBanReason($vars->username, 'username'));
		}elseif($res == 'ERROR_LOADING'){
			redirect('index.php?pack=user&view=login&redirect='.base64_encode($vars->redirect), sprintf(trans('INCORRECT UNAME OR PASS'),(int)@++$_SESSION['LOGIN_TRYOUTS'], $times), 'error');
		}else{
			redirect('index.php?pack=user&view=login&redirect='.base64_encode($vars->redirect), $res, 'error');
		}
	}

	function logout(){
		$vars = ArtaRequest::getVars('post', 'object');
		$user = ArtaLoader::User();
		$user = $user->getCurrentUser();
		if($user->id <= 0 ){
			redirect('index.php?pack=user&view=login', trans('YOU ARE ALREADY LOGGED OUT'), 'warning');	
		}
		if(!isset($vars->redirect)){
			$vars->redirect = 'index.php';
		}else{
			$vars->redirect = base64_decode($vars->redirect);
		}
		ArtaLoader::Import('user->helper');
		if(ArtaUserHelper::Logout(session_id())){
			redirect('index.php?pack=user&task=logout_redirect&redirect='.base64_encode($vars->redirect));
			
		}else{
			redirect($vars->redirect, trans('ERROR IN LOGGING OUT'), 'error');
		}
	}

	function logout_redirect(){
		$redirect = getVar('redirect', false, '', 'string');
		if($redirect == false){
			$redirect = 'index.php';
		}else{
			$redirect = base64_decode($redirect);
		}
		redirect($redirect, trans('YOU HAVE LOGGED OUT'));
		
	}

	function register(){
		ArtaLoader::Import('#user->helper');
		ArtaLoader::Import('#misc->captcha');
		$enabled = $this->getSetting('allow_registering', 1);
		if($enabled===false){
			ArtaError::show(500, trans('REGISTERING IS DISABLED'));
		}
		
		$vars =ArtaRequest::getVars('post', 'object');

		if(!isset($vars->token) || $vars->token !== ArtaSession::genToken()){
			redirect('index.php?pack=user&view=register', trans("INVALID TOKEN"), 'warning');
		}

		//setting redirect
		if(!isset($vars->redirect)){
			$vars->redirect = 'index.php';
		}else{
			$vars->redirect = base64_decode($vars->redirect);
		}

		//gathering information
		$userlength = $this->getSetting('username_min_length', 6);
		$passlength = $this->getSetting('password_min_length', 6);
		$ug = $this->getSetting('default_ug', 1);
		$denied = $this->getSetting('denied_username', array());
		
		if(ArtaUtility::keysExists(array(
		'name',
		'username',
		'email',
		'email_verify',
		'password',
		'password_verify',
		'misc',
		'captcha'			
		),$vars)==false){
			redirect('index.php?pack=user&view=register', trans("FORM ISNT COMPLETE"), 'warning');
		}
		
		$vars=ArtaFilterinput::trim($vars);
		foreach(array(
		'name'=>255,
		'username'=>255,
		'email'=>255
		) as $k=>$v){
			if(strlen($vars->$k)>$v){
				ArtaError::show(500, ('too long input found'));
			}
		}
		
		$vars=ArtaUtility::array_extend($vars, 
		array(
			'rules'=>0,
			'misc'=>array()
		)
		);
		
		$vars=ArtaFilterinput::clean($vars, array(
		'name'=>'string',
		'username'=>'string',
		'email'=>'string',
		'email_verify'=>'string',
		'password'=>'string',
		'password_verify'=>'string',
		'misc'=>'array'
		));
		
		
		$db = ArtaLoader::DB();
		$l=ArtaLoader::Language();
		
		$db->setQuery('SELECT * FROM #__userfields WHERE fieldtype=\'misc\'');
		$uf=ArtaUtility::keyByChild((array)$db->loadObjectList(), 'var');
		
		$misc = new stdClass;
		foreach($vars->misc as $kk=>$vv){
			$row=@$uf[$kk];
			if(isset($uf[$kk]) && $uf[$kk]->check!=''){
				$l->addtoNeed($row->extname, $row->extype);
				$value=$vv;
				$error=null;
				$scope="register";
				eval(@$row->check);
				if($error!==null){
					redirect('index.php?pack=user&view=register',$error, 'warning');
				}
				$vv=$value;
			}
			if($row && $vv!=unserialize($row->default)){
				$misc->$kk=$vv;
			}
			
		}
		$misc=serialize($misc);
		
		
		if(ArtaCaptcha::verifyCode($vars->captcha,'user_register')==false){
			redirect('index.php?pack=user&view=register', trans("INVALID CAPTCHA"), 'warning');
		}

		
		
		if(is_int(strpos($vars->username, ' '))){
			redirect('index.php?pack=user&view=register',trans('DO NOT USE SPACE IN UNAME'), 'warning');
		}
		//check length
		if(ArtaUTF8::strlen($vars->username) < $userlength){
			redirect('index.php?pack=user&view=register', sprintf(trans('USERNAME MUST BE AT LEAST _ CHARS'), $userlength), 'warning');
		}
		if(ArtaUTF8::strlen($vars->username) > 20){
			redirect('index.php?pack=user&view=register', sprintf(trans('USERNAME MUST BE AT LAST 20 CHARS'), $userlength), 'warning');
		}
		foreach($denied as $k=>$v){
			if(is_numeric(strpos($vars->username, $v))==true){
				redirect('index.php?pack=user&view=register', sprintf(trans('ILLEGAL USERNAME'), htmlspecialchars($vars->username), htmlspecialchars($v)));
			}
		}
		
		
		
		$thislength=substr($vars->password_verify, 32);
		$vars->password_verify=substr($vars->password_verify, 0, 32);

		if($thislength < $passlength){
			redirect('index.php?pack=user&view=register', sprintf(trans('PASSWORD MUST BE AT LEAST _ CHARS'), $passlength), 'warning');
		}
		
			
		// check verifies
		if($vars->password !== $vars->password_verify){
			redirect('index.php?pack=user&view=register', trans('PASSWORD VERIFY FAILED'), 'warning');
		}
		if($vars->email !== $vars->email_verify || strlen((string)$vars->email) == 0){
			redirect('index.php?pack=user&view=register', trans('EMAIL VERIFY FAILED'), 'warning');
		}

		if($vars->rules == 0){
			redirect('index.php?pack=user&view=register', trans('YOU MUST ACCEPT RULES'), 'warning');
		}
		
		//lets register
		$res = ArtaUserHelper::Register($vars->name, $vars->username, $vars->email, $vars->password, $ug, $misc);
		
		//analyze results
		if($res == 'INCOMPLETE'){
			redirect('index.php?pack=user&view=register', trans("FORM ISNT COMPLETE"), 'warning');
		}elseif($res == 'USERNAME_EXISTS'){
			redirect('index.php?pack=user&view=register', trans("USERNAME_EXISTS"), 'error');
		}elseif($res == 'EMAIL_EXISTS'){
			redirect('index.php?pack=user&view=register', trans("EMAIL_EXISTS"), 'error');
		}elseif($res == 'ERROR'){
			redirect('index.php?pack=user&view=register', trans("REGISTER_ERROR"), 'error');
		}elseif($res == 'COMPLETE'){
				
				$mode = (int)$this->getSetting('activation_mode', 0);
				switch($mode){
					case 0: 
						$db->setQuery("UPDATE #__users SET activation='0' WHERE LOWER(`username`)=".$db->Quote(ArtaUTF8::strtolower($vars->username)));
						$db->query();
						$config = ArtaLoader::Config();
						$mail = ArtaLoader::Mail();
						$mail->mail($vars->email, 
						sprintf(trans('REGISTER_AT_'), $config->site_name), 
						sprintf(trans('MAIL_WELCOME_ACTIVE'), 
							htmlspecialchars($vars->username), 
							htmlspecialchars($config->site_name),
							htmlspecialchars(ArtaURL::getSiteURL())
						));
					break;
					case 1:
						$db->setQuery("SELECT * FROM #__users WHERE LOWER(`username`)=".$db->Quote(ArtaUTF8::strtolower($vars->username)));
						$data = $db->loadObject();
						$mail = ArtaLoader::Mail();
						$config = ArtaLoader::Config();
						$link = ArtaURL::getSiteURL().'index.php?pack=user&activation_code='.urlencode(base64_encode($data->activation)).'&task=activate';
						$res2 = $mail->mail($data->email, sprintf(trans('ACTIVATION AT_'), $config->site_name), sprintf(trans('ACTIVATION_MAIL'), 
						htmlspecialchars($config->site_name), 
						htmlspecialchars($vars->username), 
						htmlspecialchars($link)
						));
						if($res2 === false){
							ArtaApplication::enqueueMessage(trans('AN ERROR WHILE MAILING'), 'warning');
						}
					break;
					case 2:
						$db->setQuery("UPDATE #__users SET activation='MODERATOR' WHERE LOWER(`username`)=".$db->Quote(ArtaUTF8::strtolower($vars->username)));
						$db->query();
						$config = ArtaLoader::Config();
						$mail = ArtaLoader::Mail();
						$mail->mail($vars->email, sprintf(trans('REGISTER_AT_'), $config->site_name), sprintf(trans('MAIL_WELCOME_MODERATOR'), 
							htmlspecialchars($vars->username), 
							htmlspecialchars($config->site_name), 
							htmlspecialchars(ArtaURL::getSiteURL())
						));
					break;
				}
				redirect($vars->redirect, trans("REGISTER COMPLETED").' '.trans('ACTIVATION'.$mode));
		}else{
			redirect('index.php?pack=user&view=register', $res, 'error');
		}
		
		
	}

	function activate(){
		ArtaLoader::Import('user->helper');
		$hash=getVar('activation_code', '', '', 'string');
		if($hash == ''){
			redirect('index.php', trans('INVALID ACTIVATION CODE'));
		}
		$hash=base64_decode($hash);
		if(ArtaUserHelper::Activate($hash)){
			redirect('index.php?pack=user&view=login', trans('YOUR ACCOUNT ACTIVATED'));
		}else{
			redirect('index.php', trans('INVALID ACTIVATION CODE'));
		}
	}

	function gen_remember(){
		$mail = getVar('email', false, '', 'string');
		$token = getVar('token', false, '', 'string');
		$captcha= getVar('remind', false, '', 'string');
		
		if(ArtaCaptcha::verifyCode($captcha,'user_remind')==false){
			redirect('index.php?pack=user&view=remind', trans("INVALID CAPTCHA"), 'warning');
		}
		
		if(!isset($token) || $token !== ArtaSession::genToken()){
			redirect('index.php?pack=user&view=remind', trans("INVALID TOKEN"), 'warning');
		}
		if($mail==false){
			redirect('index.php?pack=user&view=remind', trans("FORM ISNT COMPLETE"), 'warning');
		}
		$uc=ArtaLoader::User();
		$user = $uc->getUser($mail, 'email');
		if(isset($user->email) == false || $user->email !== $mail){
			redirect('index.php?pack=user&view=remind', trans("INVALID MAIL"), 'warning');
		}
		$config=ArtaLoader::Config();
		$link = ArtaURL::getSiteURL().'index.php?pack=user&view=reset&reset_code='.urlencode(md5(base64_encode(md5($user->register_date.$config->secret.$user->lastvisit_date.md5($user->password))))).'&uid='. $user->id;
		$mail = ArtaLoader::Mail();
		$res = $mail->mail($user->email, sprintf(trans('REMIND AT_'),$config->site_name), sprintf(trans('REMIND_MAIL'), $user->username, $link));
		if($res === false){
			redirect('index.php?pack=user&view=remind', trans('AN ERROR WHILE MAILING'), 'ERROR');
		}else{
			redirect('index.php', trans('REMEMBER MAIL SENT'));
		}
	}

	function reset(){
		$vars=ArtaRequest::getVars('post', 'object');

		if(!isset($vars->uid) || !isset($vars->reset_code)){
			redirect('index.php?pack=user&view=remind');
		}
		
		if(!isset($vars->token) || $vars->token !== ArtaSession::genToken()){
			redirect('index.php?pack=user&view=reset&uid='.$vars->uid.'&reset_code='.$vars->reset_code, trans("INVALID TOKEN"), 'warning');
		}		
		$uc=ArtaLoader::User();
		$user = $uc->getUser($vars->uid);
		if(@$user->id !== $vars->uid){
			ArtaError::show(500, trans('INVALID USERID'));
		}
		
		$config=ArtaLoader::Config();
		if($vars->reset_code !== md5(base64_encode(md5($user->register_date.$config->secret.$user->lastvisit_date.md5($user->password))))){
			redirect('index.php?pack=user&view=remind', trans('INVALID RESET CODE'), 'error');
		}
		$passlength = $this->getSetting('password_min_length', 6);
		
		$thislength=substr($vars->verify_password, 32);
		$vars->verify_password=substr($vars->verify_password, 0, 32);

		if($thislength < $passlength){
			redirect('index.php?pack=user&view=reset&uid='.$vars->uid.'&reset_code='.$vars->reset_code, sprintf(trans('PASSWORD MUST BE AT LEAST _ CHARS'), $passlength), 'warning');
		}

		// check verifies
		if($vars->password !== $vars->verify_password){
			redirect('index.php?pack=user&view=reset&uid='.$vars->uid.'&reset_code='.$vars->reset_code, sprintf(trans('PASSWORD VERIFY FAILED'), $passlength), 'warning');
		}
		ArtaLoader::Import('user->helper');
		if(ArtaUserHelper::resetPassword($vars->uid, $vars->password) !== false){
			redirect('index.php?pack=user&view=login', trans('RESET COMPLETE'));
		}else{
			redirect('index.php?pack=user&view=reset&uid='.$vars->uid.'&reset_code='.$vars->reset_code, trans("ERROR IN DB"), 'error');
		}
	}
	
	private function runEval(&$row, &$u, &$value, &$error, &$scope){
		eval($row->check);
	}
	
	function edit(){
		$u=$this->getCurrentUser();
		$vars=ArtaRequest::getVars('post');
		$vars=ArtaUtility::array_extend($vars, array('name'=>$u->name, 'settings'=>$u->settings, 'misc'=>$u->misc));
		
		if($u->id<=0){
			redirect('index.php?pack=user&view=login&redirect='.base64_encode('index.php?pack=user&view=edit'), trans('YOU ARE NOT LOGGED IN'), 'warning');
		}

		if((int)$u->id!==getVar('uid', null, 'post', 'int')){
			redirect('index.php?pack=user&view=edit');
		}else{
			$suf='';
			$db=ArtaLoader::DB();
			// if password or email changed;check for current email...
			if($vars['_password_current']!=='' && $vars['password_current']==''){
				$vars['password_current']=md5($vars['_password_current']);
			}			
			if(ArtaString::hash($vars['password_current'], 'artahash-no-md5')!==$u->password && $u->password{0}!='_' && (strtolower($vars['email'])!==$u->email || $vars['password']!=='')){
				redirect('index.php?pack=user&view=edit', trans('INVALID CURRENT PASS'), 'error');
			}
			
			//if email changed...
			if(strtolower($vars['email'])!==strtolower($u->email)){
				//verification
				if(strtolower($vars['email'])!==strtolower($vars['email_verify'])){
					redirect('index.php?pack=user&view=edit', trans('EMAIL VERIFY FAILED'), 'error');
				}
				//existence
				$uc=ArtaLoader::User();
				$test=$uc->getUser($vars['email'], 'email');
				if($test!==null){
					redirect('index.php?pack=user&view=edit', trans('EMAIL EXISTS EDITING'), 'error');
				}
				$suf.=', email='.$db->Quote($vars['email']);
			}
			
			
			if($vars['_password']!=='' && $vars['password']=='' && $vars['_password_verify']!=='' && $vars['password_verify']==''){
				$vars['password_verify']=md5($vars['_password_verify']).strlen($vars['_password']);
				$vars['password']=md5($vars['_password']);
			}

			// if password changed...
			if($vars['password']!=='' && $vars['password_verify']!=='' && ArtaString::hash($vars['password'], 'artahash-no-md5')!==$u->password){
				$len=substr($vars['password_verify'], 32);
				
				//verification
				$vars['password_verify']=substr($vars['password_verify'], 0, 32);
				if($vars['password']!==$vars['password_verify']){
					redirect('index.php?pack=user&view=edit', trans('PASSWORD VERIFY FAILED'), 'error');
				}
				$minpasslen=$this->getSetting('password_min_length', 6);
				//length
				if($len<$minpasslen){
					redirect('index.php?pack=user&view=edit', sprintf(trans('PASSWORD MUST BE AT LEAST _ CHARS'), $minpasslen), 'error');
				}
				
				$suf.=', password='.$db->Quote(ArtaString::hash($vars['password'], 'artahash-no-md5'));
				$newPass=ArtaString::hash($vars['password'], 'artahash-no-md5');
			}
			
			// settings and misc
			if(is_array($vars['misc'])){
				$vars['misc']=ArtaUtility::array2object($vars['misc']);
			}else{
				$vars['misc']=new stdClass;
			}
			if(is_array($vars['settings'])){
				$vars['settings']=ArtaUtility::array2object($vars['settings']);
			}else{
				$vars['settings']=new stdClass;
			}
			$l=ArtaLoader::Language();
			$db->setQuery('SELECT * FROM #__userfields WHERE fieldtype=\'setting\'');
			$set=ArtaUtility::keyByChild($db->loadObjectList(), 'var');
			$conf=ArtaLoader::Config();
			if(unserialize($set['cal_type']->default)==null){
				$set['cal_type']->default=serialize($conf->cal_type);
			}
			if(unserialize($set['time_offset']->default)==null){
				$set['time_offset']->default=serialize($conf->time_offset);
			}
			foreach($vars['settings'] as $k=>$v){
				$row=@$set[$k];
				if(isset($set[$k]) && $set[$k]->check!=''){
					$l->addtoNeed($row->extname, $row->extype);
					if($row->vartype=='bool'){
						$v=@(bool)$v;
					}
					$value=$v;
					$error=null;
					$scope="useredit";
					//eval(@$row->check);
					$this->runEval($row, $u, $value, $error, $scope);
					if($error!==null){
						redirect('index.php?pack=user&view=edit',$error, 'error');
					}
					$v=$value;
					$vars['settings']->$k=$value;
					
				}
				if($row==false || $v==unserialize($row->default)){
					unset($vars['settings']->$k);
				}
			}
			$db->setQuery('SELECT * FROM #__userfields WHERE fieldtype=\'misc\'');
			$set=ArtaUtility::keyByChild($db->loadObjectList(), 'var');
			foreach($vars['misc'] as $k=>$v){
				$row=@$set[$k];
				if(isset($set[$k]) && $set[$k]->check!=''){
					$l->addtoNeed($row->extname, $row->extype);
					if($row->vartype=='bool'){
						$v=@(bool)$v;
					}
					$value=$v;
					$error=null;
					$scope="useredit";
					//eval(@$row->check);
					$this->runEval($row, $u, $value, $error, $scope);
					if($error!==null){
						redirect('index.php?pack=user&view=edit',$error, 'error');
					}
					$v=$value;
					$vars['misc']->$k=$value;
					
				}
				if($row==false || $v==unserialize($row->default)){
					unset($vars['misc']->$k);
				}
								
			}
			// make Query
			$db->setQuery('UPDATE #__users SET name='.$db->Quote($vars['name']).
			', misc='.$db->Quote(serialize($vars['misc'])).
			', settings='.$db->Quote(serialize($vars['settings'])).
			$suf.' WHERE id='.$u->id
			);

			if($db->query()){
				if(isset($newPass)){
					$_SESSION['pass']=$newPass;
				}
				redirect('index.php?pack=user&view=edit', trans('USER EDITED SUCC'));
			}else{
				redirect('index.php?pack=user&view=edit', trans('ERROR IN DB'), 'error');
			}
		}
	}
	
	function avatar(){
		$u=$this->getCurrentUser();
		$vars=ArtaRequest::getVars('post');
		$vars=ArtaUtility::array_extend($vars, array('type'=>'none'));
		
		if($u->id<=0){
			redirect('index.php?pack=user&view=login&redirect='.base64_encode('index.php?pack=user&view=avatar'), trans('YOU ARE NOT LOGGED IN'), 'warning');
		}
		
		if((int)$u->id!==getVar('uid', null, 'post', 'int')){
			redirect('index.php?pack=user&view=avatar');
		}else{
			switch($vars['av_type']){
				case 'delete':
					if((string)$u->avatar==''){
						redirect('index.php?pack=user&view=avatar', trans('you have no avatar to remove'));
					}else{
						ArtaFile::Delete(ARTAPATH_BASEDIR.'/media/avatars/'.$u->id.'.jpg');
						ArtaFile::Delete(ARTAPATH_BASEDIR.'/media/avatars/big/'.$u->id.'.jpg');
						$toset='';
					}
				break;
				case 'upload':
				case 'link':
					$tmpname=ArtaString::makeRandStr().'.img';
					if($vars['av_type']=='upload'){
						if(!is_uploaded_file($_FILES['uploadFile']['tmp_name'])){
							redirect('index.php?pack=user&view=avatar', trans('ERROR IN WRITING'), 'error');
						}
						$file=$_FILES['uploadFile']['tmp_name'];
						$did=ArtaFile::rename($file, ARTAPATH_BASEDIR.'/tmp/'.$tmpname);
						$ext=strtolower(ArtaFile::getExt($_FILES['uploadFile']['name']));
					}else{
						if(strpos($vars['linkFile'], 'http://')!==0 && 
						strpos($vars['linkFile'], 'https://')!==0 &&
						strpos($vars['linkFile'], 'ftp://')!==0 &&
						strpos($vars['linkFile'], 'ftps://')!==0
						){
							redirect('index.php?pack=user&view=avatar', trans('invalid file'), 'error');
						}
						$file=$vars['linkFile'];
						$ch = curl_init();
						curl_setopt($ch, CURLOPT_URL, $file);
						curl_setopt($ch, CURLOPT_HEADER, false);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
						curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
						curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
						curl_setopt($ch, CURLOPT_USERAGENT,ARTA_USERAGENT);
						curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
						curl_setopt($ch, CURLOPT_TIMEOUT, 30);
						set_time_limit(40);
						
						// grab URL
						$c= @curl_exec($ch);
						if($c==false){
							redirect('index.php?pack=user&view=avatar', trans('UNABLE TO CONNECT'), 'error');
						}
						$did=ArtaFile::write(ARTAPATH_BASEDIR.'/tmp/'.$tmpname, $c);
						$ext=strtolower(ArtaFile::getExt($vars['linkFile']));
					}
					
					if($did){
						switch($ext){
							case 'jpg':
							case 'jpeg':
								$i=@imagecreatefromjpeg(ARTAPATH_BASEDIR.'/tmp/'.$tmpname);
							break;
							case 'gif':
								$i=@imagecreatefromgif(ARTAPATH_BASEDIR.'/tmp/'.$tmpname);
							break;
							case 'png':
								$i=@imagecreatefrompng(ARTAPATH_BASEDIR.'/tmp/'.$tmpname);
							break;
							default:
								$i=false;
							break;
						}
						ArtaFile::Delete(ARTAPATH_BASEDIR.'/tmp/'.$tmpname);
						if(!is_resource($i)){
							redirect('index.php?pack=user&view=avatar', trans('invalid file'), 'error');
						}
						$w=imagesx($i);
						$h=imagesy($i);
						if($w>$h){
							$new_w=100;
							$new_h=round(($h*100)/$w);
						}else{
							$new_w=round(($w*100)/$h);
							$new_h=100;
						}
						$newi=imagecreatetruecolor($new_w, $new_h);
						imagecopyresampled($newi, $i, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
						ob_start();
						imagejpeg($newi);
						$con=ob_get_contents();
						ob_end_clean();
						$r=array();
						$r[]=ArtaFile::write(ARTAPATH_MEDIA.'/avatars/'.$u->id.'.jpg', $con);
						
						$new_w=$new_w*2;
						$new_h=$new_h*2;
						unset($newi);
						$newi=imagecreatetruecolor($new_w, $new_h);
						imagecopyresampled($newi, $i, 0, 0, 0, 0, $new_w, $new_h, $w, $h);
						ob_start();
						imagejpeg($newi);
						$con=ob_get_contents();
						ob_end_clean();
						$r[]=ArtaFile::write(ARTAPATH_MEDIA.'/avatars/big/'.$u->id.'.jpg', $con);
						if(in_array(false, $r)){
							redirect('index.php?pack=user&view=avatar', trans('ERROR IN WRITING'), 'error');
						}else{
							$toset=$u->id.'.jpg';
						}
					}else{
						redirect('index.php?pack=user&view=avatar', trans('ERROR IN WRITING'), 'error');
					}
				break;
				case 'gravatar':
					ArtaFile::Delete(ARTAPATH_BASEDIR.'/media/avatars/'.$u->id.'.jpg');
					ArtaFile::Delete(ARTAPATH_BASEDIR.'/media/avatars/big/'.$u->id.'.jpg');
					$toset='gravatar';
				break;
				case 'none':
				default:
					redirect('index.php');
				break;
				
			}
			
			$db=ArtaLoader::DB();
			$db->setQuery('UPDATE #__users SET avatar='.$db->Quote($toset).' WHERE id='.$u->id, array('avatar'));
			if($db->query()){
				redirect('index.php?pack=user&view=avatar', trans('AVATAR UPLOADED SUCC'));
			}
			
		}
	}
	
	function saveMsg(){
		$u=$this->getCurrentUser();
		$vars=ArtaRequest::getVars('post');
		$vars=ArtaFilterinput::clean($vars, array('owner'=>'int', 'content'=>'very-safe-html', 'title'=>'string', 'vis'=>'string'));
		
		if(ArtaCaptcha::verifyCode($vars['vis'],'vis')==false){
			redirect('index.php?pack=user&view=remind', trans("INVALID CAPTCHA"), 'warning');
		}
		
		if($u->id==0){
			redirect('index.php?pack=user&view=profile&uid='.$vars['owner'], trans('GUESTS CANT POST MESSAGES'), 'error');
		}
		
		if($vars['owner']==$u->id || ArtaUsergroup::getPerm('can_post_visitormessage', 'package', 'user')){
			$db=ArtaLoader::DB();
			$db->setQuery('INSERT INTO #__user_visitormessages (title,content,`for`,`by`,added_time) VALUES ('.$db->Quote($vars['title']).', '.$db->Quote($vars['content']).', '.$db->Quote($vars['owner']).', '.$db->Quote($u->id).','.$db->Quote(ArtaDate::toMySQL(time())).')');
			if($db->query()){
				redirect('index.php?pack=user&view=profile&uid='.$vars['owner'], trans('SAVED SUCC'));
			}else{
				redirect('index.php?pack=user&view=profile&uid='.$vars['owner'], trans('ERROR IN DB'), 'error');
			}
		}else{
			redirect('index.php?pack=user&view=profile&uid='.$vars['owner'], trans('YOU CANNOT POST VISITORMESSAGES'), 'error');
		}
	}
	
	function deleteMsg(){
		$u=$this->getCurrentUser();
		$vars=ArtaRequest::getVars('post');
		$vars['owner']=ArtaFilterinput::Clean($vars['owner'], 'int');
		$vars['id']=ArtaFilterinput::Clean($vars['id'], 'int');
		
		$us=ArtaLoader::User();
		$us=$us->getUser($vars['owner']);
		
		if((@$us->id==$u->id || ArtaUsergroup::getPerm('can_edit_others_visitormessage', 'package', 'user')) && is_object($us)){
			$db=ArtaLoader::DB();
			$db->setQuery('DELETE FROM #__user_visitormessages WHERE id='.$db->Quote($vars['id']).' AND `for`='.$db->Quote($us->id));
			if($db->query()){
				redirect('index.php?pack=user&view=profile&uid='.$vars['owner'], trans('DELETED SUCC'));
			}else{
				redirect('index.php?pack=user&view=profile&uid='.$vars['owner'], trans('ERROR IN DB'), 'error');
			}
		}else{
			redirect('index.php?pack=user&view=profile&uid='.$vars['owner'], trans('YOU CANNOT EDIT OTHERS VISITORMESSAGES'), 'error');
		}
	}
	
	function saveNote(){
		$v=ArtaRequest::getVars('post');
		$u=$this->getCurrentUser();
		if($v['uid']!==$u->id || $u->id==0){
			ArtaError::show('401');
		}else{
			if(ArtaUserHelper::setText($v['txt'], $u->id, 'id')){
				redirect('index.php?pack=user&view=notes', trans('NOTE SAVED SUCC'));
			}else{
				redirect('index.php?pack=user&view=notes', trans('ERROR IN DB'),'error');
			}
		}
	}

}

function loginMsg1($time, $timeout){
		unset($_SESSION['LOGIN_TRYOUTS']);
		redirect('index.php?pack=user&view=login', sprintf(trans("LOGINMISS_EXPIRED"),(int)(($time+$timeout-time())/60)+1), 'warning');
}

?>

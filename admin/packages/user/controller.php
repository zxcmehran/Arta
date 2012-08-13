<?php
if(!defined('ARTA_VALID')){die('No access');}
class UserController extends ArtaPackageController{
	
	function display(){
		$view=$this->getView(getVar('view', 'userlist', '', 'string'));
		ArtaAdminTabs::addTab(trans('USERS'), 'index.php?pack=user');
		ArtaAdminTabs::addTab(trans('WAIT FOR MODERATION'), 'index.php?pack=user&view=moderation');
		$view->display();
	}

	function activate(){
		if(ArtaUserGroup::getPerm('can_edit_users_activity', 'package', 'user') == false){
			ArtaError::show(500 ,trans('YOU CANNOT EDIT USERS ACTIVITY'));
		}
		
		if(strtoupper($_SERVER["REQUEST_METHOD"])!='POST' && ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
	
		$db=ArtaLoader::DB();
		$ids=getVar('ids', false, '', 'array');
		if($ids == false || count($ids) == 0){
			redirect('index.php?pack=user');
		}
		if(getVar('moderation',false)==true){$m='&view=moderation';}else{$m='&view=userlist';}
		$uc=ArtaLoader::User();
		foreach($ids as $id){
			$u=$uc->getUser($id, 'id');
			if($u->activation == 'MODERATOR'){
				$mail=ArtaLoader::Mail();
				$config=ArtaLoader::Config();
				$mail->mail($u->email, sprintf(trans('YOU ARE ACTIVATED_TITLE'), $config->site_name), sprintf(trans('YOU ARE ACTIVATED'), 
				htmlspecialchars($config->site_name), 
				htmlspecialchars(ArtaURL::getSiteURL()),
				htmlspecialchars($config->site_name)));
			}
			$db->setQuery("UPDATE #__users SET activation='0' WHERE id=".$db->Quote($id), array('activation'));
			$db->query();
		}
		
		redirect('index.php?pack=user'.$m, trans('USER ACTIVATED'));
	}

	function deactivate(){
		if(ArtaUserGroup::getPerm('can_edit_users_activity', 'package', 'user') == false){
					ArtaError::Show(500 ,trans('YOU CANNOT EDIT USERS ACTIVITY'));
		}
		
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
		
		$db=ArtaLoader::DB();
		$id=getVar('ids', false, '', 'array');
		if($id == false || count($id) == 0){
			redirect('index.php?pack=user');
		}
		
		$id=$id[0];
		$config=ArtaLoader::Config();
		$db->setQuery("UPDATE #__users SET activation='".md5($config->secret)."' WHERE id=".$db->Quote($id), array('activation'));
		$db->query();
		redirect('index.php?pack=user', trans('USER DEACTIVATED'));
	}
	
	private function runEval(&$row, &$u, &$value, &$error, &$scope){
		eval($row->check);
	}

	function save(){
		if(ArtaUserGroup::getPerm('can_addedit_users', 'package', 'user') == false){
			ArtaError::show(403,trans('YOU CANNOT ADDEDIT USERS'));
		}
		
		ArtaLoader::Import('user->helper');
		ArtaLoader::Import('misc->date');
		
		$vars =ArtaRequest::getVars('post', 'object');

		//gathering information
		foreach($vars->ids as $k=>$v){
			
			if($k>0){$id='&ids[]='.$k;}else{$id='';}
			
			if(ArtaUtility::keysExists(array(
			'username',
			'email',
			'password',
			'verify_password',
			'activation',
			'usergroup',
			'ban',
			'ban_reason',
			'register_date',
			'lastvisit_date'			
			),$v)==false){
				ArtaError::show(400, trans('FORM ISNT COMPLETE'));
			}
			
			$v=ArtaFilterinput::trim($v);
			$v=ArtaFilterinput::array_limit($v,
			array(
			'name'=>255,
			'username'=>255,
			'email'=>255,
			'ban_reason'=>255
			)
			);
			
			$v=ArtaUtility::array_extend($v, 
			array(
				'settings'=>array(),
				'misc'=>array(),
				'av_type'=>'none'
			)
			);
			
			$v=ArtaFilterinput::clean($v, array(
			'name'=>'string',
			'username'=>'string',
			'email'=>'string',
			'password'=>'string',
			'verify_password'=>'string',
			'activation'=>'bool',
			'usergroup'=>'int',
			'ban'=>'bool',
			'ban_reason'=>'string',
			'register_date'=>'datetime',
			'lastvisit_date'=>'datetime',
			'settings'=>'array',
			'misc'=>'array',
			'av_type'=>'string',
			'linkFile'=>'string'
			));
			
			if($v['register_date']==false){
				redirect('index.php?pack=user&view=new'.$id, trans('INVALID REGISTER_DATE'),'warning');
			}

			if($v['name']==null){
				ArtaError::show(400, trans('FORM ISNT COMPLETE'));
			}
			
			foreach($v as $kk=>$vv){
				if($kk !== 'ban_reason' && $kk !== 'name' && $kk !== 'lastvisit_date' && (($k!==0 && $kk !== 'password') || $k==0) &&  (($k!==0 && $kk !== 'verify_password') || $k==0) && $vv===''){
					redirect('index.php?pack=user&view=new'.$id, trans('FORM ISNT COMPLETE'), 'error');
				}
			}
			
			
			
			if($v['password'] !==''){
				// check verifies
				if($v['password'] !== $v['verify_password']){
					redirect('index.php?pack=user&view=new'.$id, trans('PASSWORD VERIFY FAILED'), 'error');
				}
				$pass=ArtaString::hash($v['password'], 'artahash-no-md5');
			}else{
				$pass='';
			}
			
			if(is_int(strpos($v['username'], ' '))){
				redirect('index.php?pack=user&view=new'.$id,trans('DO NOT USE SPACE IN UNAME'), 'warning');
			}
			
			$db = ArtaLoader::DB();
			$l=ArtaLoader::Language();
			
			$db->setQuery('SELECT * FROM #__userfields');
			$uf=ArtaUtility::keyByChild((array)$db->loadObjectList(), 'fieldtype', true);

			if(@$uf['setting']){
				$uf['setting']=ArtaUtility::keyByChild($uf['setting'], 'var');
			}
			
			if(@$uf['misc']){
				$uf['misc']=ArtaUtility::keyByChild($uf['misc'], 'var');
			}
			
			$conf=ArtaLoader::Config();
			if(unserialize($uf['setting']['cal_type']->default)==null){
				$uf['setting']['cal_type']->default=serialize($conf->cal_type);
			}
			if(unserialize($uf['setting']['time_offset']->default)==null){
				$uf['setting']['time_offset']->default=serialize($conf->time_offset);
			}
			$userc=ArtaLoader::User();
			$urow=$k>0?$userc->getUser($k):null;
			$misc = new stdClass;
			foreach($v['misc'] as $kk=>$vv){
				$row=@$uf['misc'][$kk];
				if($row->vartype=='bool'){
					$vv=@(bool)$vv;
				}
				if(isset($uf['misc'][$kk]) && $uf['misc'][$kk]->check!=''){
					$l->addtoNeed($row->extname, $row->extype);
					$value=$vv;
					$error=null;
					$scope="adminedit";
					//eval(@$row->check);
					$this->runEval($row, $urow, $value, $error, $scope);
					if($error!==null){
						redirect('index.php?pack=user&view=new'.$id,$error, 'warning');
					}
					$vv=$value;
					
				}
				if($row && $vv!=unserialize($row->default)){
					$misc->$kk=$vv;
				}
				
			}
			$misc=serialize($misc);

			$set = new stdClass;
			foreach($v['settings'] as $kk=>$vv){
				$row=$uf['setting'][$kk];
				if($row->vartype=='bool'){
					$vv=@(bool)$vv;
				}
				if(isset($uf['setting'][$kk]) && $uf['setting'][$kk]->check!=''){
					$l->addtoNeed($row->extname, $row->extype);
					$value=$vv;
					$error=null;
					$scope="adminedit";
					//eval(@$row->check);
					$this->runEval($row, $urow, $value, $error, $scope);
					if($error!==null){
						redirect('index.php?pack=user&view=new'.$id,$error, 'warning');
					}
					$vv=$value;
				}
				if($row && $vv!=unserialize($row->default)){
					$set->$kk=$vv;
				}
				
			}
			$set=serialize($set);

			
			$db->setQuery("SELECT * FROM #__users WHERE id != ".$db->Quote($k)." AND (LOWER(`username`) = ".$db->Quote(ArtaUTF8::strtolower($v['username']))." OR LOWER(`email`) = ".$db->Quote(ArtaUTF8::strtolower($v['email'])).")");
			$exist=$db->loadObject();

			if(strtolower(@$exist->email) == strtolower($v['email'])){
				redirect('index.php?pack=user&view=new', sprintf(trans('EMAIL EXISTS'), htmlspecialchars($v['email'])));
			}
			if(strtolower(@$exist->username) == strtolower($v['username'])){
				redirect('index.php?pack=user&view=new', sprintf(trans('USERNAME EXISTS'), htmlspecialchars($v['username'])));
			}


			if($k>0){$id=$db->getEscaped($k);}else{$id='';}
			
			if($id==''){
				if($v['lastvisit_date']=false){
					$v['lastvisit_date']='0000-00-00 00:00:00';
				}
				$db->setQuery("INSERT INTO #__users ".
					"(`id`, `name`, `username`, `email`, `password`, `usergroup`, `ban`, `ban_reason`, `register_date`, `lastvisit_date`, `settings`, `misc`, `activation`)".
					" VALUES (NULL, ".$db->Quote($v['name']).
						", ".$db->Quote($v['username']).
						", ".$db->Quote($v['email']).
						", ".$db->Quote($pass).
						", ".$db->Quote($v['usergroup']).
						", ".$db->Quote($v['ban']).
						", ".($v['ban_reason'] ? $db->Quote($v['ban_reason']): 'NULL').
						", ".$db->Quote(ArtaDate::toMySQL($v['register_date'])).
						", ".$db->Quote(ArtaDate::toMySQL($v['lastvisit_date'])).
						", ".$db->Quote($set).
						", ".$db->Quote($misc).
						", ".$db->Quote($v['activation']?'0':$conf->secret).");");
			}else{
				if($v['lastvisit_date']=false){
					$v['lastvisit_date']='0000-00-00 00:00:00';
				}
				if(strlen($pass) > 0){$pass=", `password`=".$db->Quote($pass);}
				
				$u=ArtaLoader::User();
				$u=$u->getUser($id);
				if($u->username!==$v['username']){
					ArtaCache::clearData('user','sef_aliases');
				}
				
				$vars=$v;
				switch($vars['av_type']){
					case 'delete':
							$toset='';
					break;
					case 'upload':
					case 'link':
						$tmpname=ArtaString::makeRandStr().'.img';
						if($vars['av_type']=='upload'){
							
							$errs=ArtaFilterinput::uploadErr($_FILES['uploadFile']['error']);
							if($errs!==false){
								redirect('index.php?pack=user&view=new&ids[]='.$k, $errs, 'error');
							}
							
							if(!is_uploaded_file($_FILES['uploadFile']['tmp_name'])){
								redirect('index.php?pack=user&view=new&ids[]='.$k, trans('ERROR IN WRITING'), 'error');
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
								redirect('index.php?pack=user&view=new&ids[]='.$k, trans('invalid file'), 'error');
							}
							$file=$vars['linkFile'];
							$ch = curl_init();
							curl_setopt($ch, CURLOPT_URL, $file);
							curl_setopt($ch, CURLOPT_HEADER, false);
							curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
							curl_setopt($ch, CURLOPT_BINARYTRANSFER,true);
							curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
							curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
							curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
							curl_setopt($ch, CURLOPT_TIMEOUT, 30);
							curl_setopt($ch, CURLOPT_USERAGENT,ARTA_USERAGENT);
							set_time_limit(40);
							// grab URL
							$c= @curl_exec($ch);
							if($c==false){
								redirect('index.php?pack=user&view=new&ids[]='.$k, trans('UNABLE TO CONNECT'), 'error');
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
								redirect('index.php?pack=user&view=new&ids[]='.$k, trans('invalid file'), 'error');
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
								redirect('index.php?pack=user&view=new&ids[]='.$k, trans('ERROR IN WRITING'), 'error');
							}else{
								$toset=$u->id.'.jpg';
							}
						}else{
							redirect('index.php?pack=user&view=new&ids[]='.$k, trans('ERROR IN WRITING'), 'error');
						}
					break;
					case 'gravatar':
						ArtaFile::Delete(ARTAPATH_BASEDIR.'/media/avatars/'.$u->id.'.jpg');
						ArtaFile::Delete(ARTAPATH_BASEDIR.'/media/avatars/big/'.$u->id.'.jpg');
						$toset='gravatar';
					break;
					default:
						$toset=$id.'.jpg';
					break;
				}
				
				
				$u=ArtaLoader::User();
				$u=$u->getUser($id);
				$updated=true;
				if($u->activation=='MODERATOR' && $v['activation']==false){
					$act='MODERATOR';
				}else{
					$act=$v['activation']?'0':$conf->secret;
				}
				$db->setQuery("UPDATE #__users SET `name`=".$db->Quote($v['name']).
					", `username`=".$db->Quote($v['username']).
					", `email`=".$db->Quote($v['email']).
					", `usergroup`=".$db->Quote($v['usergroup']).
					", `ban`=".$db->Quote($v['ban']).
					", `ban_reason`=".$db->Quote($v['ban_reason']).
					", `register_date`=".$db->Quote(ArtaDate::toMySQL($v['register_date'])).
					", `lastvisit_date`=".$db->Quote(ArtaDate::toMySQL($v['lastvisit_date'])).
					", `settings`=".$db->Quote($set).
					", `misc`=".$db->Quote($misc).
					", `avatar`=".$db->Quote($toset).
					", `activation`=".$db->Quote($act).
					$pass.
					" WHERE id=".$db->Quote($id), true, array('user,sef_aliases'));
			}

			$res = $db->query();
			
			if(@$updated==true && $res==true && $v['activation']==true && $u->activation=='MODERATOR'){
				$mail=ArtaLoader::Mail();
				$config=ArtaLoader::Config();
				$mail->mail($u->email, sprintf(trans('YOU ARE ACTIVATED_TITLE'), $config->site_name), sprintf(trans('YOU ARE ACTIVATED'), 
				htmlspecialchars($config->site_name), 
				htmlspecialchars(ArtaURL::getSiteURL()),
				htmlspecialchars($config->site_name)));
			}

		}
		if(getVar('moderation',false)==true){$m='&view=moderation';}else{$m='&view=userlist';}
		if($res==false){
			redirect('index.php?pack=user'.$m, trans('ERROR IN DB'));
		}
		redirect('index.php?pack=user'.$m, trans('USER ADDED/EDITED'));
		
	}

	function force_logout(){
		if(getvar('toindex', false)!==false){
			$to='index.php';
		}else{
			$to='index.php?pack=user';
		}
		if(ArtaUserGroup::getPerm('can_force_logout_users', 'package', 'user') == false){
			ArtaError::show(403,trans('YOU CANNOT FORCE LOGOUT USERS'));
		}
		$ids=getVar('ids',false, 'post', 'array');
		if($ids==false){redirect('index.php?pack=user');}
		ArtaLoader::Import('user->helper');
		$db=ArtaLoader::DB();
		foreach($ids as $k=>$v){$ids[$k]=$db->Quote($v);}
		$db->setQuery('SELECT * FROM #__sessions WHERE userid IN ('.implode(',', $ids).')');
		$uids=$db->loadObjectList();

		foreach($uids as $k=>$v){
			ArtaUserHelper::Logout($v->session_id);
		}

		redirect($to, trans('USER LOGGED OUT'));
	}

	function delete(){
		if(ArtaUserGroup::getPerm('can_delete_users', 'package', 'user') == false){
			ArtaError::show(403, trans('YOU CANNOT DELETE USERS'));}
		$ids=getVar('ids',false, 'post', 'array');
		if($ids==false){redirect('index.php?pack=user');}
		ArtaLoader::Import('user->helper');
		foreach($ids as $v){
			ArtaUserHelper::Delete($v, 'id');
		}
		if(getVar('moderation',false)==true){$m='&view=moderation';}else{$m='&view=userlist';}
		redirect('index.php?pack=user'.$m, trans('USER DELETED'));
	}

}
?>
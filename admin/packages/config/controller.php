<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ConfigController extends ArtaPackageController{

	function display(){
		ArtaAdminTabs::addTab(trans('c_config'), 'index.php?pack=config&view=config');
		ArtaAdminTabs::addTab(trans('c_defaults'), 'index.php?pack=config&view=defaults');
		ArtaAdminTabs::addTab(trans('c_package'), 'index.php?pack=config&view=extension&extype=package');
		ArtaAdminTabs::addTab(trans('c_module'), 'index.php?pack=config&view=extension&extype=module');
		ArtaAdminTabs::addTab(trans('c_plugin'), 'index.php?pack=config&view=extension&extype=plugin');
		ArtaAdminTabs::addTab(trans('c_template'), 'index.php?pack=config&view=extension&extype=template');
		ArtaAdminTabs::addTab(trans('c_cron'), 'index.php?pack=config&view=extension&extype=cron');
		$viewname=getVar('view', 'group', '', 'string');
		$view = $this->getView($viewname);
		$view->display();
	}

	function save(){
		$type=getVar('extype', false, 'post', 'string');
		$id=getVar('extname', false, 'post', 'string');
		

		if($type == false){
			ArtaError::show(400);
		}

		if($id == false){
			ArtaError::show(400);
		}
		

		$allowed=array('package', 'module', 'plugin', 'template', 'cron');
		if(in_array($type, $allowed) == false){
			ArtaError::show(400);
		}

		if(ArtaUserGroup::getPerm('can_edit_settings_'.$type, 'package', 'config')== false){
			ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
		}

		$s=getVar('settings', false, 'post', 'array');
		if(is_array($s) == false || count($s)==0){
			redirect('index.php?pack=config&view=group');
		}
		
		// clean cache
		ArtaCache::clearData($type.'_setting', 'site_'.$id);
		ArtaCache::clearData($type.'_setting', 'admin_'.$id);
		ArtaCache::clearData($type.'_setting', '*_'.$id);
		

		$language=ArtaLoader::Language();
		$path=ARTAPATH_ADMIN;
		$db=ArtaLoader::DB();
		foreach($s as $k=>$v){
			$xk=explode('|',$k);
			$k=$xk[0];
			$client=$xk[1];
			if($type=='cron'){
				$client='site';
			}
			if($client!=='site'&& $client!=='admin' && $client!=='*'){
				ArtaError::show(400);
			}
			$value=$v;
			$db->setQuery("SELECT * FROM #__settings WHERE var=".$db->Quote($k)." AND extype=".$db->Quote($type)." AND extname='".$id."' AND client='".$client."'");
			$row=$db->loadObject();
			if($row->vartype=='bool'){
				$value=@(bool)$value;
			}
			$error=null;
			$language->addtoNeed($id, $type, $path);
			$this->runEval($row,$id,$type,$client,$value,$error);
			$s[$k]=$value;
			$v=$value;
			if($error==null){
				$db->setQuery("UPDATE #__settings SET value = ".$db->Quote(serialize($value))." WHERE var=".$db->Quote($k)." AND extype=".$db->Quote($type)." AND extname=".$db->Quote($id)." AND client=".$db->Quote($client), array('value'));
				$res=$db->query();
			}else{
				redirect('index.php?pack=config&view=edit&extype='.$type.'&extname='.$id.'&client='.$client, $error, 'error');
			}
		}
		if(!isset($res)){$res=true;}
		if($res==true){
			redirect('index.php?pack=config&view=edit&extype='.$type.'&extname='.$id.'&client='.$client, trans('CHANGES MADE SUCC'));		
		}else{
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=config&view=edit&extype='.$type.'&extname='.$id.'&client='.$client);		
		}
		
	}
	
	private function runEval(&$row,&$id,&$type,&$client,&$value,&$error){
		eval($row->check);
	}

	function config_save(){

		$vars=ArtaRequest::getVars('POST', 'object');
		if(!isset($vars->data) || !is_array($vars->data)){
			redirect('index.php?pack=config&view=group');
		}
		if(ArtaUserGroup::getPerm('can_edit_settings_config', 'package', 'config')== false){
			ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
		}
		ArtaLoader::Import('misc->date');
		$conf="<?php \r\n".
			"#########################################\r\n".
			"# Arta is created by Mehran Ahadi artaproject.com \r\n".
			"# config.php \r\n".
			"# This file contains settings for Arta ".ArtaVersion::getVersion()." \r\n".
			"# Last Modified : ".ArtaDate::toMySQL(time())."\r\n".
			"#########################################\r\n".
			"if(!defined('ARTA_VALID')){die('No access');}\r\n".
			"class ArtaConfig {\r\n";
		
		$config=ArtaLoader::Config();
		$new_config=new ArtaConfig;
		
		if($vars->data['offline_pass']==''){
			$vars->data['offline_pass']=$new_config->offline_pass;
		}
		
		if($vars->data['db_pass']==''){
			$vars->data['db_pass']=$new_config->db_pass;
		}
		
		if($vars->data['mail_password']==''){
			$vars->data['mail_password']=$new_config->mail_password;
		}
		
		if($vars->data['ftp_pass']==''){
			$vars->data['ftp_pass']=$new_config->ftp_pass;
		}
		
		if((int)$vars->data['ftp_enabled']!==0){
			$config->ftp_enabled=1;
			$config->ftp_host=$vars->data['ftp_host'];
			$config->ftp_port=$vars->data['ftp_port'];
			$config->ftp_user=$vars->data['ftp_user'];
			$config->ftp_pass=$vars->data['ftp_pass'];
			$config->ftp_path=$vars->data['ftp_path'];
			ArtaLoader::Import('file->file');
			$ftp=ArtaFile::getFTP();
			if($ftp==false){
				redirect('index.php?pack=config&view=config', trans('INVALID FTP ACCESS INFORMATION'), 'error');
			}
		}else{
			$config->ftp_enabled='0';
		}
		
		if($vars->data['site_name']==false){
			redirect('index.php?pack=config&view=config', sprintf(trans('_INVALID'), trans('SITE_NAME')), 'error');
		}

		if(($vars->data['list_limit']=ArtaRequest::clean($vars->data['list_limit'],'int'))==false){
			redirect('index.php?pack=config&view=config', sprintf(trans('_INVALID'), trans('LIST_LIMIT')), 'error');
		}
		
		if(($vars->data['cache_lifetime']=ArtaRequest::clean($vars->data['cache_lifetime'],'int'))==false){
			redirect('index.php?pack=config&view=config', sprintf(trans('_INVALID'), trans('CACHE_LIFETIME')), 'error');
		}
		
		if(($vars->data['cron_log_lifetime']=ArtaRequest::clean($vars->data['cron_log_lifetime'],'int'))==false){
			redirect('index.php?pack=config&view=config', sprintf(trans('_INVALID'), trans('CRON_LOG_LIFETIME')), 'error');
		}
		
		if(($vars->data['session_lifetime']=ArtaRequest::clean($vars->data['session_lifetime'],'int'))==false){
			redirect('index.php?pack=config&view=config', sprintf(trans('_INVALID'), trans('SESSION_LIFETIME')), 'error');
		}
		
		$vars->data['main_domain']= trim($vars->data['main_domain']);
		if(strpos($vars->data['main_domain'], '/')!==false OR strpos($vars->data['main_domain'], '\\')!==false OR strpos($vars->data['main_domain'], ' ')!==false){
			redirect('index.php?pack=config&view=config', sprintf(trans('_INVALID'), trans('MAIN_DOMAIN')), 'error');
		}
		
		
		foreach($vars->data as $k => $v){
			$conf .="\tvar $".$k." = '".addcslashes($v, "'")."';\r\n";
		}
		

		$conf .="\tvar $".'secret'." = '".addcslashes($new_config->secret, "'")."';\r\n";
		$conf .="\tvar $".'install_time'." = '".addcslashes($new_config->install_time, "'")."';\r\n";

		$conf .="\r\n\tfunction __construct(){\r\n".
			"\t\t\$this->time_offset=(string)\$this->time_offset;\r\n".
			"\t\t\$this->time_offset= substr(\$this->time_offset, 0, 1)=='-' ? \$this->time_offset : '+'.\$this->time_offset;\r\n".
			"\t}\r\n".
			"}\r\n".
			"?>";
		$res=ArtaFile::chmod(ARTAPATH_BASEDIR.'/config.php', 0644);
		if($res==true){
			$res=ArtaFile::write(ARTAPATH_BASEDIR.'/config.php', $conf);
			
		}
		ArtaFile::chmod(ARTAPATH_BASEDIR.'/config.php', 0444);
		if($res == true){
			redirect('index.php?pack=config&view=config', trans('CHANGES MADE SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN WRITING CONFIG'));
		}
	}

	function save_defaults(){
		$vars=ArtaRequest::getVars('post', 'object');
		if(ArtaUserGroup::getPerm('can_edit_settings_defaults', 'package', 'config')== false){
			ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
		}
		$language=ArtaLoader::Language();
		$path=ARTAPATH_ADMIN;
		$db=ArtaLoader::DB();
		$vars->fields=ArtaFilterinput::Clean($vars->fields, 'array');
		foreach($vars->fields as $k=>$v){
			$k=ArtaString::split($k,'|', false, true);
			$db->setQuery("SELECT * FROM #__userfields WHERE var=".$db->Quote($k[0])." AND extname=".$db->Quote($k[1])." AND extype=".$db->Quote($k[2])." AND fieldtype=".$db->Quote($k[3]));
			$row=$db->loadObject();
			$error=null;
			$value=$v;
			if($row->vartype=='bool'){
				$value=@(bool)$value;
			}
			$scope="defaults";
			$language->addtoNeed($row->extname, $row->extype, $path);
			$urow=null;
			$this->runEval2($row,$urow,$value,$error, $scope);
			//@eval($row->check);
			$vars->fields[ArtaString::stick($k, '|', true)]=$value;
			$v=$value;
			if($error==null){
				$db->setQuery("UPDATE #__userfields SET `default` = ".$db->Quote(serialize($v))." WHERE var=".$db->Quote($k[0])." AND extname=".$db->Quote($k[1])." AND extype=".$db->Quote($k[2])." AND fieldtype=".$db->Quote($k[3]), array('default'));
				if($db->query() == false){
					$res=false;
				}
			}else{
				redirect('index.php?pack=config&view=defaults', $error, 'error');
			}
		}
		if(!isset($res)){$res=true;}
		if($res==true){
			redirect('index.php?pack=config&view=defaults', trans('CHANGES MADE SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'),'index.php?pack=config&view=defaults');
		
		}
		
	}
	
	private function runEval2(&$row, &$u, &$value, &$error, &$scope){
		eval($row->check);
	}

}
?>
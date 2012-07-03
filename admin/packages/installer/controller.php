<?php
if(!defined('ARTA_VALID')){die('No access');}
class InstallerController extends ArtaPackageController{
	
	function display(){
		$view=$this->getView(getVar('view', 'install'));
		ArtaAdminTabs::addTab(trans('EXTENSION MANAGER'), 'index.php?pack=installer');
		ArtaAdminTabs::addTab(trans('INSTALLATION LOGS'), 'index.php?pack=installer&view=logs');
		$view->display();
	}

	function install(){
		$i=getVar('extension', false, 'files');
		$err=ArtaFilterinput::uploadErr($i['error']);
		if($err!==false && $i['error']!=UPLOAD_ERR_NO_FILE){ // user tried to upload but couldn't...
			redirect('index.php?pack=installer', $err, 'error');
		}
		
		if(ArtaUsergroup::getPerm('can_install_extensions', 'package', 'installer')){
			if($i['error']==UPLOAD_ERR_NO_FILE || $i==false){ // seems user ries to use local file or it's next steps of installation.
				$address=getVar('localfile', false, '', 'string');
				if(getVar('del', false, '', 'bool')){ // if it's next steps of a upload based installation, then archive must be removed after install.
					$del=true;
					$address=ARTAPATH_BASEDIR.'/'.$address;
				}
				if(is_file($address)==false){
					redirect('index.php?pack=installer', trans('FILE NOT FOUND ON YOUR HOST'), 'error');
				}
			}else{
				$path=ARTAPATH_BASEDIR.'/tmp/installer_sources/'.$i['name'];
				if(is_file($path)){
					$fileext=ArtaFile::getExt($i['name']);
					$i=2;
					while(is_file(ARTAPATH_BASEDIR.'/tmp/installer_sources/'.substr($i['name'],0,strlen($i['name'])-strlen($fileext)-1).'('.$i.')'.$fileext)){
						$i++;
					}
					$path=ARTAPATH_BASEDIR.'/tmp/installer_sources/'.substr($i['name'],0,strlen($i['name'])-strlen($fileext)-1).'('.$i.')'.$fileext;
				}
				if(!ArtaFile::rename($i['tmp_name'], $path)){
					redirect('index.php?pack=installer', trans('ARCHIVE MOVE ERROR'), 'error');
				}
				$address=$path;
				$del=true;
			}
			
			$p=@$del?'&del=1':'';
			
			ArtaLoader::Import('#installer->installer');
			
			if(ArtaFile::getExt($address)=='xml'){ // user cannot upload an xml file.
				ArtaError::show(400);
			}
			
			$inst = new ArtaInstaller;
			$res=$inst->init($address);
			
			if(!is_bool($res) || $res!==true){
				redirect('index.php?pack=installer', trans('ERROR').': '.trans($res), 'error');
			}
			
			if($inst->update==true && ArtaUsergroup::getPerm('can_install_updates', 'package', 'installer')==false){
				$inst->removeDir();
				ArtaError::show(403, trans('YOU CANNOT INSTALL UPDATES'));
			}
			
			// show archive details on first step.
			if(getVar('showdetails', false, '', 'post')){
				echo trans('OBJECTS IN THIS ARCHIVE').': <br/>';
				if($inst->update==false){
					echo '<p><table class="admintable"><thead><tr><th>#</th><th>'.trans('TITLE').'</th><th>'.trans('NAME').'</th><th>'.trans('TYPE').'</th><th>'.trans('CLIENT').'</th><th>'.trans('VERSION').'</th></tr></thead><tbody>';
					$i=1;
					foreach($inst->xml->extension as $ext){
						echo '<tr class="row'.($i%2).'"><td align="center">'.$i.'</td><td>'.htmlspecialchars($ext->title).'</td><td>'.htmlspecialchars($ext->name).'</td><td align="center">'.trans($ext['type']).'</td><td align="center">'.trans($ext['client']=='*'?'BOTH':$ext['client']).'</td><td align="center">'.htmlspecialchars($ext->version).'</td></tr>';
						$i++;
					}
					echo '</tbody></table></p>';
				}else{
					$vers=array();
					foreach($inst->xml->update as $upd){
						$vers[]=$upd['ver'];
					}
					usort($vers, 'version_compare');
					echo '<p><b>'.trans('UPDATE TO VERSION').' '.array_pop($vers).'</b></p>';
				}
				echo '<br/><a href="index.php?pack=installer&task=install&localfile='.urlencode(ArtaFile::getRelatedPath($address, true)).$p.'">'.trans('CONTINUE').'</a><br/><a href="index.php?pack=installer">'.trans('STOP').'</a>';
				return;
				
			}
			
			// pass step if you are passing a multi step installation process.
			$step = getVar('step', false, '', 'int');
			if($step>0){
				$inst->step=$step;
			}
			
			// start.
			$res=$inst->install();
			
			
			if(($res!==true && is_string($res)) /*|| is_array($res)*/ || $inst->fully_installed===true){
				$inst->removeDir(); // remove dust if installation failed or completed successfully.
			}
			
			// populate data about recently installed extension.
			$installing = $inst->installing;
			if($inst->update==false){
				$detail=$installing['client'].' '.$installing['type'].': '.$installing['title'];
			}else{
				$detail='v'.$installing['ver'];
			}
			
			// one of extensions installed successfully and some others are pending
			if($inst->fully_installed==false AND $res===true){
				ArtaApplication::enqueueMessage(trans($inst->update?'UPDATED SUCC':'INSTALLED SUCC').' ('.$detail.')');
				
				echo '<div class="msgdiv">'.$inst->content.'</div><br/><a href="index.php?pack=installer&task=install&localfile='.urlencode(ArtaFile::getRelatedPath($address, true)).$p.'">'.trans('CONTINUE').' ('.count($inst->installed).'/'.$inst->todo.')</a>';
				$this->reportInstallation(array_merge($installing, array('update'=>$inst->update, 'current_version'=> @$inst->installer->extupdate ? $inst->installer->extupdate->current_version : '')));
			// dependencies needed	
			}elseif($inst->fully_installed==false AND is_array($res)){
				ArtaApplication::enqueueMessage(trans('DEPENDENCIES NEEDED'), 'warning');
				echo '<code style="font-size:125%">';
				foreach($res as $kdep=>$dep){
					foreach($dep as $ke => $d){
						echo ucfirst($d[1]).' '.ucfirst($d[2]).' &quot;'.$d[3].'&quot; ';
						if(is_file($inst->path.'/'.$d[4])){
							$fname = ArtaFile::getFilename($address);
							$fdest = ArtaFile::realpath($inst->path.'/../../installer_sources/'.substr($fname, 0, strlen($fname)-strlen(ArtaFile::getExt($fname))-1).'/'.$d[4]);
							
							if(!is_file($fdest)){
								ArtaFile::copy($inst->path.'/'.$d[4], $fdest);
							}
							
							echo '(<a target="_blank" href="index.php?pack=installer&task=install&localfile='.urlencode(ArtaFile::getRelatedPath($fdest, true)).'&dep=true">'.trans('INSTALL NOW').'</a>)';
						}
						if($ke != count($dep)-1){
							echo ' <b style="color: #991501">OR</b> ';
						}else{
							echo '<br/>';
						}
					}
				}
				echo '</code>';
				echo '<div class="msgdiv">'.$inst->content.'</div><br/><a href="index.php?pack=installer">'.trans('STOP').'</a>';
				echo '<br/><a href="index.php?pack=installer&task=install&localfile='.urlencode(ArtaFile::getRelatedPath($address, true)).$p.'">'.trans('RETRY').' ('.count($inst->installed).'/'.$inst->todo.')</a>';
			
				$inst->removeDir();
				
			// steps between installer.
			}elseif($inst->fully_installed==false AND !is_string($res) && $inst->step!=0){
				echo '<form method="post" action="index.php?pack=installer&task=install&localfile='.urlencode(ArtaFile::getRelatedPath($address, true)).$p.'&step='.($inst->step+1).'"><b>'.trans('STEPS OF INSTALLING').': '.$detail.'</b> <br/><br/><div class="msgdiv">'.$inst->content.'</div><br/><input type="submit" value="'.trans('NEXT').'"/></form>';// pass null on installer
			
			// starting steps
			}elseif($inst->fully_installed==false AND !is_string($res) && $inst->step==0){
				echo '<form method="post" id="install_form" action="index.php?pack=installer&task=install&localfile='.urlencode(ArtaFile::getRelatedPath($address, true)).$p.'&step=1">'.trans('STARTING INSTALLATION STEPS').'<br/><input type="submit" value="'.trans('CLICK HERE IF NOTHING HAPPENS').'"/></form><script>setTimeout("$(\'install_form\').submit()",1000);</script>';// pass null on installer
				
			// error occured during installation
			}elseif($inst->fully_installed==false AND is_string($res)){
				
				ArtaApplication::enqueueMessage(trans('ERROR').' ('.$detail.') : '.trans($res), 'error');
				if(in_array($res, array('ERROR_UNINSTALLING_FILES', 'ERROR_INSTALLING_FILES', 'ERROR_UPDATING_FILES'))){
					echo '<code>'.implode('<br/>',$inst->fileError).'</code><br/>';
				}
				echo '<div class="msgdiv">'.$inst->content.'</div><br/><a href="index.php?pack=installer">'.trans('STOP').'</a>';
				echo '<br/><a href="index.php?pack=installer&task=install&localfile='.urlencode(ArtaFile::getRelatedPath($address, true)).$p.'">'.trans('RETRY').' ('.count($inst->installed).'/'.$inst->todo.')</a>';
				$this->reportInstallation(array_merge($installing, array('update'=>$inst->update, 'current_version'=> @$inst->installer->extupdate ? $inst->installer->extupdate->current_version : '')), false, $res);
				
			// last extension installed and everything is done
			}elseif($inst->fully_installed==true){
				ArtaApplication::enqueueMessage(trans($inst->update?'UPDATED SUCC':'INSTALLED SUCC').' ('.$detail.')');
				ArtaApplication::enqueueMessage(trans($inst->update?'UPDATE COMPLETED':'INSTALLATION COMPLETED'));
				if(getVar('dep', null)){
					ArtaFile::unlink($inst->installed_log);
					ArtaApplication::enqueueMessage(trans('DEP SUCC U CAN RESUME INSTALLING'));
				}
				echo '<div class="msgdiv">'.$inst->content.'</div><br/>';
				echo '<a href="index.php?pack=installer">'.trans('CONTINUE').' ('.$inst->todo.'/'.$inst->todo.')</a>';
				if(isset($del) && $del===true){ // we should remove archive now
					ArtaFile::unlink($address);
					ArtaFile::unlink($inst->installed_log);
				}
				$this->reportInstallation(array_merge($installing, array('update'=>$inst->update, 'current_version'=> @$inst->installer->extupdate ? $inst->installer->extupdate->current_version : '')));
			}
			
		}else{
			ArtaError::show(403, trans('YOU CANNOT INSTALL EXTENSIONS'));
		}
	}
	
	private function reportInstallation($ext, $status=true, $error=null){
		if($ext['update']==false AND $ext['current_version']>=$ext['version']){
			return;
		}
		$config = ArtaLoader::Config();
		$xmlrpc = ArtaLoader::XMLRPC();
		$xmlrpc->Connect('/arta/logger.php', 'cc.artaproject.com');
		$xmlrpc->sendRequest((bool)$ext['update'] ? 'logger.logUpdate' : 'logger.logInstallation', 
				(bool)$ext['update']?
				array(
					(string)md5(md5($config->secret)), //unique
					(bool)$status, //successful
					(string)$error, //error
					(string)ArtaVersion::VERSION, //core_version
					(string)$ext['ver'] //ver_to
				)
				:
				array(
					(string)md5(md5($config->secret)), //unique
					(bool)$status, //successful
					(string)$error, //error
					(string)ArtaVersion::VERSION, //core_version
					(string)$ext['current_version'], //ver_from, in case of extupdate
					(string)$ext['version'], //ver_to
					(string)$ext['type']=='package'?'admin':(string)$ext['insertion_client'], //client
					(string)$ext['type'], //type
					(string)$ext['name'], //name
				)
				);
	}
	
	private function reportUnInstallation($ext, $status=true, $error=null){
		$config = ArtaLoader::Config();
		$xmlrpc = ArtaLoader::XMLRPC();
		$xmlrpc->Connect('/arta/logger.php', 'cc.artaproject.com');
		$xmlrpc->sendRequest('logger.logUnInstallation', 
				array(
					(string)md5(md5($config->secret)), //unique
					(bool)$status, //successful
					(string)$error, //error
					(string)ArtaVersion::VERSION, //core_version
					(string)$ext['version'], //version
					(string)$ext['type']=='package'?'admin':(string)$ext['insertion_client'], //client
					(string)$ext['type'], //type
					(string)$ext['name'], //name
				)
				);
	}
	
	function uninstall(){
		if(ArtaUsergroup::getPerm('can_uninstall_extensions', 'package', 'installer')){
			$i=ArtaRequest::getVars();
			$i['pid']=ArtaFilterinput::clean($i['pid'], 'string');
			$ex=explode('|',$i['pid']);
			if($ex==false){
				ArtaError::show();
			}
			array_shift($ex);
			$type=strtolower($ex[1]);
			$name=strtolower($ex[0]);
			$client=@strtolower($ex[2]);
			
			$insertion_client = $client;
			
			if($type=='package'){
				$insertion_client = 'admin';
			}elseif($type=='library'){
				$insertion_client = '*';
			}elseif($type=='cron'||$type=='webservice'||$type=='widget'){
				$insertion_client = 'site';
			}
			
			if(@$ex[2]){
				
			}elseif($type=='cron'||$type=='webservice'||$type=='widget'||$type=='library'){
				$client='site';
			}elseif($type=='package'){
				$client='admin';
			}
			if($client=='*'){
				$client='site';
			}
			if(@$ex[3]){
				$group=ArtaFilterinput::safeAddress(strtolower($ex[3]));
			}
			if($client!=='site' && $client!=='admin'){
				redirect('index.php?pack=installer');
			}
			if(!in_array($type, array('package'
			, 'module'
			, 'plugin'
			, 'cron'
			, 'webservice'
			, 'template'
			, 'language'
			, 'imageset'
			, 'widget'
			, 'library'))){
				redirect('index.php?pack=installer');
			}
			switch($type){
				case 'package':
				case 'module':
				case 'template':
				case 'language':
				case 'imageset':
					eval('$pth=ARTAPATH_'.strtoupper($client).'."/".$type."s/".$name;');
				break;
				case 'plugin':
					eval('$pth=ARTAPATH_'.strtoupper($client).'."/plugins/".$group;');
				break;
				case 'cron':
				case 'webservice':
				case 'widget':
					$pth=ARTAPATH_BASEDIR."/".$type.'s';
				break;
				case 'library':
					$pth=ARTAPATH_LIBRARY.'/external/'.$name.'/';
				break;
			}
			
			if($type=='package' && !is_file($pth."/".ArtaFilterinput::safeAddress(str_replace(
			array('/','\\',':','*','?','"','<','>','|'), '_',
			$name)).'.xml')){
				$pth=ARTAPATH_SITE.'/'.$type."s/".$name;
			}
			
			ArtaLoader::Import('#installer->installer');
			$inst = new ArtaInstaller;
			
			$res=$inst->init($pth.'/'.ArtaFilterinput::safeAddress(str_replace(
			array('/','\\',':','*','?','"','<','>','|'), '_',
			$name)).'.xml');
			
			if(!is_bool($res) || $res!==true){
				redirect('index.php?pack=installer', trans('ERROR').': '.trans($res), 'error');
			}
			
			$res=$inst->uninstall();
			$data=$inst->installing;
			
			$invalid=false;
			
			if(!is_bool($res)){
				$invalid=true;
				ArtaApplication::enqueueMessage(trans('ERROR').': '.trans($res).' ('.$data['title'].' - '.$data['client'].' - '.$data['type'].')', 'warning');
				$this->reportUnInstallation(array_merge($data, array('insertion_client'=>$insertion_client)), false, $res);
			}elseif($res==true){
				ArtaApplication::enqueueMessage(trans('UNINSTALLED SUCC').' ('.$data['title'].' - '.$data['client'].' - '.$data['type'].')');
				$this->reportUnInstallation(array_merge($data, array('insertion_client'=>$insertion_client)));
			}else{
				ArtaApplication::enqueueMessage(trans('UNKNOWN UNINST ERROR').' ('.$data['title'].' - '.$data['client'].' - '.$data['type'].')', 'warning');
				$this->reportUnInstallation(array_merge($data, array('insertion_client'=>$insertion_client)), false, '');
			}
			
			
			echo '<div class="msgdiv">'.$inst->content.'</div><br><a href="index.php?pack=installer">'.trans('CONTINUE').'</a>';
			
			if($invalid==true){
				ArtaApplication::enqueueMessage(trans('ERRORS OCCURRED'), 'error');
			}
		}else{
			redirect('index.php?pack=installer', trans('YOU CANNOT UNINSTALL EXTENSIONS'), 'error');
		}
	}
	
	function delete(){
		if(ArtaUsergroup::getPerm('can_install_extensions', 'package', 'installer')==false){
			ArtaError::show(403);
		}
		$file=getVar('file', '', '', 'string');
		if(is_file(ARTAPATH_BASEDIR.'/tmp/installer_sources/'.$file)==false){
			ArtaError::show(404, 'File not found to delete.');
		}
		ArtaFile::delete(ARTAPATH_BASEDIR.'/tmp/installer_sources/'.md5($file).'.ais');
		$dirname = substr($file, 0, strlen($file)-strlen(ArtaFile::getExt($file))-1);
		if(is_dir(ARTAPATH_BASEDIR.'/tmp/installer_sources/'.$dirname)){
			ArtaFile::rmdir_extra(ARTAPATH_BASEDIR.'/tmp/installer_sources/'.$dirname);
		}
		if(is_dir(ARTAPATH_BASEDIR.'/tmp/installer/'.$dirname)){
			ArtaFile::rmdir_extra(ARTAPATH_BASEDIR.'/tmp/installer/'.$dirname);
		}
		
		if(ArtaFile::delete(ARTAPATH_BASEDIR.'/tmp/installer_sources/'.$file)){
			redirect('index.php?pack=installer', trans('DELETED SUCC'));
		}else{
			ArtaError::show(500, 'Could not delete file.');
		}
	}
	
	function activate(){
		if(!ArtaUsergroup::getPerm('can_change_extensions_activity', 'package', 'installer')){
			ArtaError::show(403);
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
		$i=ArtaRequest::getVars();
		$i['pid']=ArtaFilterinput::clean($i['pid'], 'string');
		$ex=explode('|',$i['pid']);
		if($ex==false){
			ArtaError::show();
		}
		array_shift($ex);
		$type=strtolower($ex[1]);
		$name=strtolower($ex[0]);
		if(@$ex[2]){
			$client=strtolower($ex[2]);
		}elseif($type=='cron'||$type=='webservice'){
			$client='site';
		}elseif($type=='package'){
			$client='admin';
		}
		if(@$ex[3]){
			$group=strtolower($ex[3]);
		}
		if($client!=='site' && $client!=='admin' && $client!=='*'){
			ArtaError::show(500, false, 'index.php?pack=installer');
		}
		if(!in_array($type, array('package'
		, 'plugin'
		, 'cron'
		, 'webservice'))){
			ArtaError::show(500, false, 'index.php?pack=installer');
		}
		$db=ArtaLoader::DB();
		switch($type){
			case 'package':
				$db->setQuery('UPDATE #__packages SET enabled=1 WHERE name='.$db->Quote($name), array('enabled'));
			break;
			case 'plugin':
				$db->setQuery('UPDATE #__plugins SET enabled=1 WHERE plugin='.$db->Quote($name).' AND `group`='.$db->Quote($group), array('enabled'));
			break;
			case 'cron':
			case 'webservice':
				$db->setQuery('UPDATE #__'.$type.'s SET enabled=1 WHERE '.$type.'='.$db->Quote($name), array('enabled'));
			break;
		}
		if(!$db->query()){
			ArtaError::Show(500);
		}
	}
	
	function deactivate(){
		if(!ArtaUsergroup::getPerm('can_change_extensions_activity', 'package', 'installer')){
			ArtaError::show(403);
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
		$i=ArtaRequest::getVars();
		$i['pid']=ArtaFilterinput::clean($i['pid'], 'string');
		$ex=explode('|',$i['pid']);
		if($ex==false){
			ArtaError::show();
		}
		array_shift($ex);
		$type=strtolower($ex[1]);
		$name=strtolower($ex[0]);
		if(@$ex[2]){
			$client=strtolower($ex[2]);
		}elseif($type=='cron'||$type=='webservice'){
			$client='site';
		}elseif($type=='package'){
			$client='admin';
		}
		if(@$ex[3]){
			$group=strtolower($ex[3]);
		}
		if($client!=='site' && $client!=='admin' && $client!=='*'){
			ArtaError::show(500, false, 'index.php?pack=installer');
		}
		if(!in_array($type, array('package'
		, 'plugin'
		, 'cron'
		, 'webservice'))){
			ArtaError::show(500, false, 'index.php?pack=installer');
		}
		$db=ArtaLoader::DB();
		switch($type){
			case 'package':
				$db->setQuery('UPDATE #__packages SET enabled=0 WHERE name='.$db->Quote($name), array('enabled'));
			break;
			case 'plugin':
				$db->setQuery('UPDATE #__plugins SET enabled=0 WHERE plugin='.$db->Quote($name).' AND `group`='.$db->Quote($group).' AND `client`='.$db->Quote($client), array('enabled'));
			break;
			case 'cron':
			case 'webservice':
				$db->setQuery('UPDATE #__'.$type.'s SET enabled=0 WHERE '.$type.'='.$db->Quote($name), array('enabled'));
			break;
		}
		if(!$db->query()){
			ArtaError::Show(500);
		}
	}
	
	function saveM(){
		$i=ArtaRequest::getVars();
		$i['pid']=ArtaFilterinput::clean($i['pid'], 'string');
		$ex=explode('|',$i['pid']);
		if($ex==false){
			ArtaError::show();
		}
		array_shift($ex);
		$type=strtolower($ex[1]);
		$name=strtolower($ex[0]);
		if(@$ex[2]){
			$client=strtolower($ex[2]);
		}elseif($type=='cron'||$type=='webservice'){
			$client='site';
		}elseif($type=='package'){
			$client='admin';
		}
		if(@$ex[3]){
			$group=strtolower($ex[3]);
		}
		if($client!=='site' && $client!=='admin' && $client!=='*'){
			ArtaError::show(500, false, 'index.php?pack=installer&view=perm_editor&tmpl=package&pid='.urlencode($i['pid']));
		}
		if(!in_array($type, array('package', 'plugin'))){
			ArtaError::show(500, false, 'index.php?pack=installer&view=perm_editor&tmpl=package&pid='.urlencode($i['pid']));
		}
		if(!ArtaUsergroup::getPerm('can_change_'.$type.'s_perms', 'package', 'installer')){
			ArtaError::show(403);
		}
		$denied= $i['denied_type']==1 ? '-'.implode(',',$i['denied']) : implode(',',$i['denied']);
		$db=ArtaLoader::DB();
		switch($type){
			case 'package':
				$db->setQuery('UPDATE #__packages SET denied='.$db->Quote($denied).' WHERE name='.$db->Quote($name), array('denied'));
			break;
			case 'plugin':
				$db->setQuery('UPDATE #__plugins SET denied='.$db->Quote($denied).' WHERE plugin='.$db->Quote($name).' AND `group`='.$db->Quote($group).' AND `client`='.$db->Quote($client), array('denied'));
			break;
		}
		if(!$db->query()){
			ArtaError::Show(500);
		}else{
			redirect('index.php?pack=installer&view=perm_editor&tmpl=package&done=1&pid='.urlencode($i['pid']), trans('PERMS SAVED SUCC YOU CAN CLOSE'));
		}
	}
	
	function saveTimings(){
		$i=ArtaRequest::getVars();
		$i['pid']=ArtaFilterinput::clean($i['pid'], 'string');
		$ex=explode('|',$i['pid']);
		if($ex==false){
			ArtaError::show();
		}
		array_shift($ex);
		$type=strtolower($ex[1]);
		$name=strtolower($ex[0]);
		if(!in_array($type, array('cron'))){
			ArtaError::show(500, false, 'index.php?pack=installer&view=cron_editor&tmpl=package&pid='.urlencode($i['pid']));
		}
		if(!ArtaUsergroup::getPerm('can_change_crons_timings', 'package', 'installer')){
			ArtaError::show(403);
		}
		$nextrun=getVar('nextrun','', 'post', 'string');
		if(trim($nextrun)==''){
			$nextrun=9999999999;
		}else{
			$nextrun=ArtaDate::convertInput($nextrun, true);
		}
		if($nextrun==false){
			ArtaError::show(400);
		}
		$runloop=getVar('runloop','', 'post', 'int');
		if(!is_numeric($runloop)){
			$runloop=0;
		}else{
			$runloop =round((float)$runloop*3600);
		}
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__crons SET nextrun='.$db->Quote(strtotime($nextrun)).', runloop='.$db->Quote($runloop).' WHERE cron='.$db->Quote($name), array('nextrun','runloop'));
		if(!$db->query()){
			ArtaError::Show(500);
		}else{
			redirect('index.php?pack=installer&view=cron_editor&tmpl=package&done=1&pid='.urlencode($i['pid']), trans('TIMINGS SAVED SUCC YOU CAN CLOSE'));
		}
	}
	
		
}
?>
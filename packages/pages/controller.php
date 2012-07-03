<?php 
if(!defined('ARTA_VALID')){die('No access');}
class PagesController extends ArtaPackageController{

	function display(){
		$viewname = getVar('view', 'page', '', 'string');
		$type=getVar('type', 'html', '', 'string');
		$view = $this->getView($viewname,$type);
		$view->display();
	}
	
	function savePage(){
		if(ArtaUsergroup::getPerm('can_addedit_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT PAGES'));
		}
		// GET DATA FROM $_POST
		$vars=ArtaRequest::getVars('post');

		// Check data and some definitions
		$vars=ArtaUtility::array_extend($vars, array('title'=>'','sef_alias'=>'', 'desc'=>'', 'tags'=>'', 'height'=>"600",'width'=>"0", 'enabled'=>'0', 'page_template'=>'-', 'mods'=>array(), 'deny_type'=>0, 'denied'=>array(), 'denied_type'=>0, 'is_dynamic'=>0, 'content'=>''));
		$vars=ArtaFilterinput::clean($vars,array('title'=>'string','sef_alias'=>'string','desc'=>'string','height'=>'int','width'=>"int", 'enabled'=>'bool', 'page_template'=>'string', 'mods'=>'array', 'deny_type'=>'bool', 'denied_type'=>'bool', 'is_dynamic'=>'bool', 'content'=>'safe-html'));
		if($vars['pid']){
			$vars['pid']=ArtaFilterinput::clean($vars['pid'],'int');
			if($vars['pid']>0){
				$id=$vars['pid'];
				$q='index.php?pack=pages&view=new&pid='.$id;
			}
		}
				
		$vars=ArtaFilterinput::trim($vars);
		$vars=ArtaFilterinput::array_limit($vars,array('title'=>255,'sef_alias'=>255,'tags'=>255));
		
		if($vars['sef_alias']==''){
			$vars['sef_alias']=ArtaFilteroutput::stringURLSafe($vars['title']);
		}else{
			$vars['sef_alias']=ArtaFilteroutput::stringURLSafe($vars['sef_alias']);
		}
		
		if(!isset($q)){
			$q='index.php?pack=pages&view=new';
		}
		if(strlen($vars['title'])==0){
			redirect($q, trans('INVALID TITLE'),'error');
		}
		
		if($vars['width']=='0'){
			$vars['width']='auto';
		}else{
			$vars['width'].='px';
		}
				

		$params=serialize(array('height'=>$vars['height'].'px', 'width'=>$vars['width'], 'template'=>$vars['page_template']));
		$vars['mods']=implode(',',$vars['mods']);
		if($vars['deny_type']==true){
			$vars['mods']='-'.$vars['mods'];
		}
		
		$vars['denied']=implode(',',$vars['denied']);
		if($vars['denied_type']==true){
			$vars['denied']='-'.$vars['denied'];
		}
		
		$u=$this->getCurrentUser();
		$db=ArtaLoader::DB();
		
		// Start DB Updating
		
		if(@$id){
			$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($id));
			$row=$db->loadObject();
			if(@$row->id!=$id){
				ArtaError::show(404, trans('PAGE NOT FOUND'),$q);
			}
			if($row->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
				ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
			}
			
			if($vars['sef_alias']!==$row->sef_alias){
				ArtaCache::clearData('pages','sef_aliases');
			}
			
			if(ArtaUsergroup::getPerm('can_change_pages_activity', 'package', 'pages')==false && $vars['enabled']!=$row->enabled){
				$vars['enabled']=$row->enabled;
				$deact=$row->enabled;
			}
			
			if($vars['is_dynamic']==true){
				$vars['content']='';
			}else{
				$db->setQuery('DELETE FROM #__pages_widgets WHERE pageid='.$row->id);
				$db->query();
			}
			
			
			$db->setQuery('UPDATE `#__pages` SET `title`='.$db->Quote($vars['title']).
			', `sef_alias`='.$db->Quote($vars['sef_alias']).
			', `desc`='.$db->Quote($vars['desc']).
			', `tags`='.$db->Quote($vars['tags']).
			', `is_dynamic`='.$db->Quote($vars['is_dynamic']).
			', `content`='.$db->Quote($vars['content']).
			', `mods`='.$db->Quote($vars['mods']).
			', `enabled`='.$db->Quote((int)$vars['enabled']).
			', `denied`='.$db->Quote($vars['denied']).
			', `params`='.$db->Quote($params).' WHERE `id`='.$id
			, true, array('pages,sef_aliases'));
		}else{
			if(ArtaUsergroup::getPerm('can_change_pages_activity', 'package', 'pages')==false){
				$vars['enabled']=0;
				$deact=0;
			}
			
			if($vars['is_dynamic']==true){
				$vars['content']='';
			}
			
			$db->setQuery('INSERT INTO `#__pages` VALUES (NULL,'.$db->Quote($vars['title']).
			','.$db->Quote($vars['sef_alias']).
			','.$db->Quote($vars['desc']).
			','.$db->Quote($vars['tags']).
			','.$db->Quote($vars['is_dynamic']).
			','.$db->Quote($vars['content']).
			','.$db->Quote($vars['mods']).
			','.$db->Quote((int)$vars['enabled']).
			','.$db->Quote($vars['denied']).
			','.$db->Quote($u->id).
			','.$db->Quote($params).')'
			);
		}
		
		$r=$db->query();
		if($r==true){
			if(isset($deact)){
				ArtaApplication::enqueueMessage(sprintf(trans('SAVED BUT ACTIVATION SET TO _ BECAUSE PERMS'), (int)$deact));
			}
			if(@$id==false){
				$db->setQuery('SELECT LAST_INSERT_ID();');
				$id=$db->loadResult();
				$q.='&pid='.$id;
			}
			redirect($q, trans('SAVED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'));
		}
	}
	
	
	function openenv(){
		$pid=getVar('pid', null, null, 'int');
		if((int)$pid>0){
			setcookie('arta_environment_editing_pageid', (string)$pid, time()+3600, '/');
		}
		
		redirect('index.php?pack=pages&view=environment');
	}
	
	function closeenv(){
		$n=getVar('arta_environment_editing_pageid', null, 'cookie');
		setcookie('arta_environment_editing_pageid', 0, time()-86400, '/');
		$display=$this->getView('response', 'xml');
		$display->display();
		echo '<response><![CDATA['.makeURL('index.php?pack=pages&pid='.$n).']]></response>';
	}
	
	
	function saveWidget(){
		if(ArtaUsergroup::getPerm('can_addedit_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT PAGES'));
		}
		$vars=ArtaRequest::getVars('post');
		$vars=ArtaUtility::array_extend($vars, array('title'=>'', 'content'=>'', 'params'=>array(), 'settings'=>array()));
		$vars['params']=ArtaUtility::array_extend($vars['params'], array('width'=>'200px','height'=>'200px','top'=>mt_rand(100,200).'px','left'=>mt_rand(100,200).'px','other'=>''));
		$alpha=array_merge(range('a','z'), range('A','Z'), array('%'));
		foreach (array('width'=>'200px','height'=>'200px','top'=>mt_rand(100,200).'px','left'=>mt_rand(100,200).'px') as $k=>$v){
			if((string)$vars['params'][$k]==''){
				$vars['params'][$k]=$v;
			}
		}
		foreach(array('width', 'height', 'top', 'left') as $name){
			$vars['params'][$name]=trim($vars['params'][$name], ";");
			if(!in_array(substr($vars['params'][$name], -1,1), $alpha)){
				$vars['params'][$name]=$vars['params'][$name].'px';
			}
		}
		if($vars['params']['width']=='0'){
			$vars['params']['width']='auto';
		}
		
		$db=Artaloader::db();
		$vars['pid']=ArtaFilterinput::clean($vars['pid'], 'int');
		$vars['content']=ArtaFilterinput::clean($vars['content'], 'safe-html');
		if(isset($vars['widget'])){
			$vars['widget']=ArtaFilterinput::clean($vars['widget'], 'int');
			if($vars['widget']<0){
				$vars['widget']=0;
			}
		}else{
			$vars['widget']=0;
		}
		
		$u=$this->getCurrentUser();
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($vars['pid']));
		$p=$db->loadObject();
		if($p==null){
			ArtaError::show(404);
		}
		
		if($p->is_dynamic==false){
			ArtaError::show(401, 'It is a static page!');
		}
		
		if($p->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
		}
		
		if(strtolower($vars['params']['width'])=='auto' AND 
			strpos($vars['params']['other'], 'max-width')===false){
			$pp=unserialize($p->params);
			if(strtolower($pp['width'])!='auto'){
				$vars['params']['other']='max-width: '.($pp['width']);
			}
		}
		
		if($vars['widget']>0){
			$vars['content']='';
			$db->setQuery('SELECT * FROM #__pages_widgets_resource WHERE id='.$db->Quote($vars['widget']));
			$w=$db->loadObject();
			if($w==null){
				ArtaError::show();
			}
			
			
			$db->setQuery('SELECT `var`,`check` FROM #__settings WHERE extype=\'widget\' AND `check`!=\'\' AND `check` IS NOT NULL AND extname='.$db->Quote($w->filename));
			$r= $db->loadObjectList();
			if(!$r==null){
				$language=ArtaLoader::Language();
				$language->addtoNeed($w->filename, 'widget');
				$r=ArtaUtility::keyByChild($r, 'var');//var_dump($r);
				foreach($vars['settings'] as $k=>$v){
					$value=$v;
					$error=null;
					if(isset($r[$k])){
						$this->checkWidget($r[$k], $value, $error);
					}
					$vars['settings'][$k]=$value;
					$v=$value;
					if($error!=null){
						ArtaError::show(500, $error);
					}
				}
				
			}
			$vars['params']['settings']=$vars['settings'];
			
		}
		
		if(isset($vars['id'])){
			$vars['id']=ArtaFilterinput::clean($vars['id'], 'int');
		}
		if(@$vars['id']>0){
			
			
			$db->setQuery('SELECT * FROM #__pages_widgets WHERE id='.$db->Quote($vars['id']));
			$wid=$db->loadObject();
			if($wid==null){
				ArtaError::show(404);
			}
			
			
			$db->setQuery('UPDATE #__pages_widgets SET '
			.'`title`='.$db->Quote($vars['title'])
			.',`content`='.$db->Quote($vars['content'])
			.',`widget`='.$db->Quote($vars['widget'])
			.',`params`='.$db->Quote(serialize($vars['params'])).' WHERE id='.$wid->id);
			$res=$db->Query();
			if($res){
				echo '<br><br><br><center><input type="button" value="'.trans('ok').'" onclick="window.opener.updateWidget(\'widget_'.$wid->id.'\', \''.$p->id.'\');"></center>';
			}
		}else{

			$db->setQuery('INSERT INTO #__pages_widgets VALUES (NULL'
			.','.$db->Quote($vars['title'])
			.','.$db->Quote($vars['content'])
			.','.$db->Quote($vars['widget'])
			.','.$db->Quote($p->id)
			.','.$db->Quote(serialize($vars['params'])).')'
			);
			$res=$db->Query();
			if($res){
				$db->setQuery('SELECT LAST_INSERT_ID();');
				$wid=$db->loadResult();
				echo '<br><br><br><center><input type="button" value="'.trans('ok').'" onclick="window.opener.addWidget(\'widget_'.$wid.'\', \''.$p->id.'\');"></center>';
			}
		}
		if(!$res){
			ArtaError::show('500', trans('ERROR IN DB'));
		}else{
			ArtaApplication::enqueueMessage(trans('WIDGET SAVED SUCC'));
		}
		
	}
	
	private function checkWidget($row, &$value, &$error){
		eval($row->check);
	}
	
	function getWidget(){
		if(ArtaUsergroup::getPerm('can_addedit_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT PAGES'));
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(400);
		}
		$plug=ArtaLoader::Plugin();
		$id=getVar('id', '','','string');
		$pid=getvar('pid',null, null, 'int');
		if(strlen($id)<7 OR $pid==null){
			ArtaError::show(404);
		}
		$id=substr($id,7);
		$id=ArtaFilterinput::clean($id, 'int');
		$u=$this->getCurrentUser();
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$pid);
		$p=$db->loadObject();
		if($p==null){
			ArtaError::show(404);
		}
		
		if($p->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
		}
		$db->setQuery('SELECT * FROM #__pages_widgets WHERE id='.$id);
		$wid=$db->loadObject();
		if($wid==null){
			ArtaError::show(404);
		}
		if($wid->pageid!==$p->id){
			ArtaError::show(404);
		}
		$params=unserialize($wid->params);
		
		if($wid->widget>0){
			$db->setQuery('SELECT * FROM #__pages_widgets_resource WHERE id='.$wid->widget);
			$rwid=$db->loadObject();
			if($rwid!==null){
				$settings=$params['settings'];
				$db->setQuery('SELECT * FROM #__settings WHERE extype=\'widget\' AND extname='.$db->Quote($rwid->filename));
				$return= (array)$db->loadObjectList();
				$x=array();
				foreach($return as $k=>$v){
					$x[$v->var]=$v->value;
				}
				$settings=ArtaUtility::array_extend($settings, $x);
				$language=ArtaLoader::Language();
				$language->addtoNeed($rwid->filename, 'widget');
				ob_start();
				$this->includeFile($wid, $settings, ARTAPATH_BASEDIR.'/widgets/'.$rwid->filename.'.php');
				$wid->content=ob_get_contents();
				ob_end_clean();
			}else{
				$plug->trigger('onShowBody', array(&$wid->content, 'widget'));
			}
		}else{
			$plug->trigger('onShowBody', array(&$wid->content, 'widget'));
		}
		
		$display=$this->getView('response', 'xml');
		$display->display();
		echo '<response>';
		if(trim($wid->title)!=''){
			$title='<tr><td class="widget_title">'.htmlspecialchars(htmlspecialchars($wid->title)).'</td></tr>';
		}else{
			$title='';
		}
		$wid->content=str_replace('<a ', '<a onclick="return false;" ', $wid->content); // stop content links inside edit env
		echo '<inner><![CDATA[<table>'.$title.'<tr><td class="widget_content">'.$wid->content.'</td></tr></table><span class="resizeHandle" style="position:absolute;right:0px;bottom:0px;"></span>]]></inner>';
		echo "\n".'<inner><![CDATA[width:'.$params['width'].'; height:'.$params['height'].'; position:absolute; top:'.$params['top'].'; left: '.$params['left'].';'.@htmlspecialchars($params['other']).']]></inner>';
		echo '</response>';
		
	}
	
	private function includeFile($wid, $settings){
		include func_get_arg(2);
	}
	
	function delWidget(){
		if(ArtaSession::checkToken()==false){
			ArtaError::show(400);
		}
		if(ArtaUsergroup::getPerm('can_addedit_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT PAGES'));
		}
		$id=getVar('id', '', '', 'string');
		$pid=getvar('pid',null, null, 'int');
		if(strlen($id)<7 OR $pid==null){
			ArtaError::show(404);
		}
		$id=substr($id,7);
		$id=ArtaFilterinput::clean($id, 'int');
		$u=$this->getCurrentUser();
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$pid);
		$p=$db->loadObject();
		if($p==null){
			ArtaError::show(404);
		}
		if($p->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
		}
		$db->setQuery('SELECT * FROM #__pages_widgets WHERE id='.$id);
		$wid=$db->loadObject();
		if($wid==null){
			ArtaError::show(404);
		}
		if($wid->pageid!==$p->id){
			ArtaError::show(404);
		}
		$db->setQuery('DELETE FROM #__pages_widgets WHERE id='.$wid->id);
		if($db->query()==false){
			ArtaError::show(500);
		}else{
			$this->setDoctype('xml');
			echo '<response>1</response>';
		}
	}
	
	function AjaxsaveWidget(){
		if(ArtaSession::checkToken()==false){
			ArtaError::show(400);
		}
		if(ArtaUsergroup::getPerm('can_addedit_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT PAGES'));
		}
		$id=getVar('id','','','string');
		$pid=getvar('pid',null, null, 'int');
		if(strlen($id)<7 OR $pid==null){
			ArtaError::show(404);
		}
		$id=substr($id,7);
		$id=ArtaFilterinput::clean($id, 'int');
		$u=$this->getCurrentUser();
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($pid));
		$p=$db->loadObject();
		if($p==null){
			ArtaError::show(404);
		}
		if($p->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
		}
		$db->setQuery('SELECT * FROM #__pages_widgets WHERE id='.$db->Quote($id));
		$wid=$db->loadObject();
		if($wid==null){
			ArtaError::show(404);
		}
		
		if($wid->pageid!==$p->id){
			ArtaError::show(404);
		}
		
		if($p->is_dynamic==false){
			ArtaError::show(401, 'It is a static page!');
		}
		
		$xy=base64_decode(getVar('data', '','','string'));
		$xy=explode('|',$xy);
		$wid->params=unserialize($wid->params);
		$wid->params['left']=ArtaFilterinput::clean($xy[1], 'int').'px';
		$wid->params['top']=ArtaFilterinput::clean($xy[0], 'int').'px';
		$wid->params['width']=ArtaFilterinput::clean($xy[2], 'int').'px';
		$wid->params['height']=ArtaFilterinput::clean($xy[3], 'int').'px';
		$wid->params=serialize($wid->params);
		$db->setQuery('UPDATE #__pages_widgets SET `params`='.$db->Quote($wid->params).' WHERE id='.$wid->id, array('params'));
		if($db->query()==false){
			ArtaError::show(500);
		}else{
			$this->setDoctype('xml');
			echo '<response>1</response>';
		}
	}
	
	function AjaxsavePage(){
		if(ArtaSession::checkToken()==false){
			ArtaError::show(400);
		}
		if(ArtaUsergroup::getPerm('can_addedit_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT PAGES'));
		}
		$pid=getvar('pid',null, null, 'int');
		if($pid==null){
			ArtaError::show(404);
		}
		$u=$this->getCurrentUser();
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__pages WHERE id='.$db->Quote($pid));
		$p=$db->loadObject();
		if($p==null){
			ArtaError::show(404);
		}
		
		if($p->is_dynamic==false){
			ArtaError::show(401, 'It is a static page!');
		}
		
		if($p->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_pages', 'package', 'pages')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS PAGES'));
		}
		$xy=base64_decode(getVar('data', '', '', 'string'));
		$xy=explode('|',$xy);
		$p->params=unserialize($p->params);
		$p->params['width']=ArtaFilterinput::clean($xy[0], 'int').'px';
		$p->params['height']=ArtaFilterinput::clean($xy[1], 'int').'px';
		$p->params['canvasAlign']= in_array(strtolower($xy[2]), array('left','right','center')) ?  strtolower($xy[2]) : '';
		if($p->params['width']=='0px'){
			$p->params['width']='auto';
		}
		$p->params=serialize($p->params);
		$db->setQuery('UPDATE #__pages SET `params`='.$db->Quote($p->params).' WHERE id='.$p->id, array('params'));
		if($db->query()==false){
			ArtaError::show(500);
		}else{
			$this->setDoctype('xml');
			echo '<response>1</response>';
		}
	}

}
?>

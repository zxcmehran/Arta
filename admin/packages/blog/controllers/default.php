<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogControllerDefault extends ArtaPackageController{
	
	function display(){
		$view=$this->getView(getVar('view', 'posts', '', 'string'), getVar('type', 'html', '', 'string'));
		ArtaAdminTabs::addTab(trans('BLOG POSTS MANAGER'), 'index.php?pack=blog');
		ArtaAdminTabs::addTab(trans('BLOG CAT MANAGER'), 'index.php?pack=blog&view=category');
		$view->display();
	}

	function save(){
		if(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT BLOG POSTS'));
		}
		
		$vz=ArtaRequest::getVars('post');
		if(@$vz['id']){$id='&ids[]='.ArtaFilterinput::clean($vz['id'], 'int');}else{
			$id='';
		}
		
		//<Data processing>
		
			// 1.Check Existence
			if(!ArtaUtility::keysExists(
				array('title',
				'sef_alias',
				'content',
				'enabled',
				'denied_type',
				'blogid',
				'added_time',
				'mod_time',
				'pub_time',
				'unpub_time',
				'hits',
				'tags')
			,$vz)){
				
				redirect('index.php?pack=blog&view=new'.$id, trans('FORM ISNT COMPLETE'), 'warning');
			}
			
			// 2.Trim Input and Cut them
			$vz=ArtaFilterinput::trim($vz);
			$vz=ArtaFilterinput::array_limit($vz, array('title'=>255,'sef_alias'=>255));
			
			// 3.Extend input
			$vz=ArtaUtility::array_extend($vz, array('denied'=>array(), 'id'=>0, 'att'=>array()));
			
			// 4.Clean input
			$vz=ArtaFilterinput::clean($vz, array('title'=>'string',
			'sef_alias'=>'string',
			'content'=>'safe-html',
			'enabled'=>'bool',
			'denied'=>'array',
			'denied_type'=>'bool',
			'blogid'=>'int',
			'added_time'=>'datetime',
			'mod_time'=>'datetime',
			'pub_time'=>'datetime',
			'unpub_time'=>'datetime',
			'hits'=>'int',
			'tags'=>'string',
			'id'=>'int',
			'att'=>'array'
			));
			
			if($vz['added_time']==false){
				redirect('index.php?pack=blog&view=new'.$id, trans('INVALID ADDED_TIME VALUE'),'warning');
			}
			if($vz['mod_time']==false && $vz['id']>0){
				$vz['mod_time']=ArtaDate::toMySQL(time());
			}
			if($vz['pub_time']==false){
				$vz['pub_time']=$vz['added_time'];
			}
			
			foreach($vz['denied'] as $k=>$kk){
				$kk=ArtaFilterinput::clean($kk, 'int');
				if($kk<0){
					unset($vz['denied'][$k]);
				}else{
					$vz['denied'][$k]=$kk;
				}
			}
			
			// 5.Check lengths
			if($vz['title']==''){
				redirect('index.php?pack=blog&view=new'.$id, trans('NO POST TITLE SPECIFIED'),'warning');
			}
			if($vz['sef_alias']==''){
				$vz['sef_alias']=ArtaFilteroutput::stringURLSafe($vz['title']);
			}else{
				$vz['sef_alias']=ArtaFilteroutput::stringURLSafe($vz['sef_alias']);
			}
			
			// 6.Make data ready
			if($pos=strpos($vz['content'], '<hr id="readmore_handler" />')){
				$vz['introcontent']=substr($vz['content'], 0, $pos);
				$vz['morecontent']=substr($vz['content'], $pos+28);
			}else{
				$vz['introcontent']=$vz['content'];
				$vz['morecontent']='';
			}
			$vz['denied_type']= $vz['denied_type'] ? '-' : '';
			$vz['denied']=$vz['denied_type'].implode(',',$vz['denied']);
			
			
			
			if($vz['hits']<=0){
				// now its ONLY >0.
				$vz['hits']=0;
			}
		
		// </Data processing>
		
		// Maybe you can not publish posts...
		if(ArtaUsergroup::getPerm('can_publish_posts', 'package', 'blog')==false && $vz['enabled']==true){
			$vz['enabled']=false;
			$forced_unpub=true;
		}
		
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__blogcategories WHERE id='.$db->Quote($vz['blogid']));
		$crow=$db->loadObject();
		if($crow==null){
			if(@$vz['id']){$id='&ids[]='.$vz['id'];}else{
				$id='';
			}
			ArtaError::show(404, trans('CATEGORY NOT FOUND'), 'index.php?pack=blog&view=new'.$id);				
		}
		
		$db->die=true;
		
		$tags=explode(',', $vz['tags']);
		$tags=array_map('trim',$tags);
		$tags=array_map(array('ArtaFilterinput', 'clean'),$tags, array_fill(0,count($tags), 'string'));
		foreach($tags as $k=>$v){
			if($v==''){
				unset($tags[$k]);
			}
		}
		$vz['tags']=implode(',',$tags);
		
		// Editing or adding ?
		if(isset($vz['id'])&&$vz['id']>0){
			// Editing
			$db->setQuery('SELECT * FROM #__blogposts WHERE id='.$db->Quote($vz['id']));
			$row=$db->loadObject();
			
			if($row==null){
				ArtaError::show(404, trans('INVALID POST TO EDIT'), 'index.php?pack=blog&view=new');
			}
			
			if($vz['tags']!==$row->tags){
				ArtaCache::clearData('blog', 'new_tags');
			}
			
			if($vz['sef_alias']!==$row->sef_alias){
				ArtaCache::clearData('blog','sef_aliases');
			}
			
			$u=$this->getCurrentUser();
			if($row->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog')==false){
				ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS BLOG POSTS'));
			}
			
			if($vz['mod_time']!==false){
				$u=ArtaLoader::User();
				$u=$u->getCurrentUser();
				$vz['mod_by']=$db->Quote($u->id);
				$vz['mod_time']=$db->Quote($vz['mod_time']);
			}else{
				$vz['mod_by']='NULL';
				$vz['mod_time']='NULL';
			}
			
			if($vz['unpub_time']!==false){
				$vz['unpub_time']=$db->Quote($vz['unpub_time']);
			}else{
				$vz['unpub_time']='NULL';
			}
			
			$db->setQuery('UPDATE #__blogposts SET '.
				'`title`='.$db->Quote($vz['title']).','.
				'`sef_alias`='.$db->Quote($vz['sef_alias']).','.
				'`introcontent`='.$db->Quote($vz['introcontent']).','.
				'`morecontent`='.$db->Quote($vz['morecontent']).','.
				'`enabled`='.$db->Quote($vz['enabled']).','.
				'`denied`='.$db->Quote($vz['denied']).','.
				'`blogid`='.$db->Quote($vz['blogid']).','.
				'`added_time`='.$db->Quote($vz['added_time']).','.
				'`mod_time`='.$vz['mod_time'].','.
				'`mod_by`='.$vz['mod_by'].','.
				'`pub_time`='.$db->Quote($vz['pub_time']).','.
				'`unpub_time`='.$vz['unpub_time'].','.
				'`hits`='.$db->Quote($vz['hits']).','.
				'`tags`='.$db->Quote($vz['tags']).
				' WHERE id='.$db->Quote($vz['id'])
			,true,array('blog,new_tags', 'blog,sef_aliases'));
			
			if($db->query()){
				
				$id=$vz['id'];
				$ins=array();
				
				foreach($vz['att'] as $atk => $at){
					$ins[]='('.$db->Quote($id).', '.$db->Quote($atk).', '.$db->Quote($at).')';
				}
				
				$db->setQuery('DELETE FROM #__blog_attachments WHERE postid='.$db->Quote($id));
				$db->query();
				if(count($ins)){
					$db->setQuery('INSERT INTO #__blog_attachments VALUES '.implode(',', $ins));
					$db->query();
				}
				$msg=trans('saved succ');
				if(isset($forced_unpub)){
					ArtaApplication::enqueueMessage(trans('ADDED BUT UNPUBLISHED'));
				}
				redirect('index.php?pack=blog', $msg);
			}else{				
				ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog&view=new&ids[]='.$vz['id']);
			}
			
		}else{
			// Adding
			$u=ArtaLoader::User();
			$u=$u->getCurrentUser();
			if($vz['mod_time']!==false){
				$vz['mod_by']=$u->id;
			}else{
				$vz['mod_by']='';
			}
			$db->setQuery('INSERT INTO #__blogposts '.
				'(`title`, `sef_alias`, `introcontent`, `morecontent`, `enabled`, `denied`, `blogid`, `added_time`, `mod_time`, `mod_by`, `pub_time`, `unpub_time`, `hits`, `tags`, `added_by`)'.
				' VALUES ('.
				$db->Quote($vz['title']).','.
				$db->Quote($vz['sef_alias']).','.
				$db->Quote($vz['introcontent']).','.
				$db->Quote($vz['morecontent']).','.
				$db->Quote($vz['enabled']).','.
				$db->Quote($vz['denied']).','.
				$db->Quote($vz['blogid']).','.
				$db->Quote($vz['added_time']).','.
				$db->Quote($vz['mod_time']).','.
				$db->Quote($vz['mod_by']).','.
				$db->Quote($vz['pub_time']).','.
				$db->Quote($vz['unpub_time']).','.
				$db->Quote($vz['hits']).','.
				$db->Quote($vz['tags']).','.
				$db->Quote($u->id).
				')'
			);
			if($db->query()){
				
				$db->setQuery('SELECT LAST_INSERT_ID()');
				$id=$db->loadResult();

				$ins=array();
				
				foreach($vz['att'] as $atk => $at){
					$ins[]='('.$db->Quote($id).', '.$db->Quote($atk).', '.$db->Quote($at).')';
				}
				
				$db->setQuery('DELETE FROM #__blog_attachments WHERE postid='.$db->Quote($id));
				$db->query();
				
				if(count($ins)>0){
					$db->setQuery('INSERT INTO #__blog_attachments VALUES '.implode(',', $ins));
					$db->query();
				}
				
				$msg=trans('added succ');
				if(isset($forced_unpub)){
					ArtaApplication::enqueueMessage(trans('ADDED BUT UNPUBLISHED'));
				}
				redirect('index.php?pack=blog'.(@$vz['isPackage']==true?'&tmpl=package':''), $msg);
			}else{
				ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog&view=new');
			}
		}
	}
	
	function delete(){
		if(ArtaUsergroup::getPerm('can_delete_posts', 'package', 'blog')==false){
			ArtaError::show(403, trans('YOU CANNOT DELETE BLOG POSTS'));
		}
		$v=getVar('ids', array(), 'post', 'array');
		$db=ArtaLoader::DB();
		foreach($v as $k=>$id){
			$id=ArtaFilterinput::clean($id, 'int');
			if($id<=0){
				unset($v[$k]);
			}else{
				$v[$k]=$db->Quote($id);
			}
		}
		if(count($v)==0){
			ArtaError::show(404, trans('NO POSTS FOUND'), 'index.php?pack=blog');
		}
		
		
		$db->setQuery('SELECT id,added_by FROM #__blogposts WHERE id IN('.implode(',',$v).')');
		$posts=$db->loadObjectList('id');
		
		if(count($posts)==0){
			ArtaError::show(404, trans('NO POSTS FOUND'), 'index.php?pack=blog');
		}
		
		$to_delete=array();
		$can_edit=ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog');
		$u=$this->getCurrentUser();
		
		foreach($posts as $id=>$post){
			if($can_edit==true || $u->id==$post->added_by){
				$to_delete[]=(int)$id;
			}
		}
		
		if(count($to_delete)==0 && count($posts)!=0){
			ArtaError::show(403, trans('YOU CANNOT DELETE OTHERS POSTS'));
		}
		
		$db->setQuery('DELETE FROM #__blogposts WHERE id IN('.implode(',',$to_delete).')');
		$r=$db->query();
		if($r==false){
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog');
		}
		
		$db->setQuery('DELETE FROM #__blog_comments WHERE postid IN('.implode(',',$to_delete).')');
		$r=$db->query();
		if($r==false){
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog');
		}
		
		
		
		$db->setQuery('DELETE FROM #__blog_attachments WHERE postid IN('.implode(',',$to_delete).')');
		if($db->query()){
			if(count($to_delete)!= count($posts)){
				ArtaApplication::enqueueMessage(trans('SOME NOT DELETED BECAUSE YOU CANT DELETE OTHERS POSTS'), 'warning');
			}
			redirect('index.php?pack=blog', trans('DELETED SUCC'));
			
		}else{
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog');
		}	
	}
	
	function activate($activation=1){
		if(ArtaUsergroup::getPerm('can_publish_posts', 'package', 'blog')==false){
			ArtaError::show(403,trans('YOU CANNOT CHANGE BLOG POSTS PUBLISHED PARAMETER'));
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		$v=getVar('ids', array(),'get','array');		
		$db=ArtaLoader::DB();
		foreach($v as $k=>$id){
			$id=ArtaFilterinput::clean($id, 'int');
			if($id<=0){
				unset($v[$k]);
			}else{
				$v[$k]=$db->Quote($id);
			}
		}
		if(count($v)==0){
			ArtaError::show(404, trans('NO POSTS FOUND'), 'index.php?pack=blog');
		}
		
		$db->setQuery('SELECT id,added_by FROM #__blogposts WHERE id IN('.implode(',',$v).')');
		$posts=$db->loadObjectList('id');
		
		if(count($posts)==0){
			ArtaError::show(404, trans('NO POSTS FOUND'), 'index.php?pack=blog');
		}
		
		$to_set=array();
		$can_edit=ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog');
		$u=$this->getCurrentUser();
		
		foreach($posts as $id=>$post){
			if($can_edit==true || $u->id==$post->added_by){
				$to_set[]=(int)$id;
			}
		}
		
		if(count($to_set)==0 && count($posts)!=0){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS BLOG POSTS'));
		}
		
		
		$db->setQuery('UPDATE #__blogposts SET enabled='.$activation.' WHERE id IN('.implode(',',$to_set).')', array('enabled'));
		if($db->Query()){
			if(count($to_set)!= count($posts)){
				ArtaApplication::enqueueMessage(trans('SOME NOT ACTIVATED BECAUSE YOU CANT EDIT OTHERS POSTS'), 'warning');
			}
			redirect('index.php?pack=blog', $activation==1 ? trans('ACTIVATED SUCC'): trans('DEACTIVATED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog');
		}	
	}
	
	
	function deactivate(){
		$this->activate(0);
	}

	function saveCat(){
		if(!ArtaUsergroup::getPerm('can_addedit_categories', 'package', 'blog')){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT BLOG CATEGORIES'));
		}
		
		$v=ArtaRequest::getVars('post');
		if(@$v['id']){$id='&id='.ArtaFilterinput::clean($v['id'], 'int');}else{
			$id='';
		}
		
		// 1.check existence
		if(ArtaUtility::keysExists(array('title', 'sef_alias', 'desc', 'parent'), $v)==false){
			ArtaError::show(400, trans('FORM ISNT COMPLETE'), 'index.php?pack=blog&view=newcat'.$id);
		}
		
		//2. trim input
		$v=ArtaFilterinput::trim($v);
		$v=ArtaFilterinput::array_limit($v, array('title'=>255, 'sef_alias'=>255));
		
		//3.Extend input
		$v=ArtaUtility::array_extend($v, array('ugs'=>array(),'id'=>0));
		
		//4.clean
		$v=ArtaFilterinput::clean($v, array('id'=>'int', 'title'=>'string', 'sef_alias'=>'string', 'parent'=>'int', 'desc'=>'safe_html','ugs'=>'array'));
		
		if($v['sef_alias']==''){
			$v['sef_alias']=ArtaFilteroutput::stringURLSafe($v['title']);
		}else{
			$v['sef_alias']=ArtaFilteroutput::stringURLSafe($v['sef_alias']);
		}
		
		
		//5.check lengths
		if($v['title']==''){
			redirect('index.php?pack=blog&view=newcat'.$id,trans('FORM ISNT COMPLETE'));
		}
		
		// 6.make data ready
		$ugs=array();
		foreach($v['ugs'] as $k=>$kk){
			$kk=ArtaFilterinput::clean($kk, 'int');
			if($kk<2){
				if($kk==0){
					$ugs[]='-'.$k;
				}else{
					$ugs[]=$k;
				}
			}else{
				unset($v['ugs'][$k]);
			}
		}

		$ugs=implode(',',$ugs);


		$db=ArtaLoader::DB();
		if($v['parent']>0){
			$db->setQuery('SELECT id FROM #__blogcategories WHERE id='.$db->Quote($v['parent']));
			$v['parent']=(int)$db->loadResult();
			if($v['parent']==null){
				ArtaError::show(404, trans('PARENT BLOG CATEGORY NOT FOUND'), 'index.php?pack=blog&view=newcat'.$id);
			}
		}else{
			$v['parent']=0;
		}
		
		if($v['id']>0){
			$db->setQuery('SELECT * FROM #__blogcategories WHERE id='.$db->Quote($v['id']));
			$r=$db->loadObject();
			if($r==null){
				ArtaError::show(404, trans('CATEGORY NOT FOUND'), 'index.php?pack=blog&view=newcat');
			}
			
			$model=$this->getModel('category');
			$childs=$model->getCategories($v['id']);
			$childs_id=array();
			foreach($childs as $child){
				$childs_id[]=$child->id;
			}

			
			if((int)$v['parent']==(int)$v['id'] || in_array($v['parent'], $childs_id)==true){
				ArtaError::show(400, trans('Category CANT BE SELF CHILD'));
			}
			if($v['sef_alias']!==$r->sef_alias){
				ArtaCache::clearData('blog','cat_sef_aliases');
			}
			$db->setQuery('UPDATE #__blogcategories SET '.
			'title='.$db->Quote($v['title']).','.
			'sef_alias='.$db->Quote($v['sef_alias']).','.
			'`desc`='.$db->Quote($v['desc']).','.
			'parent='.$db->Quote($v['parent']).','.
			'accmask='.$db->Quote($ugs).
			' WHERE id='.$r->id
			,true,array('blog,cat_sef_aliases'));
			
		}else{
			$db->setQuery('INSERT INTO #__blogcategories (title,sef_alias,`desc`, parent, accmask) VALUES( '.
			$db->Quote($v['title']).','.
			$db->Quote($v['sef_alias']).','.
			$db->Quote($v['desc']).','.
			$db->Quote($v['parent']).','.
			$db->Quote($ugs).
			')'
			);
		}

		$r=$db->query();
		
		if($r){
			if(getVar('close', false)!=false){
				echo '<script>window.top.opener.refreshCats();window.close();</script>';
			}else{
				redirect('index.php?pack=blog&view=category', trans('SAVED SUCC'));
			}
		}else{
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog&view=category');
		}
	}
	
	function deleteCat(){
		if(!ArtaUsergroup::getPerm('can_delete_categories', 'package', 'blog')){
			ArtaError::show(403, trans('YOU CANNOT DELETE BLOG CATEGORIES'));
		}
		$v=getVar('ids', array(), 'post', 'array');
		$db=Artaloader::DB();
		foreach($v as $k=>$vv ){
			$db->setQuery('SELECT id FROM #__blogcategories WHERE id='.$db->Quote($vv));
			if($vv!==$db->loadResult()){
				unset($v[$k]);
			}else{
				$db->setQuery('SELECT count(*) FROM #__blogcategories WHERE parent='.$db->Quote($vv));
				if((int)$db->loadResult()>0){
					$ok=true;
				}else{
					$db->setQuery('SELECT count(*) FROM #__blogposts WHERE blogid='.$db->Quote($vv));
					if((int)$db->loadResult()>0){
						$ok=true;
					}
				}
			}
		}
		if(@$ok==true){
			ArtaError::show(500, trans('NOT EMPTY CATS'), 'index.php?pack=blog&view=category');
		}
		if(count($v)==0){
			ArtaError::show(404, trans('CATEGORY NOT FOUND'), 'index.php?pack=blog&view=category');
		}
		$db->setQuery('DELETE FROM #__blogcategories WHERE id IN('.implode(',', $v).')');
		if($db->query()){
			redirect('index.php?pack=blog&view=category', trans('DELETED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog&view=category');
		}
	}

}
?>
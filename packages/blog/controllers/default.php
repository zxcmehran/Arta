<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/2/27 19:53 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class BlogControllerDefault extends ArtaPackageController{

	function display(){
		$v=$this->getView(getVar('view', 'last', '', 'string'), getVar('type', 'html', '', 'string'));
		$v->Display();
		return true;
	}
	
	function save(){
		if(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT BLOG POSTS'));
		}
		
		$vz=ArtaRequest::getVars('post');
		if(@$vz['id']){$id='&id='.ArtaFilterinput::clean($vz['id'], 'int');}else{
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
			$vz=ArtaUtility::array_extend($vz, array('denied'=>array(), 'id'=>0));
			
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
			if($vz['mod_time']==false){
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
		$db->die=true;
		$db->setQuery('SELECT * FROM #__blogcategories WHERE id='.$db->Quote($vz['blogid']));
		$crow=$db->loadObject();
		if($crow==null){
			if(@$vz['id']){$id='&id='.$vz['id'];}else{
				$id='';
			}
			ArtaError::show(404, trans('CATEGORY NOT FOUND'), 'index.php?pack=blog&view=new'.$id);				
		}
				
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
			,true, array('blog,new_tags', 'blog,sef_aliases'));
			
			if($db->query()){
				
				$id=$vz['id'];
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
				
				if(isset($forced_unpub)){
					$msg=trans('ADDED BUT UNPUBLISHED');
				}else{
					$msg=trans('ADDED SUCC');
				}
				$x='&view=post&id='.$vz['id'];
				redirect('index.php?pack=blog'.$x, $msg, isset($forced_unpub) ? 'warning' : 'tip');
			}else{				
				ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog&view=new&id='.$vz['id']);
			}
			
		}else{
			// Adding
			$u=ArtaLoader::User();
			$u=$u->getCurrentUser();
			if($vz['mod_time']!=false){
				$vz['mod_by']=$u->id;
			}else{
				$vz['mod_by']='';
                                $vz['mod_time']=null;
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
				
				if(isset($forced_unpub)){
					$msg=trans('ADDED BUT UNPUBLISHED');
				}else{
					$msg=trans('ADDED SUCC');
				}
				
				$x='&view=post&id='.$id;
				redirect('index.php?pack=blog'.$x, $msg, isset($forced_unpub) ? 'warning' : 'tip');
			}else{
				ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog&view=new');
			}
		}
	}
	
	function rate(){
		$r=getVar('value', 0, '','int');
		$id=getVar('id', 0, '','int');
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
		if($r>$this->getSetting('max_possible_rating_value', 5)){
			ArtaError::show(400);
		}
		if(ArtaUsergroup::getPerm('can_rate_posts', 'package', 'blog') && @$_COOKIE['last_rate_'.$id]+$this->getSetting('rating_timer',3600) <= time()){
			$db=ArtaLoader::DB();
			$db->setQuery('UPDATE #__blogposts SET rate_count= rate_count+1 , rating= rating + '.$db->Quote($r). ' WHERE id='.$db->Quote($id), array('rate_count', 'rating'));
			if($db->query()){
				setcookie('last_rate_'.$id, time(), time()+3600, '/');
			}
		}
		
	}
	
	function ajaxGetContent(){
		$id=getVar('id', '', '', 'int');
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__blogposts WHERE id='.$db->Quote($id));
		$r=$db->loadObject();
		if($r==null){
			ArtaError::show();
		}
		
		$this->setDoctype('xml');
		echo '<response><title>'.htmlspecialchars($r->title).'</title><content>'.htmlspecialchars($r->morecontent==null ? $r->introcontent : $r->introcontent.'<hr id="readmore_handler" />'.$r->morecontent).'</content></response>';
	}
	
	function ajaxSetContent(){
		if(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT BLOG POSTS'));
		}
		
		$id=getVar('id', '', '', 'int');
		$title=getVar('title', '', '', 'string');
		$con=getVar('content', '', '', 'safe-html');
		
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403);
		}
		
		if($pos=strpos($con, '<hr id="readmore_handler" />')){
			$intro=substr($con, 0, $pos);
			$more=substr($con, $pos+28);
		}else{
			$intro=$con;
			$more='';
		}
		
		if(ArtaUsergroup::getPerm('can_publish_posts', 'package', 'blog')==false){
			$enabled=false;
			$forced_unpub=true;
		}else{
			$enabled=true;
		}
		
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__blogposts WHERE id='.$db->Quote($id));
		$row=$db->loadObject();
		
		if($row==null){
			ArtaError::show(404, trans('INVALID POST TO EDIT'), 'index.php?pack=blog&view=new');
		}
		
		if($row->title!=$title){
			$sef_alias=ArtaFilteroutput::stringURLSafe($title);
		}else{
			$sef_alias=$row->sef_alias;
		}
		
		$u=$this->getCurrentUser();
		if($row->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS BLOG POSTS'));
		}
		
		$modby=$u->id;
		$modtime=time();

		$db->setQuery('UPDATE #__blogposts SET '.
			'`title`='.$db->Quote($title).','.
			'`sef_alias`='.$db->Quote($sef_alias).','.
			'`introcontent`='.$db->Quote($intro).','.
			'`morecontent`='.$db->Quote($more).','.
			'`enabled`='.$db->Quote($enabled).','.
			'`mod_time`='.$db->Quote(ArtaDate::toMySQL($modtime)).','.
			'`mod_by`='.$modby.
			' WHERE id='.$db->Quote($id)
		);
		$r=$db->query();
		if($r==false){
			ArtaError::show(500);
		}
		
		$this->setDoctype('xml');
		if(isset($forced_unpub)){
			$msg=trans('ADDED BUT UNPUBLISHED');
		}else{
			$msg=trans('ADDED SUCC');
		}
		echo '<response><msg>'.htmlspecialchars($msg).'</msg></response>';
	}

}

?>
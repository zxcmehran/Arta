<?php
if(!defined('ARTA_VALID')){die('No access');}
$pack = ArtaLoader::Package();
if($pack->getSetting('commenting_system', true)==false){
	ArtaError::show(403);
}
class BlogControllerComments extends ArtaPackageController{

	function save(){
		if(ArtaUsergroup::getPerm('can_edit_post_comments', 'package', 'blog')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT COMMENTS'));
		}
		
		$vz=ArtaRequest::getVars('post');
		
		//<Data processing>
		
			// 1.Check Existence
			if(!ArtaUtility::keysExists(
				array('title',
				'content',
				'added_time',
				'cid',
				'id')
			,$vz)){
				
				redirect('index.php?pack=blog', trans('FORM ISNT COMPLETE'), 'warning');
			}
			
			// 2.Trim Input and Cut them
			$vz=ArtaFilterinput::trim($vz);
			$vz=ArtaFilterinput::array_limit($vz, array('title'=>255,'author'=>255,'authormail'=>255,'authorweb'=>255));
			
			// 3.Extend input
			$user=ArtaLoader::User();
			$u=$user->getGuest();
			$s=unserialize($u->settings);
			$m=unserialize($u->misc);
			$db=ArtaLoader::DB();
			
			$db->setQuery('SELECT id FROM #__languages WHERE name='.$db->Quote($s->site_language).' AND client=\'site\'');
			$l=$db->loadResult();
			$vz=ArtaUtility::array_extend($vz, array('lang'=>$l, 'published'=>false, 'points'=>0));
			
			// 4.Clean input
			$vz=ArtaFilterinput::clean($vz, array('title'=>'string',
			'content'=>'string',
			'author'=>'string',
			'added_time'=>'datetime',
			'published'=>'bool',
			'authormail'=>'string',
			'authorweb'=>'string',
			'cid'=>'int',
			'id'=>'int',
			'lang'=>'int',
			'points'=>'int'			
			));
			
			$link='index.php?pack=blog&view=post&id='.$vz['id'];
			
			// 5.Check lengths
			if($vz['title']==''){
				redirect($link, trans('NO COMMENT TITLE SPECIFIED'),'warning');
			}
			
			if($vz['content']==''){
				redirect($link, trans('NO COMMENT CONTENT FOUND'),'warning');
			}
			
			if($vz['added_time']==false){
				redirect($link, trans('INVALID DATE'),'warning');
			}
			
			if($vz['lang']!==$l){
				$db->setQuery('SELECT id FROM #__languages WHERE id='.$db->Quote($vz['lang']).' AND client=\'site\'');
				if($vz['lang']!=$db->loadResult()){
					ArtaError::show(404, trans('COMMENT LANGUAGE NOT FOUND'));
				}
			}
			
			
		// </Data processing>
		
		// Maybe you can not publish posts...
		

		$db->setQuery('SELECT *, (SELECT COUNT(s.replyto) FROM #__blog_comments as s WHERE s.replyto=m.id GROUP BY s.replyto) AS `childs` FROM #__blog_comments as m WHERE m.postid='.$db->Quote($vz['id']).' AND m.id='.$db->Quote($vz['cid']));
		$row=$db->loadObject();
		if($row==null){
			ArtaError::show();
		}
		
		$comu=$user->getUser($row->added_by);
		if($comu==null && $row->added_by!=0){
			$vz['added_by']='0';
		}else{
			$vz['added_by']=$row->added_by;
		}
		
		if($row->added_by!=0){
			$vz['author']='';
			$vz['authorweb']='';
			$vz['authormail']='';
		}

		if(((int)$row->replyto!=0 || (int)$row->childs!=0)&&(int)$row->language!==(int)$vz['lang']){
			ArtaError::show(400, trans('YOU CANNOT CHANGE LANG OF REPLY OR REPLIED MESSAGES'));
		}
		
		if($row==null){
			ArtaError::show(404);
		}
		
		$u=$this->getCurrentUser();
		if($u->id==0 || ($row->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_comments', 'package', 'blog')==false)){
			ArtaError::show(403, trans('YOU CANNOT EDIT OTHERS COMMENTS'));
		}
		
		if(ArtaUsergroup::getPerm('can_change_comments_publish_status', 'package', 'blog')==false && $vz['published']==true){
			$vz['published']=false;
			$forced_pub=false;
		}
			
			
		$db->setQuery('UPDATE #__blog_comments SET '.
			'`title`='.$db->Quote($vz['title']).','.
			'`content`='.$db->Quote($vz['content']).','.
			'`added_time`='.$db->Quote($vz['added_time']).','.
			'`language`='.$db->Quote($vz['lang']).','.
			'`published`='.$db->Quote($vz['published']).','.
			'`added_by`='.$db->Quote($vz['added_by']).','.
			'`points`='.$db->Quote($vz['points']).','.
			'`author`='.$db->Quote($vz['author']).','.
			'`authormail`='.$db->Quote($vz['authormail']).','.
			'`authorweb`='.$db->Quote($vz['authorweb']).
			' WHERE id='.$db->Quote($vz['cid']).
			' AND postid='.$db->Quote($vz['id'])
		);

		if($db->query()){
			
			if(isset($forced_pub)){
				ArtaApplication::enqueueMessage(sprintf(trans('PUBLISHED SET TO _ BEACAUSE PERM'), (int)$forced_pub),'warning');
			}
			redirect('index.php?pack=blog&view=post&id='.$vz['id'], trans('SAVED SUCC'));
		}else{				
			ArtaError::show(500, trans('ERROR IN DB'), $link);
		}
			
		
	}
	
	function delete(){
		if(ArtaUsergroup::getPerm('can_delete_posts_comments', 'package', 'blog')==false){
			ArtaError::show(403, trans('YOU CANNOT DELETE COMMENTS'));
		}
		$cid=getVar('cid', '', '', 'int');
		$id=getVar('id', '', '', 'int');
		
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT id FROM #__blog_comments WHERE replyto='.$db->Quote($cid));
		$item=$db->loadResult();
		if($item!==null){
			redirect('index.php?pack=blog&view=post&id='.$id, trans('COMMENT HAS REPLIES'), 'error');
		}
		
		$db->setQuery('DELETE FROM #__blog_comments WHERE id='.$db->Quote($cid));
		if($db->Query()){
			redirect('index.php?pack=blog&view=post&id='.$id, trans('DELETED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=blog&view=comments&id='.$id);
		}	
	}
	
	function publish(){
		if(ArtaUsergroup::getPerm('can_change_comments_publish_status', 'package', 'blog')==false){
			ArtaError::show(403,trans('YOU CANNOT CHANGE COMMENT PUBLISHED PARAMETER'));
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		$v=getVar('cid', '','','int');		
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__blog_comments SET published=1 WHERE id='.$db->Quote($v), array('published'));
		if($db->Query()){
			ArtaError::show(200, trans('PUBLISHED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'));
		}	
	}
	
	
	function unpublish(){
		if(ArtaUsergroup::getPerm('can_change_comments_publish_status', 'package', 'blog')==false){
			ArtaError::show(403,trans('YOU CANNOT CHANGE COMMENT PUBLISHED PARAMETER'));
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		$v=getVar('cid', '','','int');		
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__blog_comments SET published=0 WHERE id='.$db->Quote($v), array('published'));
		if($db->Query()){
			ArtaError::show(200, trans('UNPUBLISHED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'));
		}	
	}
	
	function savenew(){
		if(ArtaUsergroup::getPerm('can_leave_comments', 'package', 'blog')==false){
			ArtaError::show(403, trans('YOU CANNOT LEAVE COMMENTS'));
		}
		
		$vz=ArtaRequest::getVars('post');

		//<Data processing>
		
			// 1.Check Existence
			if(!ArtaUtility::keysExists(
				array('title',
				'content',
				'captcha',
				'id')
			,$vz)){
				
				ArtaError::show(400, trans('FORM ISNT COMPLETE'));
			}
			
			// 2.Trim Input and Cut them
			$vz=ArtaFilterinput::trim($vz);
			$vz=ArtaFilterinput::array_limit($vz, array('title'=>255,'author'=>255,'authormail'=>255,'authorweb'=>255));
			
			// 3.Extend input
			$cu=$this->getCurrentUser();
			$_m=unserialize($cu->misc);
			$u=ArtaLoader::User();
			$u=$u->getGuest();
			$s=unserialize($u->settings);
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id FROM #__languages WHERE name='.$db->Quote($s->site_language).' AND client=\'site\'');
			$l=$db->loadResult();

			$vz=ArtaUtility::array_extend($vz, array('lang'=>$l, 'replyto'=>0));
			
			// 4.Clean input
			$vz=ArtaFilterinput::clean($vz, array('title'=>'string',
			'content'=>'string',
			'author'=>'string',
			'authormail'=>'email',
			'authorweb'=>'string',
			'id'=>'int',
			'captcha'=>'string',
			'lang'=>'int',
			'replyto'=>'int'			
			));
			
			
			$link='index.php?pack=blog&view=post&id='.$vz['id'];
			
			
			if($cu->id==0 && @$vz['authormail']==false){
				ArtaError::show(400, trans('EMAIL FIELD IS REQUIRED ENTER VALID ADDRESS'));
			}
			if($cu->id!==0){
				$vz['author']='';
				$vz['authormail']='';
				$vz['authorweb']='';
			}elseif($vz['author']==''){
				unset($vz['author']);
			}			
			
			$vz=ArtaUtility::array_extend($vz, array('author'=>'Guest'.mt_rand(0,9).mt_rand(0,9), 'authorweb'=>''));
			
			// 5.Check lengths
			if($vz['title']==''){
				redirect($link, trans('NO COMMENT TITLE SPECIFIED'),'warning');
			}
			
			if($vz['content']==''){
				redirect($link, trans('NO COMMENT CONTENT FOUND'),'warning');
			}
			
			if($vz['replyto']>0){
				$db->setQuery('SELECT language FROM #__blog_comments WHERE id='.$vz['replyto']);
				$vz['lang']=$db->loadResult();
				$got=true;
			}
			
			if($vz['lang']!==$l && @$got!=true){
				$db->setQuery('SELECT id FROM #__languages WHERE id='.$db->Quote($vz['lang']).' AND client=\'site\'');
				if($vz['lang']!=$db->loadResult()){
					ArtaError::show(404, trans('COMMENT LANGUAGE NOT FOUND'));
				}
			}

			if(ArtaCaptcha::verifyCode($vz['captcha'], 'comment_'.$vz['id'])==false){
				redirect($link, trans('INVALID CAPTCHA'),'error');
			}
				
		// </Data processing>
		
		$pub=$this->getSetting('autopublish_comments', 0);
		
		$db->setQuery('INSERT INTO #__blog_comments VALUES(NULL'.
			','.$db->Quote($vz['title']).
			','.$db->Quote($vz['content']).
			','.$db->Quote($vz['id']).
			','.$db->Quote($vz['replyto']).
			','.$db->Quote($cu->id).
			','.$db->Quote(ArtaDate::toMySQL(time())).
			','.$db->Quote($vz['lang']).
			','.$db->Quote((int)$pub).
			', 0'.
			','.$db->Quote($vz['author']).
			','.$db->Quote($vz['authormail']).
			','.$db->Quote($vz['authorweb']).
			')'
		);
	
		if($db->query()){
			
			redirect('index.php?pack=blog&view=post&id='.$vz['id'], $pub==true?trans('COMMENT SAVED SUCC'):trans('COMMENT SAVED SUCC AND WILL BE PUBLISHED SOON'));
		}else{				
			ArtaError::show(500, trans('ERROR IN DB'), $link);
		}
			
		
	}
	
	function updatePoint(){
		$v=getVar('cid', '','','int');
		$g=getVar('grow', '','','bool');
		
		if(ArtaUsergroup::getPerm('can_touch_comment_points', 'package', 'blog')==false || (@$_COOKIE['touched_'.$v]==1 && ArtaUsergroup::getPerm('can_edit_post_comments', 'package', 'blog')==false)){
			ArtaError::show(403,trans('YOU CANNOT CHANGE COMMENT POINTS'));
		}
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
				
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__blog_comments SET points=points + ('.($g==true?1:-1).') WHERE id='.$db->Quote($v), array('points'));

		if($db->Query()){
			setcookie('touched_'.$v, '1', time()+604800);
			ArtaError::show(200, trans('SAVED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'));
		}	
	}
	
}
?>
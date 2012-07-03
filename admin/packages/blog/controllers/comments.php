<?php
if(!defined('ARTA_VALID')){die('No access');}
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
				'published',
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
			$vz=ArtaUtility::array_extend($vz, array('lang'=>$l, 'points'=>0));
			
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
			
			$link='index.php?pack=blog&view=comments&id='.$vz['id'].'&cid='.$vz['cid'];
			
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
			'`points`='.$db->Quote($vz['points']).','.
			'`added_by`='.$db->Quote($vz['added_by']).','.
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
			redirect('index.php?pack=blog&view=comments&id='.$vz['id'], trans('SAVED SUCC'));
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
			redirect('index.php?pack=blog&view=comments&id='.$id, trans('COMMENT HAS REPLIES'), 'error');
		}
		
		$db->setQuery('DELETE FROM #__blog_comments WHERE id='.$db->Quote($cid));
		if($db->Query()){
			redirect('index.php?pack=blog&view=comments&id='.$id, trans('DELETED SUCC'));
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
	
}
?>
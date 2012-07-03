<?php
if(!defined('ARTA_VALID')){die('No access');}
function getUsersBlogs($m){
	$a=$m->getParam(1); $a=$a->scalarVal();
	$b=$m->getParam(2); $b=$b->scalarVal();
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT * FROM #__users WHERE `username`='.$db->Quote($a).' AND `password`='.$db->Quote(ArtaString::hash($b)));
	$u=$db->loadObject();
	if($u==null){
		return new xmlrpcresp(0, 403, 'Invalid Username/Password');
	}elseif(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog', CLIENT, $u->usergroup)==false){
		return new xmlrpcresp(0, 403, 'You cannot edit Blog Posts.');
	}
	
	$c=ArtaLoader::Config();
	$x=array('isAdmin'=>new xmlrpcval(true,'boolean'),
			'url'=>new xmlrpcval(ArtaURL::getSiteURL()),
			'blogid'=>new xmlrpcval('1'),
			'blogName'=>new xmlrpcval($c->site_name),
			'xmlrpc'=>new xmlrpcval(ArtaURL::getSiteURL().'xmlrpc.php'));
	$s=new xmlrpcval($x, 'struct');
	return new xmlrpcresp(new xmlrpcval(array($s), 'array'));
	
}
$this->mapFunction('blogger.getUsersBlogs', 'getUsersBlogs', 'struct', array('string','string','string'));



function getUserInfo($m){
	$a=$m->getParam(1); $a=$a->scalarVal();
	$b=$m->getParam(2); $b=$b->scalarVal();
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT * FROM #__users WHERE `username`='.$db->Quote($a).' AND `password`='.$db->Quote(ArtaString::hash($b)));
	$u=$db->loadObject();
	if($u==null){
		return new xmlrpcresp(0, 403, 'Invalid Username/Password');
	}elseif(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog', CLIENT, $u->usergroup)==false){
		return new xmlrpcresp(0, 403, 'You cannot edit Blog Posts.');
	}

	$x=array('userid'=>new xmlrpcval($u->id),
			'firstname'=>new xmlrpcval($u->name),
			'lastname'=>new xmlrpcval(''),
			'url'=>new xmlrpcval(_getSiteDomain().makeURL('index.php?pack=user&view=profile&uid='.$u->id)),
			'email'=>new xmlrpcval($u->email),
			'username'=>new xmlrpcval($u->username));
	$s=new xmlrpcval($x, 'struct');
	return new xmlrpcresp($s);
	
}
$this->mapFunction('blogger.getUserInfo', 'getUserInfo', 'struct', array('string','string','string'));

function getRecentPosts($m){
	$a=$m->getParam(1); $a=$a->scalarVal();
	$b=$m->getParam(2); $b=$b->scalarVal();
	$c=$m->getParam(3); $c=$c->scalarVal();
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT * FROM #__users WHERE `username`='.$db->Quote($a).' AND `password`='.$db->Quote(ArtaString::hash($b)));
	$u=$db->loadObject();
	if($u==null){
		return new xmlrpcresp(0, 403, 'Invalid Username/Password');
	}elseif(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog', CLIENT, $u->usergroup)==false){
		return new xmlrpcresp(0, 403, 'You cannot edit Blog Posts.');
	}
	$c=(int)$c;
	if($c>100){$c=100;}
	if($c==0){$c=10;}
	$db->setQuery('SELECT * FROM #__blogposts ORDER BY `added_time` DESC LIMIT 0,'.(int)$c);
	$p = $db->loadObjectList();
	
	$r=array();
	foreach($p as $v){
		$bid= _getBlogID($v->blogid);
		$r[]=new xmlrpcval(array('postid'=>new xmlrpcval($v->id),
			'dateCreated'=>new xmlrpcval(iso8601_encode(strtotime($v->added_time)), 'dateTime.iso8601'),
			'title'=>new xmlrpcval($v->title),
			'description'=>new xmlrpcval(strlen($v->morecontent)>0?$v->introcontent.'<hr id="readmore_handler" />'.$v->morecontent:$v->introcontent),
			'categories'=>new xmlrpcval(array(new xmlrpcval($bid->title)), 'array'),
			'publish'=>new xmlrpcval((bool)$v->enabled,'boolean'),
			'link'=>new xmlrpcval(_getSiteDomain().makeURL('index.php?pack=blog&view=post&id='.$v->id)),
			'permaLink'=>new xmlrpcval(_getSiteDomain().makeURL('index.php?pack=blog&view=post&id='.$v->id))), 'struct');
	}

	$s=new xmlrpcval($r, 'array');
	return new xmlrpcresp($s);
	
}
$this->mapFunction('metaWeblog.getRecentPosts', 'getRecentPosts', 'struct', array('string','string','string','int'));


function getCategories($m){
	$a=$m->getParam(1); $a=$a->scalarVal();
	$b=$m->getParam(2); $b=$b->scalarVal();
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT * FROM #__users WHERE `username`='.$db->Quote($a).' AND `password`='.$db->Quote(ArtaString::hash($b)));
	$u=$db->loadObject();
	if($u==null){
		return new xmlrpcresp(0, 403, 'Invalid Username/Password');
	}elseif(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog', CLIENT, $u->usergroup)==false){
		return new xmlrpcresp(0, 403, 'You cannot edit Blog Posts.');
	}
	
	/*$db->setQuery('SELECT id,title,`desc` FROM #__blogcategories ORDER BY `title` DESC');
	$p = $db->loadObjectList();*/
	$p=_getCategoriesRelated();
	
	$r=array();
	foreach($p as $v){
		$r[]=new xmlrpcval(array(
			'title'=>new xmlrpcval($v->title),
			'description'=>new xmlrpcval($v->desc==''?$v->title:$v->desc)), 'struct');
	}

	$s=new xmlrpcval($r, 'array');
	return new xmlrpcresp($s);
	
}
$this->mapFunction('metaWeblog.getCategories', 'getCategories', 'struct', array('string','string','string'));


function getPost($m){
	$x=$m->getParam(0); $x=$x->scalarVal();
	$a=$m->getParam(1); $a=$a->scalarVal();
	$b=$m->getParam(2); $b=$b->scalarVal();
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT * FROM #__users WHERE `username`='.$db->Quote($a).' AND `password`='.$db->Quote(ArtaString::hash($b)));
	$u=$db->loadObject();
	if($u==null){
		return new xmlrpcresp(0, 403, 'Invalid Username/Password');
	}elseif(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog', CLIENT, $u->usergroup)==false){
		return new xmlrpcresp(0, 403, 'You cannot edit Blog Posts.');
	}

	$db->setQuery('SELECT * FROM #__blogposts WHERE id='.(int)$x);
	$v = $db->loadObject();
	if($v==null){
		return new xmlrpcresp(0, 404, 'Post Not found.');
	}
	
	$bid= _getBlogID($v->blogid);
	$r=new xmlrpcval(array('postid'=>new xmlrpcval($v->id),
		'dateCreated'=>new xmlrpcval(iso8601_encode(strtotime($v->added_time)), 'dateTime.iso8601'),
		'title'=>new xmlrpcval($v->title),
		'description'=>new xmlrpcval(strlen($v->morecontent)>0?$v->introcontent.'<hr id="readmore_handler" />'.$v->morecontent:$v->introcontent),
		'categories'=>new xmlrpcval(array(new xmlrpcval($bid->title)), 'array'),
		'publish'=>new xmlrpcval((bool)$v->enabled,'boolean'),
		'link'=>new xmlrpcval(_getSiteDomain().makeURL('index.php?pack=blog&view=post&id='.$v->id)),
		'permaLink'=>new xmlrpcval(_getSiteDomain().makeURL('index.php?pack=blog&view=post&id='.$v->id))), 'struct');

	return new xmlrpcresp($r);
	
}
$this->mapFunction('metaWeblog.getPost', 'getPost', 'struct', array('string','string','string'));


function newPost($m){
	$a=$m->getParam(0); $a=$a->scalarVal();
	$b=$m->getParam(1); $b=$b->scalarVal();
	$c=$m->getParam(2); $c=$c->scalarVal();
	$d=$m->getParam(3);
		$d1=$d->structmem('title'); $d1=$d1->scalarval();
		$d2=$d->structmem('description'); $d2=$d2->scalarval();
		@$d3=$d->structmem('dateCreated'); 
		@$d4=$d->structmem('categories'); 
	$e=$m->getParam(4); $e=$e->scalarVal();
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT * FROM #__users WHERE `username`='.$db->Quote($b).' AND `password`='.$db->Quote(ArtaString::hash($c)));
	$u=$db->loadObject();
	if($u==null){
		return new xmlrpcresp(0, 403, 'Invalid Username/Password');
	}elseif(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog', CLIENT, $u->usergroup)==false){
		return new xmlrpcresp(0, 403, 'You cannot edit Blog Posts.');
	}
	
	if(trim($d1)==null){
		return new xmlrpcresp(0, 403, 'No title found.');
	}
	if(trim($d2)==null){
		return new xmlrpcresp(0, 403, 'No post content found.');
	}
	
	if(is_object($d3)){
		$d3=$d3->scalarval();
	}
	@$d3=iso8601_decode($d3);
	if($d3==false){
		$d3=time();
	}
	$d3=ArtaDate::toMySQL($d3);
	if($d3==false){
		$d3=ArtaDate::toMySQL(time());	
	}
	$cat=@$d4==null?null:@$d4->arraymem(0);
	if(is_object($cat)==true){
		$cat=$cat->scalarval();
	}
	if($cat!=null){
		$bid=_getBlogIDofThis($cat);
	}
	if($cat==null || $bid==null){
		return new xmlrpcresp(0, 500, 'Invalid Category.');
	}
	
	if(strpos($d2, '<hr id="readmore_handler" />')!=false){
		$d21=substr($d2, 0, strpos($d2, '<hr id="readmore_handler" />'));
		$d22=substr($d2, strpos($d2, '<hr id="readmore_handler" />')+28);
	}else{
		$d21=$d2;
		$d22='';
	}
	
	$d11=ArtaFilteroutput::stringURLSafe($d1);
	
	if(ArtaUsergroup::getPerm('can_publish_posts', 'package', 'blog',CLIENT,$u->usergroup)==false && $e==true){
			$e=false;
		}
	
	
	$db->setQuery('INSERT INTO #__blogposts '.
				'(`title`, `sef_alias`, `introcontent`, `morecontent`, `enabled`, `denied`, `blogid`, `added_time`, `mod_time`, `mod_by`, `pub_time`, `unpub_time`, `hits`, `tags`, `added_by`)'.
				' VALUES ('.
				$db->Quote($d1).','.
				$db->Quote($d11).','.
				$db->Quote($d21).','.
				$db->Quote($d22).','.
				$db->Quote($e).','.
				$db->Quote('').','.
				$db->Quote($bid).','.
				$db->Quote($d3).','.
				'NULL,'.
				'NULL,'.
				$db->Quote($d3).','.
				'NULL,'.
				$db->Quote(0).','.
				$db->Quote('').','.
				$db->Quote($u->id).
				')'
			);
	
	if($db->query()){
		return new xmlrpcresp(new xmlrpcval($db->getInsertedID()));
	}else{
		return new xmlrpcresp(0, 500, 'Error in DB.');
	}
	
}
$this->mapFunction('metaWeblog.newPost', 'newPost', 'string', array('string','string','string','struct','bool'));



function editPost($m){
	$a=$m->getParam(0); $a=$a->scalarVal();
	$b=$m->getParam(1); $b=$b->scalarVal();
	$c=$m->getParam(2); $c=$c->scalarVal();
	$d=$m->getParam(3);
		$d1=$d->structmem('title'); $d1=$d1->scalarval();
		$d2=$d->structmem('description'); $d2=$d2->scalarval();
		@$d3=$d->structmem('dateCreated'); 
		@$d4=$d->structmem('categories'); 
	$e=$m->getParam(4); $e=(bool)$e->scalarVal();
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT * FROM #__users WHERE `username`='.$db->Quote($b).' AND `password`='.$db->Quote(ArtaString::hash($c)));
	$u=$db->loadObject();
	if($u==null){
		return new xmlrpcresp(0, 403, 'Invalid Username/Password');
	}elseif(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog', CLIENT, $u->usergroup)==false){
		return new xmlrpcresp(0, 403, 'You cannot edit Blog Posts.');
	}
	
	$db->setQuery('SELECT * FROM #__blogposts WHERE id='.(int)$a);
	$bp = $db->loadObject();
	if($bp==null){
		return new xmlrpcresp(0, 404, 'Post not found.');
	}
	
	
	if(trim($d1)==null){
		return new xmlrpcresp(0, 403, 'No title found.');
	}
	if(trim($d2)==null){
		return new xmlrpcresp(0, 403, 'No post content found.');
	}
	
	if(is_object($d3)){
		$d3=$d3->scalarval();
	}
	@$d3=iso8601_decode($d3);
	if($d3==false){
		$d3=time();
	}
	$d3=ArtaDate::toMySQL($d3);
	if($d3==false){
		$d3=ArtaDate::toMySQL(time());	
	}
	$cat=@$d4==null?null:@$d4->arraymem(0);
	if(is_object($cat)==true){
		$cat=$cat->scalarval();
	}
	if($cat!=null){
		$bid=_getBlogIDofThis($cat);
	}else{
		$bid=$bp->blogid;
	}
	if($bid==null){
		return new xmlrpcresp(0, 500, 'Invalid Category.');
	}
	
	if(strpos($d2, '<hr id="readmore_handler" />')!=false){
		$d21=substr($d2, 0, strpos($d2, '<hr id="readmore_handler" />'));
		$d22=substr($d2, strpos($d2, '<hr id="readmore_handler" />')+28);
	}else{
		$d21=$d2;
		$d22='';
	}
	
	$d11=ArtaFilteroutput::stringURLSafe($d1);
	
	
	if($bp->added_by!==$u->id && ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog', CLIENT, $u->usergroup)==false){
		return new xmlrpcresp(0, 500, 'You cannot edit other\'s posts.');
	}
	
	$mtime=time();
	
	$ut=$bp->unpub_time;
	if($ut!=false){
		$ut=$db->Quote($ut);
	}else{
		$ut='NULL';
	}
	
	if(ArtaUsergroup::getPerm('can_publish_posts', 'package', 'blog',CLIENT,$u->usergroup)==false && $e==true){
		$e=false;
	}
	
	$db->setQuery('UPDATE #__blogposts SET '.
		'`title`='.$db->Quote($d1).','.
		'`sef_alias`='.$db->Quote($bp->sef_alias).','.
		'`introcontent`='.$db->Quote($d21).','.
		'`morecontent`='.$db->Quote($d22).','.
		'`enabled`='.$db->Quote($e).','.
		'`denied`='.$db->Quote($bp->denied).','.
		'`blogid`='.$db->Quote($bid).','.
		'`added_time`='.$db->Quote($bp->added_time).','.
		'`mod_time`='.$db->Quote(ArtaDate::toMySQL(time())).','.
		'`mod_by`='.$db->Quote($u->id).','.
		'`pub_time`='.$db->Quote($bp->pub_time).','.
		'`unpub_time`='.$ut.','.
		'`hits`='.$db->Quote($bp->hits).','.
		'`tags`='.$db->Quote($bp->tags).
		' WHERE id='.$db->Quote($bp->id)
	);
	
	if($db->query()){
		return new xmlrpcresp(new xmlrpcval(true,'boolean'));
	}else{
		return new xmlrpcresp(0, 500, 'Error in DB.');
	}
	
}
$this->mapFunction('metaWeblog.editPost', 'editPost', 'bool', array('string','string','string','struct','value'));




function newMediaObject($m){
	$a=$m->getParam(0); $a=$a->scalarVal();
	$b=$m->getParam(1); $b=$b->scalarVal();
	$c=$m->getParam(2); $c=$c->scalarVal();
	$d=$m->getParam(3);
		$d1=$d->structmem('name'); $d1=$d1->scalarval();
		$d2=$d->structmem('type'); $d2=$d2->scalarval();
		$d3=$d->structmem('bits'); $d3=$d3->scalarval(); 
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT * FROM #__users WHERE `username`='.$db->Quote($b).' AND `password`='.$db->Quote(ArtaString::hash($c)));
	$u=$db->loadObject();
	if($u==null){
		return new xmlrpcresp(0, 403, 'Invalid Username/Password');
	}elseif(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog', CLIENT, $u->usergroup)==false){
		return new xmlrpcresp(0, 403, 'You cannot edit Blog Posts.');
	}
	
	if($d1==false || $d2==false || $d3==false){
		return new xmlrpcresp(0, 400, 'Invalid Parameters.');
	}
	
	$d1=ArtaFilterinput::safeAddress($d1);
	
	if(ArtaUsergroup::getPerm('can_upload_files', 'package', 'filemanager', CLIENT, $u->usergroup)){
		$pack=ArtaLoader::Package();
		$types=$pack->getSetting('allowed_filetypes_to_upload','jpg,jpeg,gif,png,swf,flv,bmp,psd,mp3,wav,wma,wm,asf,mp4,mpg,mpeg,avi,wmv,mkv,3gp,rm,zip,rar,7z,gz,bz,tar,doc,pdf,xls,wri,docx,rtf', 'filemanager');
		
		$types=explode(',',$types);
		$types=array_map('trim', $types);
		$types=array_map('strtolower', $types);
		$ext=strtolower(ArtaFile::getExt($d1));
		if(!in_array($ext, $types)){
			return new xmlrpcresp(0, 500, 'The File type is invalid.');
		}
		
		if(strlen($d3)>($pack->getSetting('allowed_filesize_to_upload',1024, 'filemanager')*1024)){
			return new xmlrpcresp(0, 500, 'The File Size is invalid.');
		}
		
		if(ArtaFile::write(ARTAPATH_BASEDIR.'/content/mwapi/'.$d1, $d3)){
			return new xmlrpcresp(new xmlrpcval(array('url'=>new xmlrpcval(
				ArtaURL::getSiteURL().'content/mwapi/'.$d1
			)),'struct'));
		}else{
			return new xmlrpcresp(0, 500, 'Error in writing file.');
		}
	}else{
		return new xmlrpcresp(0, 403, 'You are not authorized.');
	}
	
}
$this->mapFunction('metaWeblog.newMediaObject', 'newMediaObject', 'bool', array('string','string','string','struct'));


function deletePost($m){
	$a=$m->getParam(1); $a=(int)$a->scalarVal();
	$b=$m->getParam(2); $b=$b->scalarVal();
	$c=$m->getParam(3); $c=$c->scalarVal();
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT * FROM #__users WHERE `username`='.$db->Quote($b).' AND `password`='.$db->Quote(ArtaString::hash($c)));
	$u=$db->loadObject();
	if($u==null){
		return new xmlrpcresp(0, 403, 'Invalid Username/Password');
	}elseif(ArtaUsergroup::getPerm('can_delete_posts', 'package', 'blog', 'admin', $u->usergroup)==false){
		return new xmlrpcresp(0, 403, 'You cannot delete Blog Posts.');
	}
	
	$db->setQuery('SELECT id,added_by FROM #__blogposts WHERE id ='.$db->Quote($a));
	$post=$db->loadObject();
	
	if($post==null){
		return new xmlrpcresp(0, 404, 'Post not found.');
	}
	
	$can_edit=ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog',CLIENT,$u->usergroup);
	
	if($post->added_by!=$u->id && $can_edit ==false){
		return new xmlrpcresp(0, 404, 'You cannot delete others\' posts.');
	}
	
	$db->setQuery('DELETE FROM #__blogposts WHERE id ='.$db->Quote($a));
	$r=$db->query();
	if($r==false){
		return new xmlrpcresp(0, 500, 'Error in DB.');
	}
	
	$db->setQuery('DELETE FROM #__blog_comments WHERE postid ='.$db->Quote($a));
	$r=$db->query();
	if($r==false){
		return new xmlrpcresp(0, 500, 'Error in DB.');
	}
	
	$db->setQuery('DELETE FROM #__blog_attachments WHERE postid ='.$db->Quote($a));
	if($db->query()){
		return new xmlrpcresp(new xmlrpcval(true,'boolean'));
	}else{
		return new xmlrpcresp(0, 500, 'Error in DB.');
	}
		
}
$this->mapFunction('blogger.deletePost', 'deletePost', 'bool', array('string','string','string','string','bool'));


// ------------------------------------------------------------


function _getCategoriesRelated($p=0, $level=0){
	$j=$level;
	if($level!==0){
		
		$level='';
		$i=0;
		while($j>$i){
			$level.='..';
			$i++;
		}
		$level.=' ';
	}else{
		$level='';
	}

	if(!isset($GLOBALS['CACHE']['blog.new_categories'])){
		$r=ArtaCache::getData('blog','new_categories');
		if($p==0&&$level==0&&$r!==false){
			$GLOBALS['CACHE']['blog.new_categories']= $r;
		}else{
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__blogcategories');
			$GLOBALS['CACHE']['blog.new_categories']=
				ArtaUtility::keyByChild((array)$db->loadObjectList(), 'parent', true);
			ArtaCache::putData('blog','new_categories', $GLOBALS['CACHE']['blog.new_categories']);
		}
	}

	$r=@$GLOBALS['CACHE']['blog.new_categories'][$p];
	if(is_object($r)){
		$r=array($r);
	}
	$r=@count($r) ? $r : array();
	foreach($r as &$v){
		
		$v->title=$level.$v->title;
	}
	$r=ArtaUtility::keyByChild($r, 'id');
	$r=ArtaUtility::SortByChild($r, 'parent');
	
	$p=1;
	foreach($r as $k=>$v){
		$c=_getCategoriesRelated($v->id, $j+1);
		$x=count(array_slice($r, 0, $p))+count($c)+1;
		$r=array_merge(array_slice($r, 0, $p),$c,array_slice($r, $p));
		$p=$x;
	}
		
	return $r;
}

function _getBlogIDofThis($cat){
	$db=ArtaLoader::DB();
        $where = 'title = '.$db->Quote($cat);
        $newcat=$cat;
        $i=0;
        while(substr($newcat,0,2)=='..'){
            $i++;
            $newcat=substr($newcat,2);
        }
        if(substr($newcat, 0,1) == ' '){
            $where = '('.$where.') OR (title='.$db->Quote(substr($newcat, 1)).' AND parent !=0)';
        }
	$db->setQuery('SELECT id FROM #__blogcategories WHERE '.$where);
	$id = $db->loadResult();
	return $id;
}


function _getBlogID($b){
	if($b!=false && $b!=0){
		if(@$GLOBALS['CACHE']['blog.categories']==false){
			if(ArtaCache::isUsable('blog', 'categories')){
				$GLOBALS['CACHE']['blog.categories'] = ArtaCache::getData('blog', 'categories');
			}else{
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__blogcategories');
				$byk=(array)$db->loadObjectList('id');
				ArtaCache::putData('blog', 'categories', $byk);
				$GLOBALS['CACHE']['blog.categories']=$byk;
			}

			if(!isset($GLOBALS['CACHE']['blog.categories'][$b])){
				$GLOBALS['CACHE']['blog.categories'][$b]=null;
			}
			return @$GLOBALS['CACHE']['blog.categories'][$b];
		}else{
			if(!isset($GLOBALS['CACHE']['blog.categories'][$b])){
				$GLOBALS['CACHE']['blog.categories'][$b]=null;
			}
			return @$GLOBALS['CACHE']['blog.categories'][$b];
		}
	}else{
		return false;
	}
}

function _getSiteDomain(){
	$p=ArtaURL::getPort();
	$c=ArtaLoader::Config();
	$url = $c->secure_site == 1 ? 'https://' : 'http://';
	if($c->secure_site==0 && $p== 443){
		$url='https://';
	}
	$url .= ArtaURL::getDomain();
	$url .= ($p== 80 || $p== 443) ? '' : ':'.$p ;
	
	return $url;
}


/*function addupThese($m){
	$a=$m->getParam(0); $a=$a->scalarVal();
	$b=$m->getParam(1); $b=$b->scalarVal();
	return new xmlrpcresp(new xmlrpcval($a+$b,'int'));
}*/
//$this->mapFunction('info.addupThese', 'addupThese', 'int', array('int','int'));
?>

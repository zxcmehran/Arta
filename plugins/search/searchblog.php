<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/14 20:27 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}


function plgSearchBlogLocations(){
	$l=ArtaLoader::Language();
	$l->addtoNeed('blog', 'package');
	return array('blog'=>trans('BLOG'));
}

function plgSearchBlog($phrase, $type, $order){
	$l=ArtaLoader::Language();
	$db=ArtaLoader::DB();
	$name=$db->Quote($l->getName());
	if((int)$type==2){
		$phrase=ArtaUTF8::strtolower($db->Quote('%'.$db->getEscaped($phrase, true).'%', false));
		$q="LOWER(bl.`title`) LIKE $phrase OR LOWER(bl.introcontent) LIKE $phrase OR LOWER(bl.morecontent) LIKE $phrase OR LOWER(bl.tags) LIKE $phrase";
		$q2="LOWER(t.value) LIKE $phrase";
	}else{
		$words=explode( ' ', $phrase);
		$wheres=array();
		$wheres2=array();
		foreach($words as $v){
			$v=ArtaUTF8::strtolower($db->Quote('%'.$db->getEscaped($v, true).'%', false));
			$wheres[]="LOWER(bl.`title`) LIKE $v OR LOWER(bl.introcontent) LIKE $v OR LOWER(bl.morecontent) LIKE $v OR LOWER(bl.tags) LIKE $v";
			$wheres2[]="(LOWER(t.value) LIKE $v AND t.row_field='title') OR (LOWER(t.value) LIKE $v AND t.row_field='introcontent') OR (LOWER(t.value) LIKE $v AND t.row_field='morecontent') OR (LOWER(t.value) LIKE $v AND t.row_field='tags')";
		}
		if($type==0){
			$q='('.implode(') AND (', $wheres).')';
			$q2='('.implode(') AND (', $wheres2).')';
		}else{
			$q='('.implode(') OR (', $wheres).')';
			$q2='('.implode(') OR (', $wheres2).')';
		}
	}
	if(ArtaUsergroup::getPerm('can_access_unpublished_posts', 'package', 'blog')==false){
		$suf=' AND b.enabled=1 AND b.pub_time < '.$db->Quote(ArtaDate::toMySQL(time())).' AND (b.unpub_time > '.$db->Quote(ArtaDate::toMySQL(time())).' OR b.unpub_time is NULL OR b.unpub_time=\'0000-00-00 00:00:00\' OR b.unpub_time=\'1970-01-01 00:00:00\' OR b.unpub_time=\'\') ';
		$suf2=' AND bl.enabled=1 AND bl.pub_time < '.$db->Quote(ArtaDate::toMySQL(time())).' AND (bl.unpub_time > '.$db->Quote(ArtaDate::toMySQL(time())).' OR bl.unpub_time is NULL OR bl.unpub_time=\'0000-00-00 00:00:00\' OR bl.unpub_time=\'1970-01-01 00:00:00\' OR bl.unpub_time=\'\') ';
	}else{
		$suf='';
		$suf2='';
	}
	
	$v=ArtaUTF8::strtolower($db->Quote('%'.$db->getEscaped($phrase, true).'%', false));
	$db->setQuery("SELECT b.id, b.title, b.introcontent, b.morecontent, b.denied, b.blogid, b.tags, b.hits, b.added_time
	FROM #__blogposts AS b JOIN #__languages_translations AS t ON (t.row_id=b.id) 
	WHERE t.group='blogpost' AND t.enabled=1 AND ($q2) AND t.row_field IN('title','introcontent','morecontent','tags') 
	      AND t.language=(SELECT DISTINCT id FROM #__languages WHERE client='site' AND LOWER(`name`)=$name) $suf
	UNION SELECT bl.id, bl.title, bl.introcontent, bl.morecontent, bl.denied, bl.blogid, bl.tags, bl.hits, bl.added_time 
	FROM #__blogposts AS bl 
	WHERE id NOT IN
	      (SELECT row_id FROM #__languages_translations AS tt WHERE tt.group='blogpost' AND tt.row_field='title' AND tt.enabled=1 
		   AND tt.language=(SELECT DISTINCT id FROM #__languages WHERE client='site' AND LOWER(`name`)=$name))
	AND ($q) $suf2
	LIMIT 0,50
	");
	$r=(array)$db->loadObjectList();
	switch($order){
		default:
		case 'popular':
			$r=ArtaUtility::sortByChild($r, 'hits', true);
		break;
		case 'newest':
			$r=ArtaUtility::sortByChild($r, 'added_time', true);
		break;
		case 'oldest':
			$r=ArtaUtility::sortByChild($r, 'added_time');
		break;
		case 'alpha':
			$r=ArtaUtility::sortByChild($r, 'title');
		break;
	}

	$db->setQuery('SELECT * FROM #__blogcategories');
	$c=ArtaUtility::keyByChild((array)$db->loadObjectList(), 'id');
	
	$plugin=ArtaLoader::Plugin();
	
	
	$con=ArtaLoader::Package();
	$con=$con->getSetting('show_blockmsg_for_denied_users', 0, 'blog');
	
	foreach($r as $k=>&$v){
		$plugin->trigger('onPrepareContent',array(&$v, 'blogpost'));
		$perms=array($c[$v->blogid]->accmask);
		$title=array($c[$v->blogid]->title);
		$x=$c[$v->blogid];
		while(isset($c[$x->parent])){
			$x=$c[$x->parent];
			$perms[]=$x->accmask;
			$title[]=$x->title;
		}
		krsort($title);
		krsort($perms);
		$v->category = trans('BLOG').' / '.implode(' / ',$title);
		$v->name='blog';
		$v->link='index.php?pack=blog&view=post&id='.$v->id;
		$v->text=$v->introcontent.' '.$v->morecontent;
		if((string)$v->tags!==''){
			$r[$k]->text .=' '.trans('TAGS').': '.$v->tags;
		}
		$perms=ArtaUsergroup::processAccessMask($perms);
		if(ArtaUsergroup::processDenied($v->denied)==false || ArtaUsergroup::processDenied($perms)==false){
			if($con==true){
				$r[$k]->text='<b>'.trans('DENIED_BLOCKMSG').'</b>';
			}else{
				unset($r[$k]);
			}
		}
		$r[$k]->text=strip_tags($v->text, $con ? '<b>' : '');
	}

	return $r;
}

?>
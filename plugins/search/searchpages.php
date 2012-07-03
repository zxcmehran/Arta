<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/14 20:27 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

function plgSearchPagesLocations(){
	$l=ArtaLoader::Language();
	$l->addtoNeed('pages', 'package');
	return array('pages'=>trans('PAGES'));
}

function plgSearchPages($phrase, $type, $order){
	$db=ArtaLoader::DB();

	if((int)$type==2){
		$phrase=ArtaUTF8::strtolower($db->Quote('%'.$db->getEscaped($phrase, true).'%', false));
		$q="LOWER(p.`title`) LIKE $phrase OR LOWER(p.desc) LIKE $phrase OR LOWER(p.tags) LIKE $phrase";
		$q2="LOWER(w.`title`) LIKE $phrase OR LOWER(w.content) LIKE $phrase";
		$q3="LOWER(p2.`title`) LIKE $phrase OR LOWER(p2.desc) LIKE $phrase OR LOWER(p2.content) LIKE $phrase OR LOWER(p2.tags) LIKE $phrase";
	}else{
		$words=explode( ' ', $phrase);
		$wheres=array();
		$wheres2=array();
		$wheres3=array();
		foreach($words as $v){
			$v=ArtaUTF8::strtolower($db->Quote('%'.$db->getEscaped($v, true).'%', false));
			$wheres[]="LOWER(p.`title`) LIKE $v OR LOWER(p.desc) LIKE $v OR LOWER(p.tags) LIKE $v";
			$wheres2[]="LOWER(w.`title`) LIKE $v OR LOWER(w.content) LIKE $v";
			$wheres3[]="LOWER(p2.`title`) LIKE $v OR LOWER(p2.desc) LIKE $v OR LOWER(p2.content) LIKE $v OR LOWER(p2.tags) LIKE $v";
		}
		if($type==0){
			$q='('.implode(') AND (', $wheres).')';
			$q2='('.implode(') AND (', $wheres2).')';
			$q3='('.implode(') AND (', $wheres3).')';
		}else{
			$q='('.implode(') OR (', $wheres).')';
			$q2='('.implode(') OR (', $wheres2).')';
			$q3='('.implode(') OR (', $wheres3).')';
		}
	}
	
	switch($order){
		default:
		case 'popular':
			$r='ORDER BY id ASC';
		break;
		case 'newest':
			$r='ORDER BY id DESC';
		break;
		case 'oldest':
			$r='ORDER BY id ASC';
		break;
		case 'alpha':
			$r='ORDER BY title ASC';
		break;
	}

	$db->setQuery("SELECT p.id, p.title, CONCAT(p.desc, ' ', p.tags) as `text`, p.denied, p.enabled, w.title as widtitle, w.content as `wid` FROM `#__pages` AS p JOIN `#__pages_widgets` AS w ON(p.id=w.pageid) WHERE ( $q ) OR ( $q2 ) UNION SELECT p2.id, p2.title, CONCAT(p2.content, ' ', p2.desc, ' ', p2.tags) as `text`, p2.denied, p2.enabled, '' as widtitle, '' as `wid` FROM `#__pages` AS p2 WHERE $q3 $r LIMIT 0,50");
	$r=(array)$db->loadObjectList();
	
	
	$data=array();
	foreach($r as $k=>$v){
		if(isset($data[$v->id])){
			$data[$v->id]->text.=' '.htmlspecialchars($v->widtitle).': '.$v->wid;
		}else{
			$data[$v->id]=$v;
			$data[$v->id]->text.=' '.htmlspecialchars($v->widtitle).': '.$v->wid;
		}
	}
	
	
	unset($k,$v);
	
	$can=ArtaUserGroup::getPerm('can_access_unpublished_pages', 'package', 'pages');
	
	foreach($data as $k=>&$v){
		$v->category = trans('PAGES');
		$v->name='pages';
		$v->link='index.php?pack=pages&pid='.$v->id;

		if(ArtaUsergroup::processDenied($v->denied)==false || ($v->enabled==false && $can==false)){
			unset($r[$k]);
		}
		$v->text=strip_tags($v->text);
	}

	return $data;
}


?>
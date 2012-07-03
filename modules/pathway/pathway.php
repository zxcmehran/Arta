<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 18:44 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

$p=ArtaLoader::Pathway();
$R=$p->getResult();

if($_SERVER['IS_HOMEPAGE']){
	$c=ArtaLoader::Config();
	$R=array($c->site_name);
	$p->links=array();
}
$x=array();
foreach($R as $k=>$v){
	$link=$p->getLink($k);
	if($link!=null && $v!=null){
		$x[]='<a href="'.htmlspecialchars($link).'">'.htmlspecialchars($v).'</a>';
	}
}
echo "\n<!-- Breadcrumbs -->\n<span class=\"pathway_container\">\n<nav>".implode("<span class=\"pathway_separator\"></span>\n", array_merge(array('<a href="'.ArtaURL::getSiteURL(true, true).'"><img src="'.imageset('home_small.png').'"/></a>'),$x))."</nav>\n</span>\n";

?>
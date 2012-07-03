<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/14 18:48 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

$config=ArtaLoader::Config();
ArtaTagsHtml::addHeader('<link rel="search" type="application/opensearchdescription+xml" title="'.htmlspecialchars($config->site_name).' - '.trans('SEARCH').'" href="index.php?pack=search&task=getXML" />
'."\n\t");

$searchC=new SearchController;
$searchC->exec(getVar('task', 'Display'));

?>
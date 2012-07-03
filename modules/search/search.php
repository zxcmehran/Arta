<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 18:44 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
$config=ArtaLoader::Config();
ArtaTagsHtml::addHeader('<link rel="search" type="application/opensearchdescription+xml" title="'.htmlspecialchars($config->site_name).' - '.trans('SEARCH').'" href="index.php?pack=search&task=getXML" />
'."\n\t");
?>
<nav>
<form action="index.php?pack=search&view=search&task=search" method="get" enctype="text/plain" onsubmit="if($('searchPhrase').value=='<?php echo trans('SEARCH') ?> ...'){$('searchPhrase').value='';}">
<input type="hidden" value="search" name="pack" />
<input type="hidden" value="search" name="view" />
<input type="hidden" value="search" name="task" />

<input type="text" id="searchPhrase" name="phrase" value="<?php
	$v= getVar('pack', null)=='search'? getVar('phrase', null):trans('SEARCH').' ...';
	echo $v;
?>" class="acceptRet searchfield" <?php if(getVar('pack', null)!=='search'){
	echo 'onfocus="if(this.value==\''.trans('SEARCH').' ...\'){this.value=\'\';}" onblur="if(this.value==\'\'){this.value=\''.trans('SEARCH').' ...\';}"';
} ?>/> <input class="acceptRet searchsubmit" type="submit" value="<?php echo trans('GO'); ?>" />
</form>
</nav>
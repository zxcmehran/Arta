<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/15 20:32 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}



function plgShowRatingControls(&$row){
	echo '<div class="blogpost_rating" id="the_rating_container">';
	ArtaTagsHtml::addLibraryScript('livepipe_rating');
	$p=ArtaLoader::Package();
	$max = $p->getSetting('max_possible_rating_value', 5, 'blog','site');
	
	$t=ArtaLoader::Template();
	@$r=round($row->rating/$row->rate_count, 1);
	if($r>$max){
		$r=$max;
	}
	$t->addtoTmpl('<script>new Control.Rating("the_rating_container", {value: '.$r.', max:'.(int)$max.', updateUrl:"'.makeURL('index.php?pack=blog&task=rate&token='.ArtaSession::genToken().'&id='.$row->id).'"})</script>', 'beforebodyend');
	
	echo '</div><br><br>';
	
}

?>
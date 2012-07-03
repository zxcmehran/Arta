<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/15 20:32 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}


function plgEmbedContent(&$c, $scope, $is_public=false){
	if($is_public){
		return;
	}
	$om=ArtaLoader::OEmbed();
	preg_match_all('@\{embed([^\{\}]*)\}([^\{\}]+)\{/embed\}@i', $c, $match);
	
	foreach($match[2] as $k=>$m){
		$atts=array();
		preg_match('#maxwidth="?([0-9]+)"?#i', $match[1][$k],$atts['maxwidth']);
		preg_match('#maxheight="?([0-9]+)"?#i', $match[1][$k],$atts['maxheight']);
		if(@is_numeric($atts['maxwidth'][1])){
			$atts['maxwidth']=$atts['maxwidth'][1];
		}else{
			unset($atts['maxwidth']);
		}
		
		if(@is_numeric($atts['maxheight'][1])){
			$atts['maxheight']=$atts['maxheight'][1];
		}else{
			unset($atts['maxheight']);
		}
		
		$embed=$om->getResult($m,$atts);
		if($embed==false){
			$embed='<a href="'.htmlspecialchars($m).'">'.$m.'</a>';
		}
		$c = str_replace($match[0][$k], $embed, $c);
	}
}

?>
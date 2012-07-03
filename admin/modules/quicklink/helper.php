<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ModuleQuicklinkHelper{
	function makeHotkeysActive($data){
		ArtaTagsHtml::addLibraryScript('livepipe_hotkey');
		$res="<script>\n";
		foreach($data as $v){
			if($v->acckey !== null){
				$keys= explode('+', $v->acckey);
				if(in_array('ctrl', $keys)){
					$ctrl= 'true';
				}else{
					$ctrl='false';
				}
				if(in_array('shift', $keys)){
					$shift= 'true';
				}else{
					$shift='false';
				}
				if(in_array('alt', $keys)){
					$alt= 'true';
				}else{
					$alt='false';
				}
				$key=$keys[count($keys)-1];
				$v->link=($v->link);
				$res .= "new HotKey('{$key}', function(event){"."document.location.href='{$v->link}';},{"."shiftKey: {$shift}, altKey: {$alt}, ctrlKey: {$ctrl}});\n";
			}
		}
		$res .="</script>\n";
		$t=ArtaLoader::Template();
		$t->addtoTmpl($res, 'beforebodyend');
	}
}
?>
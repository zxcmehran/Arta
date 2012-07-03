<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2010/06/16 18:43 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

function plgGuessUserlang(){
    if(isset($_COOKIE['guess_userlang_disappear'])){
        return;
    }
	$db=ArtaLoader::DB();
	ArtaLoader::Import('http->browser');	
	$langs= ArtaBrowser::getLanguages();
	if(@strtolower($langs[0])==strtolower(trans('_LANG_ID')) || 
		@strtolower($langs[0])==strtolower(trans('_LANG_ID_NAME'))){
		return;
	}
	$l=array();
	if(count($langs)){
		foreach($langs as $k=>$lng){
			if(strtolower($lng)!=strtolower(trans('_LANG_ID')) && 
				strtolower($lng)!=strtolower(trans('_LANG_ID_NAME'))){
				$l[] = $db->Quote($lng);
			}else{
				unset($langs[$k]);
			}
		}
	}else{
		return;
	}
	$db->setQuery('SELECT LOWER(`name`) AS `name`, `name` AS `_name`,`title` FROM #__languages WHERE `client`=\'site\' AND `name` IN ('.implode(', ',$l).')');
	$r=$db->loadObjectList('name');
	foreach($langs as $ln){
		$ln=strtolower($ln);
		if(isset($r[$ln])){
			$language=$r[$ln];
			break;
		}
	}
	if(!isset($language)){
		return;
	}
	$r=$language;
	$q=ArtaURL::getQuery();
	$q=ArtaURL::breakupQuery($q);
	$q['language']=$r->_name;
	$link='<a style="text-shadow: 0 0 8px #0174FF;" href="index.php?'.ArtaURL::makeupQuery($q).'" hreflang="'.htmlspecialchars($r->_name).'">'.$r->title.'</a>';
	$c=sprintf(trans('ITS ALSO AVAILABLE ON X LANG'),$link,$link);
	
	$t=ArtaLoader::Template();
	ArtaTagsHTML::addCSS('media/styles/msg.css');
    $c.='<span style="float:right; font-size:16px; text-align:center; color:red; border: 1px solid #D1C401; width:17px;height:17px; font-weight:bold;cursor:pointer;border-radius:10px;" onclick="new Effect.Fade($(\'language_guess_bar\'));Cookie.set(\'guess_userlang_disappear\', 1);">&times;</span>';
	$t->addtoTmpl('<div class="message" style="border-color: gray; background: #FFF8C3;text-align:center;" id="language_guess_bar">'.$c.'</div>', 'message');
}

?>

<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/16 18:44 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
$m=$this->getModel();
$m->getInfo();
if($m->u>0 && $m->g>0){
	printf(trans('WE HAVE _ U AND _ G ONLINE'), $m->u, $m->g);
}elseif($m->u==0 && $m->g>0){
	printf(trans('WE HAVE _ G ONLINE'),  $m->g);
}elseif($m->u>0 && $m->g==0){
	printf(trans('WE HAVE _ U ONLINE'),  $m->u);
}else{
	print trans('WE HAVE NO ONLINE');
}
if($this->getSetting('show_onlines', 0) && @count($m->on)>0){
	if($this->getSetting('link_onlines_profile', 0)){
		// unset other okays if set in past
		if(isset($ok)) unset($ok);
		$ok=true;
	}
	$r=array();
	echo '<br><br><p>'.trans('ONLINE USERS').': ';
	foreach($m->on as $v){
		if(isset($ok)){
			$r[]='<a href="index.php?pack=user&view=profile&uid='.$v->id.'">'.htmlspecialchars($v->username).'</a>';
		}else{
			$r[]=htmlspecialchars($v->username);
		}
	}
	echo implode(', ',$r);
	echo '</p>';
}
$p=ArtaLoader::Plugin();
$p->trigger('onShowStats');
?>

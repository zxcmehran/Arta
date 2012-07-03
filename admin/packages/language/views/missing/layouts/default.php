<?php if(!defined('ARTA_VALID')){die('No access');}
echo '<fieldset><legend>'.trans('SITE').'</legend>';
if(count($this->get('site'))>0){
	foreach($this->get('site') as $l){
		echo '<h3>'.htmlspecialchars($l->title).' ('.htmlspecialchars($l->name).')'.'</h3>';
		if(count($l->missing)>0){
			echo '<ul>';
				foreach($l->missing as $k =>$miss){
					echo '<li><a href="index.php?pack=language&view=missing&type=ini&fn='.base64_encode($k).'" title="'.trans('DOWNLOAD ORIGINAL ENGLISH FILE').'"> <img width="16" height="16" src="'.Imageset('download.png').'"/> </a>'.htmlspecialchars($miss).'</li>';
				}
			echo '</ul>';
		}else{
			echo ArtaTagsHtml::msgBox(trans('NO MISSING ITEMS FOUND'));
		}
	}
}else{
	echo ArtaTagsHtml::msgBox(trans('NO MISSING ITEMS FOUND'));
}
echo '</fieldset>';
echo '<fieldset><legend>'.trans('ADMIN').'</legend>';
if(count($this->get('admin'))>0){
	foreach($this->get('admin') as $l){
		echo '<h3>'.htmlspecialchars($l->title).' ('.htmlspecialchars($l->name).')'.'</h3>';
		if(count($l->missing)>0){
			echo '<ul>';
				foreach($l->missing as $k =>$miss){
					echo '<li><a href="index.php?pack=language&view=missing&type=ini&fn='.base64_encode($k).'" title="'.trans('DOWNLOAD ORIGINAL ENGLISH FILE').'"> <img width="16" height="16" src="'.Imageset('download.png').'"/> </a>'.htmlspecialchars($miss).'</li>';
				}
			echo '</ul>';
		}else{
			echo ArtaTagsHtml::msgBox(trans('NO MISSING ITEMS FOUND'));
		}
	}
}else{
	echo ArtaTagsHtml::msgBox(trans('NO MISSING ITEMS FOUND'));
}
echo '</fieldset>';
?>
<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 3 $
 * @date		2009/10/29 18:02 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

ArtaLoader::Import('packages->blog->models->post', 'client');
$m=new BlogModelPost('post');
$p=$m->getPost($settings['postid_to_show'], false);
if($p=='NOT_AUTHORIZED'){
	echo '<b>'.trans('DENIED_BLOCKMSG').'</b>';
	return true;
}

echo '<article><header>';
if($settings['show_post_link']){
	echo '<h1><a href="index.php?pack=blog&view=post&id='.$p->id.'">'.htmlspecialchars($p->title).'</a></h1>';
}else{
	echo '<h1>'.htmlspecialchars($p->title).'</h1>';
}
echo '</header><p>';
$plug=ArtaLoader::Plugin();
$con=$settings['show_full_content']?$p->introcontent.$p->morecontent:$p->introcontent;
$plug->trigger('onShowBody', array(&$con, $settings['show_full_content']?'blogpost':'blogpost-intro'));
echo $con;
echo '</p>';
if($settings['show_post_link'] && $settings['show_full_content']==false){
	echo '<footer><a href="index.php?pack=blog&view=post&id='.$p->id.'">'.trans('READMORE').'</a></footer>';
}
echo '</article>';
?>
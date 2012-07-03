<?php if(!defined('ARTA_VALID')){die('No access');}
ArtaTagsHtml::addHeader('<style>
td.staticpage_icon {
	width: 16px;
	height: 16px;
	text-align:center;
	max-width: 16px;
}
</style>');
$p=ArtaLoader::Plugin();
$v= $this->get('page');
?>
<article>
	<header>
		<h2 class="staticpage_title"><a href="index.php?pack=pages&pid=<?php echo $v->id ?>"><?php echo htmlspecialchars($v->title)?></a></h2>
		<?php $p->trigger('onBeforeShowPage', array(&$v)); ?>
	</header>
	<br/>
<div class="staticpage_content">
<?php 
$p->trigger('onShowBody', array(&$v->content, 'page'));
echo $v->content;
?>
</div>

<br/><br/>

<?php $p->trigger('onAfterShowPage', array(&$v)); ?>

<footer>
	<table class="staticpage_footers" align="<?php echo trans('_LANG_DIRECTION')=='ltr'?'right':'left' ;?>">
	<tr>
	<td class="staticpage_icon"><?php echo '<!-- tags: '.htmlspecialchars($v->tags).'  -->'; 
$tg=(array)explode(',', $v->tags);
foreach($tg as $k=>$tag){
	if(trim($tag)==''){
		unset($tg[$k]);
	}
}

if(count($tg)>0){
	echo '<img src="'.Imageset('tag.png').'" alt="'.trans('TAGS').'" title="'.htmlspecialchars(implode(', ', $tg)).'"/>';
}
?>
	</td>

	<td class="staticpage_icon"><?php 
if($this->getSetting('staticpages_generate_pdf_version', true)){
	echo '<a href="index.php?pack=pages&type=pdf&pid='.$v->id.'" target="_blank"><img src="'.Imageset('pdf_small.png').'" alt="PDF" title="PDF"/></a>';
}?>
	</td>

	<td class="staticpage_icon">
<?php 
if($this->getPerm()){
	echo '<a href="index.php?pack=pages&view=new&pid='.$v->id.'"><img src="'.Imageset('edit_small.png').'" alt="'.trans('edit page').'" title="'.trans('edit page').'"/></a>';
}?>
	</td>
	
	<td class="staticpage_icon">
<?php 
if(count($v->langs)>0){
	echo '<img width="16" height="16" src="'.Imageset('languages.png').'" alt="'.trans('AVAILABLE TRANSLATIONS').'" title="'.trans('AVAILABLE TRANSLATIONS').': '.htmlspecialchars(implode(', ',$v->langs)).'"/>';
}?>
	</td>
	
	</tr>
	</table>
</footer>
</article>
<?php

$p->trigger('onAfterShowPageCompletely', array(&$v));

?>
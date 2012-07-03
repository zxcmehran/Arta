<?php
if(!defined('ARTA_VALID')){die('No access');}
ArtaTagsHtml::addHeader('<style>
table.blogpost_footers td{
	width: 99%;
}
td.blogpost_icon {
	height: 16px;
	text-align:center;
/*	width: 16px;
	max-width: 16px;
	width:auto !important;*/ 
	width: 16px;
}
</style>');
$m=$this->get('m');
$v=$this->get('post');

$p=ArtaLoader::Plugin();
?>
<article class="blogpost">
	<header>
		<h2 class="blogpost_title"><a href="index.php?pack=blog&view=post&id=<?php echo $v->id ?>"><?php echo htmlspecialchars($v->title)?></a></h2>

		<table class="blogpost_headers" width="100%">
		<tr>
			<td width="%30" nowrap="nowrap"><?php echo trans('ADDED_BY')?>: <a href="index.php?pack=user&view=profile&uid=<?php echo $v->added_by_id ?>"><?php echo htmlspecialchars($v->added_by)?></a></td>
			<td width="40%"><!-- added_time: <?php echo ArtaDate::Translate($v->added_time, 'r')?> --><?php echo trans('ADDED_TIME').' <time datetime="'.ArtaDate::toHTML5($v->added_time).'" pubdate>'.ArtaDate::_($v->added_time).'</time>'?></td>
			<td width="30%"><!-- hits: <?php echo $v->hits;?>  --><?php echo trans('hits').': '.$v->hits?></td>
		</tr>

		<tr>
		<td colspan="3" width="100%">
		<!-- category: <?php echo implode('->',$this->get('cats'))?>  --><?php echo trans('CATEGORY').': <a href="index.php?pack=blog&view=index&blogid='.$v->blogid->id.'">'.implode('<span class="pathway_separator"></span>',$this->get('cats'))."</a>" ?></td>
		</tr>

		<?php $p->trigger('onAfterShowPostHeaders', array(&$v)); ?>

		</table>
	</header>
	
<br/>
<?php $p->trigger('onBeforeShowPost', array(&$v)); ?>


<div class="blogpost_content">
<?php 
$_content=$v->introcontent.$v->morecontent;
$p->trigger('onShowBody', array(&$_content, 'blogpost'));
echo $_content;
?>
</div>

<br/><br/>

<?php $p->trigger('onAfterShowPost', array(&$v)); ?>
	<footer>
		<table width="100%" class="blogpost_footers">
		<?php 
	if($v->mod_time && $v->mod_time!=='0000-00-00 00:00:00' && $v->mod_time!=='1970-01-01 00:00:00'){
		echo '<tr><td colspan="3">'.trans('MOD_BY').' <a href="index.php?pack=user&view=profile&uid='.$v->mod_by_id.'">'.htmlspecialchars($v->mod_by)."</a></td></tr>\n";
		echo '<tr><td colspan="3"><!-- mod_time: '.ArtaDate::Translate($v->mod_time, 'r').' -->'.trans('MOD_TIME').' <time datetime="'.ArtaDate::toHTML5($v->mod_time).'">'.ArtaDate::_($v->mod_time)."</time></td></tr>\n";
	}
	?>
		<tr>
		<td style="width:60%;">
			<?php 
	if(count($v->attachments) > 0){
		echo '<a name="attachments"></a><fieldset><legend>'.trans('POST ATTACHMENTS').'</legend><ol class="attachments">';
		foreach($v->attachments as $atk=>$at){
			if(trim($atk)==''){
				$atk=$at;
			}
			$img=$this->selectIcon($at);
			echo '<li style="list-style: none;"><a style="vertical-align:middle;" target="_blank" href="'.$at.'"><img style="vertical-align:middle;" src="'.$img.'" width="16" height="16" /> '.$atk.'</a></li>';
		}
		echo '</ol></fieldset>';
	}

	?>
		</td>


		<td><?php echo '<!-- tags: '.htmlspecialchars($v->tags).'  -->'.trans('TAGS').': <p>'; 
	$tg=(array)explode(',', $v->tags);
	$tgo=explode(',', $v->_tags);
	foreach($tg as $k=>&$tag){
		if(trim($tag)!=''){
			$tag='<a href="index.php?pack=blog&tagname='.htmlspecialchars(strtolower(trim($tgo[$k]))).'">'.$tag.'</a>';
		}else{
			unset($tg[$k]);
		}
	}
	echo implode(', ', $tg);

	if(count($tg)<=0){
		echo trans('NONE');
	}
	?></p>
		</td>

		<td class="blogpost_icon" valign="top"><?php 
	if($this->getSetting('blogposts_generate_pdf_version', true)){
		echo '<a href="index.php?pack=blog&view=post&type=pdf&id='.$v->id.'" target="_blank"><img src="'.Imageset('pdf_small.png').'" alt="PDF" title="PDF"/></a>';
	}?>
		</td>

		<td class="blogpost_icon" valign="top">
	<?php 
	$u=$this->getCurrentUser();

	if(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog') && 
	($v->added_by==null || $v->added_by==$u->id || ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog')!==false)){
		echo '<a href="index.php?pack=blog&view=new&id='.$v->id.'"><img src="'.Imageset('edit_small.png').'" alt="'.trans('edit post').'" title="'.trans('edit post').'"/></a>';
	}?>
		</td>

		<td class="blogpost_icon" valign="top">
	<?php 
	if(count($v->langs)>0){
		echo '<img width="16" height="16" src="'.Imageset('languages.png').'" alt="'.trans('AVAILABLE TRANSLATIONS').'" title="'.trans('AVAILABLE TRANSLATIONS').': '.htmlspecialchars(implode(', ',$v->langs)).'"/>';
	}?>
		</td>

		</tr>
		</table>
	</footer>
</article> <!-- end <article class="blogpost"> -->

<br />

<?php 
$c=ArtaLoader::Config();

ArtaTagsHtml::addRSS('index.php?pack=blog&view=last&type=xml&blogid='.$v->blogid->id, $v->blogid->title.' - '.$c->site_name);

ArtaTagsHtml::addRSS('index.php?pack=blog&view=last&type=xml', trans('BLOG').' - '.$c->site_name);


$p->trigger('onAfterShowPostCompletely', array(&$v));

if($this->getSetting('commenting_system', true)==true){
	echo '<section>';
	 // Go for comments
	echo '<h3>'.trans('COMMENTS').'</h3>';
	
	if($this->get('can')==true){
		$this->render('comments');
	}else{
		echo trans('YOU CANNOT SEE COMMENTS');
	}
	echo '</section>';
}
?>
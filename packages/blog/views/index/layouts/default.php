<?php
if(!defined('ARTA_VALID')){die('No access');}

$id=getVar('blogid', 0, '','int');


$m=$this->get('m');

echo '<section>';
echo '<header><h2>'.trans('SUBCATEGORIES').':</h2></header>';
if(is_array($this->get('cats'))==false || count($this->get('cats'))==0){
	// however its impossible...
	echo ArtaTagsHtml::msgBox(trans('NO SUBCATEGORIES'));
}else{
	echo '<nav><table class="content_table categories" align="center">';
	?>
	<thead>
		<tr>
			<th width="20" style="text-align:center">#</th>
			<th><?php echo trans('CATEGORY TITLE'); ?></th>
			<th width="75" nowrap="nowrap" style="text-align:center"><?php echo trans('POSTS'); ?></th>
		</tr>
	</thead>
	<tbody>	
	<?php
	$cats=$this->get('cats');
	foreach($cats as &$cat){
		$x=0;
		while(substr($cat->title,0,2)=='..'){
			$x++;
			$cat->title=substr($cat->title,2);
		}
		$cat->level=$x;
	}
	$i=1;
	$j=0;
	foreach($this->get('cats') as $cat){
		$prefix = str_repeat('&nbsp;.&nbsp;&nbsp;', $cat->level);
		echo '<tr class="row'.$j.'"><td align="center" class="content_details1">'.$i.'</td>';
		echo '<td class="content_title">';
		echo $prefix;
		echo '<a href="index.php?pack=blog&view=index&blogid='.$cat->id.'">';
		echo $id==$cat->id ? '<b>'.htmlspecialchars($cat->title).'</b>' : htmlspecialchars($cat->title) ;
		echo '</a>';
		
		$c = $m->getPostCount($cat->id);
		
		echo '</td><td class="content_details1"><small>'.sprintf(trans('_ POSTS'), '<big style="font-weight:bold; font-size:200%;">'.$c.'</big>').'</small></td>';
		
		echo '</tr>';
		$i++;
		$j= $j==0 ? 1 : 0;
	}
	echo '</tbody></table></nav>';
}
echo '</section>';

$b=$m->getBlogID($id);
if(@trim((string)$b->desc)!=''){
	echo '<article>';
	echo '<header><h3>'.trim((string)$b->title).'</h3></header>';
	echo '<p>'.trim((string)$b->desc).'</p>';
	echo '</article>';
}

echo '<section><header>';
if((int)$id>0 || getVar('tagname','','','string')!=''){
	echo '<a style="padding-top:15px;float:'.(trans('_LANG_DIRECTION')=='ltr'?'right':'left').'" href="index.php?pack=blog&view=index">'.trans('SHOW ALL BLOG POSTS').'</a><br/>';
}

if((string)getVar('tagname', '', '', 'string')!=''){
	$title=' ('.(trans('TAGGED').' "'.getVar('tagname', '','','string').'"').') ';
}else{
	$title='';
}

echo '<h2>'.($id !==0 ? trans('POSTS IN THIS CAT') : trans('ALL POSTS')).$title.':</h2></header>';

$items=$this->get('items');
if(is_array($items)==false || count($items)==0){
	echo ArtaTagsHtml::msgBox(trans('NO POSTS FOUND'));
}elseif(count($items)==1 && @$items['unset']!=NULL){
	echo ArtaTagsHtml::msgBox(trans('YOU ARE NOT AUTHORIZED'));
}else{
	
	echo '<nav><table class="content_table posts" align="center">';
	?>
	<thead>
		<tr>
			<th width="20" style="text-align:center">#</th>
			<th><?php echo trans('POST TITLE'); ?></th>
			<th width="75" nowrap="nowrap" style="text-align:center"><?php echo trans('HITS'); ?></th>
		
		</tr>
	</thead>
	<tbody>	
	<?php
	$u= $this->getCurrentUser();
	$i=1;
	$j=0;
	foreach($items as $v){		
		
		echo '<tr class="row'.$j.'"><td align="center" class="content_details1">'.$i.'</td>';
		if(ArtaUsergroup::getPerm('can_addedit_posts', 'package', 'blog') && 
		($v->added_by==null || $v->added_by==$u->id || ArtaUsergroup::getPerm('can_edit_others_posts', 'package', 'blog')!==false)){
			$edit= '<a href="index.php?pack=blog&view=new&id='.$v->id.'"><img src="'.Imageset('edit_small.png').'" alt="'.trans('edit post').'" title="'.trans('edit post').'"/></a>';
		}else{
			$edit='';
		}
		
		echo '<td class="content_title">';
		echo '<table class="celltable"><tr><td>';
		echo '<a href="index.php?pack=blog&view=post&id='.$v->id.'">';
		echo  htmlspecialchars($v->title) ;
		echo '</a></td><td class="details" align="'.(trans('_LANG_DIRECTION') == 'ltr' ? 'right' : 'left').'">'.$edit.'</td></tr>';
		echo '<tr><td class="details">';
		
		
		echo '<!-- added_time: '.ArtaDate::Translate($v->added_time, 'r').' --><time datetime="'.ArtaDate::toHTML5($v->added_time).'" pubdate>'.ArtaDate::_($v->added_time).'</time>';
		echo '</td><td class="details" nowrap="nowrap" align="'.(trans('_LANG_DIRECTION') == 'ltr' ? 'right' : 'left').'"> ';
		$r=@round((int)$v->rating/(int)$v->rate_count);
		if($r >0){
			echo str_repeat('<img src="'.Imageset('rating_star.png').'" alt="*"/>', $r);
		}
		if($v->attachments > 0){
			echo ' <a href="index.php?pack=blog&view=post&id='.$v->id.'#attachments"><img src="'.Imageset('attachment.png').'" alt="'.$v->attachments.' '.trans('ATTACHMENTS').'" title="'.$v->attachments.' '.trans('ATTACHMENTS').'"/></a>';
		}
		
		if($v->comments > 0 && ArtaUsergroup::getPerm('can_access_post_comments', 'package', 'blog')){
			echo ' <a href="index.php?pack=blog&view=post&id='.$v->id.'#comment-1"><img src="'.Imageset('comment.png').'" alt="'.$v->comments.' '.trans('COMMENTS').'" title="'.$v->comments.' '.trans('COMMENTS').'"/></a>';
		}
		
		if(strlen($v->tags) > 0){
			$tags=explode(',',$v->tags);
			array_map('trim', $tags);
			$v->tags=implode(', ',$tags);
			echo ' <img src="'.Imageset('tag.png').'" alt="'.trans('TAGS').': '.htmlspecialchars($v->tags).
			' " title="'.trans('TAGS').': '.htmlspecialchars($v->tags).'"/>';
		}

		if(count($v->langs)>0){
			echo ' <img width="16" height="16" src="'.Imageset('languages.png').'" alt="'.trans('AVAILABLE TRANSLATIONS').': '.htmlspecialchars(implode(', ',$v->langs)).'" title="'.trans('AVAILABLE TRANSLATIONS').': '.htmlspecialchars(implode(', ',$v->langs)).'"/>';
		}

		
		echo '</td></tr></table>';
		$c = (int)$v->hits;
		
		echo '</td><td class="content_details1"><small>'.sprintf(trans('_ HITS'), '<big style="font-weight:bold; font-size:200%;">'.$c.'</big>').'</small></td>';
		
		echo '</tr>';
		$i++;
		$j= $j==0 ? 1 : 0;
	}
	echo '</tbody></table>';
	

	
	
	if(getVar('tagname','','','string')!=''){
		$suf='&tagname='.urlencode(getVar('tagname','','','string'));
	}else{
		$suf='';
	}
	
	$c=ArtaLoader::Config();
	$rsspath=trim((string)$this->getSetting('rssfeed_alternate_url'));
	if($rsspath==''){
		$rsspath='index.php?pack=blog&view=last&type=xml'.$suf;
	}
	
	if($id>0){
		$ar=@array_shift($this->get('items'));
		ArtaTagsHtml::addRSS('index.php?pack=blog&view=last&type=xml&blogid='.$id.$suf, $ar->blogid->title.' - '.$c->site_name);
		$url="index.php?pack=blog&blogid=".$id.$suf;
		$xurl="index.php?pack=blog&type=xml&blogid=".$id.$suf;
	}else{
		$url="index.php?pack=blog".$suf;
		$xurl=$rsspath;
	}
	
	ArtaTagsHtml::addRSS($rsspath, trans('BLOG').' - '.$c->site_name);
		
	echo '<table width="100%" class="blogpost_controls"><tr><td>'.ArtaTagsHtml::SortControls(array(
		'added_time'=>trans('added_time'),
		'hits'=>trans('hits'),
		'rating'=>trans('rating')
	), 'added_time', 'desc').'</td><td nowrap="nowrap"><a href="'.$xurl.'"><img src="'.Imageset('rss.png').'"/>'.trans('RSS FEED').'</a> &nbsp; <a href="'.$url.'">'.trans('BLOG LAYOUT').'</a></td></tr></table>';
	
	
	echo ArtaTagsHtml::LimitControls($this->get('count'));
	
	echo '</nav>';
	
}

echo '</section>';
?>
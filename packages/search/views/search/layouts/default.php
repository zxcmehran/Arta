<?php
if(!defined('ARTA_VALID')){die('No access');}
ArtaTagsHtml::addCSS('packages/search/assets/style.css');
?>
<section>
	<nav>
<form action="index.php?pack=search&view=search&task=search" method="get" enctype="text/plain">
<input type="hidden" value="search" name="pack" />
<input type="hidden" value="search" name="view" />
<input type="hidden" value="search" name="task" />
<table><tr><td>
<?php
	echo trans('SEARCH PHRASE');
?>: <input type="text" name="phrase" value="<?php
	echo getVar('phrase', null);
?>" class="acceptRet" /> <input type="submit" value="<?php echo trans('GO'); ?>" />
</td>
<td> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
echo trans('ORDER_RES_BY').': ';
	$order=getVar('order', 'popular', '', 'string');
		if(!in_array($order, array('newest','oldest','popular','alpha'))){
			$order='popular';
		}
	echo ArtaTagsHtml::select('order', array(
	'popular'=>trans('popular'),
	'newest'=>trans('newest'),
	'oldest'=>trans('oldest'),
	'alpha'=>trans('alpha')
	), $order);
?>
</td>
</tr>
<tr><td>
<?php
	$at=getVar('at', array(), '', 'array');
	foreach($this->get('plugins') as $k=>$v){
		echo ArtaTagsHtml::checkbox("at[".$k."]", 1, $v, @(bool)$at[$k]).' ';
	}	
?>
</td><td> &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
<?php
	echo trans('match').': '.ArtaTagsHtml::select("match_type", array(0=>trans('ALLWORDS'),1=>trans('ANYWORD'),2=>trans('EXACT')), getVar('match_type', 0, '', 'int'));
?>
</td></tr>
</table>
</form>
	</nav>
	<br/>
<?php
	$res=$this->get('result');
	if($res===''){
		$res=null;
	}
	if(getVar('phrase')!==null){
	if($res===false){
		echo '<b>'.trans('SHORT PHRASE').'</b>';
	}elseif(count($res)==0){
		echo '<b>'.trans('not_found').'</b>';
	}else{
?><footer><b>
<?php
	printf(trans('search took _ seconds'), $this->get('past'), $this->get('count'));
	$this->setTitle(trans('SEARCH RESULTS FOR').': '.htmlspecialchars(getVar('phrase', null, '', 'string')));
?>
</b></footer>
<br /><br />

<?php
	foreach($res as $v){
		?>
<article>
	<header><h3 class="searchTitle"><a href="<?php
	echo htmlspecialchars($v->link);
?>"><?php
	echo htmlspecialchars($v->title);
?></a></h3>
	<div class="searchCat"><?php
	echo htmlspecialchars($v->category);
?></div>
	</header>
	<div class="searchText"><?php
	echo $v->text;
?></div>
	<footer>
	<div class="searchLink"><?php
	echo ArtaURL::getURL(array('path'=>makeURL($v->link),'query'=>'', 'path_info'=>''));
?></div>
	<hr class="searchSeparator" />
	</footer>
</article>
		<?php
	}
?>

<nav>
<?php
echo ArtaTagsHtml::LimitControls($this->get('count'));
?>
</nav>
<?php
	}
	}
?>
</section>
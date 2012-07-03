<?php if(!defined('ARTA_VALID')){die('No access');} 
if(!isset($GLOBALS['_ADMINTABS'])){
	$GLOBALS['_ADMINTABS']=array();
}
$data=$GLOBALS['_ADMINTABS'];
?>
<div class="tabs_container">
<ul class="tabs">
<?php 
foreach($data as $k=>$v){
	$q=explode('&', $_SERVER['QUERY_STRING']);
	if(@$q['limit']){unset ($q['limit']);}
	if(@$q['limitstart']){unset ($q['limitstart']);}
	if(@$q['order_by']){unset ($q['order_by']);}
	if(@$q['order_dir']){unset ($q['order_dir']);}
	$q=implode('&',$q);
	if($k=='index.php?'.$q){
		$ac= ' class="active"';
	}else{
		$ac='';
	}
		?>
	<li<?php echo $ac; ?>><a style="float:<?php echo trans('_LANG_DIRECTION')=='ltr'?'left':'right';?>" href="<?php echo htmlspecialchars($k); ?>"<?php echo $ac; ?>><?php echo htmlspecialchars($v); ?></a></li>
<?php
}
?>
</ul>
</div>
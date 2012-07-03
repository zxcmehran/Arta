<?php if(!defined('ARTA_VALID')){die('No access');} 
ArtatagsHtml::addCSS('modules/quicklink/assets/style.css');
?>
<div style="position:absolute;top:0px;left:0px;">
<div id="fisheye_container" style="position:fixed;<?php
	if(@strpos($_SERVER['HTTP_USER_AGENT'], 'MSIE')<=0){
		echo 'filter:alpha(opacity=90);-moz-opacity:0.9;opacity:0.9;';
	}
	if(@$_COOKIE['toolbar_xy']){
		$xy=explode(',',$_COOKIE['toolbar_xy']);
		echo 'top:'.$xy[0].';';
		echo 'left:'.$xy[1].';';
		$def=0;
	}else{
		echo 'top: 10px;left: 20px;';
		$def=1;
	}
?>">
<?php /*
<div class="fisheye_title"><?php
	echo 'Quicklink';
?></div>
*/?>
<ul id="quicklink_fisheye" class="quicklink fisheye_menu">
	<?php
	ArtaTagsHtml::addLibraryScript('fisheye');
	$items=$this->get('items');
	$t=ArtaLoader::Template();
	$i=0;
	$rand=ArtaString::makeRandStr();
	ArtaTagsHtml::addLibraryScript('livepipe_window');
	foreach($items as $v){ 
		?>
	<li class="icon"><a href="<?php echo ($v->link); ?>">
	<?php 			
			if($v->alt || $v->acckey){
				$ti=' title="'.htmlspecialchars($v->alt.' ['.htmlspecialchars($v->acckey).']').'"';
			}
			
			echo $v->alt || $v->acckey ? 
		'<img src="'.($v->img{0}=='#'? substr($v->img,1) : Imageset($v->img)).'"'.$ti.'/>'
			:'<img src="'.($v->img{0}=='#'? substr($v->img,1) : Imageset($v->img)).'" />';
			
			?>
		<span style="position:absolute;top:33px;"><?php echo htmlspecialchars($v->title); ?></span></a></li>
	<?php
		} ?>
</ul>
</div>
</div>
<?php 

$t->addtoTmpl('<script>
if(Prototype.Browser.IE==false){
	fisheyemenu.init(\'quicklink_fisheye\');
}else{
	items=$$(\'ul.fisheye_menu span\');
	for(i=0;i<items.length;i++){
		$(items[i]).setStyle({display:"none"});
		items[i].parentNode.select("IMG")[0].title=$(items[i]).innerHTML+" - "+items[i].parentNode.select("IMG")[0].title;
	}
}
new Draggable(\'fisheye_container\', {onEnd:function(e){
	elm=$(\'fisheye_container\');
	var exp = new Date();
	var newTime = exp.getTime() + 3600;
	exp.setTime(newTime);
	Cookie.set(\'toolbar_xy\', elm.getStyle("top")+\',\'+elm.getStyle("left"));
}});

if(/*parseInt($(\'fisheye_container\').getStyle(\'top\'))==20*/ '.$def.'==1 && '.count($items).'>-1){
	dim=document.viewport.getDimensions();
	$(\'fisheye_container\').setStyle({top:(dim.height-($(\'fisheye_container\').getHeight()-1))+\'px\', left:parseInt((dim.width/2)-($(\'fisheye_container\').getWidth()/2))+"px"});
}else if('.$def.'==1){
	dim=document.viewport.getDimensions();
	$(\'fisheye_container\').setStyle({left:(parseInt(dim.width/2)-parseInt($(\'fisheye_container\').getWidth()/2))+\'px\'});
}
</script>', 'beforebodyend');
?>
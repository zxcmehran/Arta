<?php if(!defined('ARTA_VALID')){die('No access');} ?>
<a name="hand">&nbsp;</a>
<div id="leftmenu">
<?php 
$x='';
$cookie=@explode(',',$_COOKIE['leftmenu_opened']);
if(isset($_COOKIE['leftmenu_opened'])){
	$x=implode(',',$cookie);
}
ArtaTagsHtml::addHeader('
<script>
theVar=new Array('.$x.');
function menu_handle(ulid, mid){
	if($(ulid).style.display==\'none\'){
		$(ulid).show();
		$(\'image_\'+ulid).src=\''.Imageset('collapse.png').'\';
		if(parseInt(mid)>0){
			theVar[theVar.length]=parseInt(mid);
		}		
	}else{
		$(ulid).hide();
		$(\'image_\'+ulid).src=\''.Imageset('uncollapse.png').'\';
		if(parseInt(mid)>0){
			for(i=0;i<theVar.length;i++){
				if(parseInt(theVar[i])==parseInt(mid)){
					delete theVar[i];
					break;
				}
			}
			
		}
	}
	for(i=0;i<theVar.length;i++){
		if(isNaN(parseInt(theVar[i]))){
			x=new Array();
			if(theVar[i-1]){
			x=theVar.slice(0, i-1);
			}
			if(theVar[i+1]){
			x=x.concat(theVar.slice(i+1));
			}
			theVar=x;
		}
	}
	Cookie.set(\'leftmenu_opened\', theVar.join(\',\'));
}
</script>
');
if(trans('_LANG_DIRECTION')=='ltr'){
	$x='padding-left: 4px;
	margin-left: 4px;';
}else{
	$x='padding-right: 4px;
	margin-right: 4px;';
}
ArtaTagsHtml::addHeader('
<style>
div#leftmenu ul{
	list-style:none;
	margin-bottom:5px;
	'.$x.'
}
div#leftmenu ul li{
	min-height: 16px;
	vertical-align:middle;
}
</style>
');

 $helper=$this->get('helper');
 
 echo $helper->makeContent($cookie);
 
 ?> 
 
 </div>

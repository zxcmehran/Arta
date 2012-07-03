<?php 
if(!defined('ARTA_VALID')){die('No access');}
if(isset($GLOBALS['_ADMINTIPS'])){
$ok=false;
foreach($GLOBALS['_ADMINTIPS'] as $v){
	if(trim($v)!==''){
		$ok=true;
		break;
	}
}
$lay=$this->getSetting('tip_view_mode', 'simple');
if($ok){
	if($lay=='simple'){
?>
<div class="admintip">
	<?php 
	echo '<h4 align="center">'.trans('TITLE_HOW').'</h4>';
	foreach($GLOBALS['_ADMINTIPS'] as $k=>$v){
		echo '<div class="tip">'.$v.'</div>';
	}
	?>
</div>
<?php
	}else{
	    $data='';
	    foreach($GLOBALS['_ADMINTIPS'] as $k=>$v){
    		$data.='<div class="tip">'.$v.'</div>';
    	}
        ArtaTagsHtml::addtoTmpl('
        <style>
        .bulb_tip{
            width:300px;
            position:fixed;
            border: 2px solid #3F8AC3;
            background: #F3F9FF;
            padding:3px;
            -moz-border-radius: 5px;
            border-radius: 5px;
            z-index:10000;
        }
        </style>
        <script>
        dim=document.viewport.getDimensions();
        h=$("idea_bulb").getHeight();
        $("idea_bulb").setStyle({position: "fixed", top: (dim.height-h-35)+"px", left: (15)+"px"});
        t=new Element("DIV", {className:"bulb_tip"});
        t.insert("'.JSValue($data).'");
        document.body.insert(t);
        t.setStyle({top: (dim.height-h-35-t.getHeight())+"px", left: "50px"});
        t.hide();
        $("idea_bulb").observe("mouseover",function(){new Effect.Appear(t,{duration:.25});});
        $("idea_bulb").observe("mouseout",function(){new Effect.Fade(t,{duration:.25});});
        </script>
        ', 'beforebodyend');
        echo '<img id="idea_bulb" src="'.imageset('idea.png').'" alt="TIP"/><noscript>'.($data).'</noscript>';
?>

<?php
	}
}
}
?>

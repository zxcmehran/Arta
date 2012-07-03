<!DOCTYPE html>
<?php $t=ArtaLoader::Template(); ?>
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo trans('_LANG_DIRECTION') ?>" lang="<?php echo trans('_LANG_ID') ?>">
<head>
<artatmpl type="head"/>
<link href="templates/default/style.css" rel="stylesheet" type="text/css" media="screen" />
<?php if($t->getDirection()=='rtl'){
	?><link href="templates/default/rtl.css" rel="stylesheet" type="text/css" media="screen" /><?php
}
?>
<script>
function triggerCollapse(id, im){
	element=$(id);
	var oldStyle = {
	    top: element.style.top,
	    left: element.style.left,
	    width: element.style.width,
	    height: element.style.height };
 	if(element.style.display=='none'){
 		from=0;
 		to=100;
 	}else{
 		from=100;
 		to=0;
 	}
 
  	new Effect.Scale(element, to, { 
		scaleContent: false, 
		scaleY: false,
		scaleFrom: from,
		restoreAfterFinish: true,
		afterFinishInternal: function(effect) {
			if(to==0){
				effect.element.hide();
				effect.element.setStyle(oldStyle);
			}
			
			
			return true;
		},
		beforeSetup: function(effect){
			if(to==100){
				effect.element.show();
			}
		} 
		
	});
	
	if(im.alt=='left'){
		im.src=client_url+'templates/default/images/right.png';
		im.alt='right';
	}else{
		im.src=client_url+'templates/default/images/left.png';
		im.alt='left';
	}
	Cookie.set(id+'_collapsed', (to==100?0:1));
}

</script>
</head>
<body>
<artatmpl type="afterbody"/>
<div id="wrapper">
	<div id="header">
		<div id="logo">
		<img src="<?php echo imageset('arta.png') ?>"/>
		<?php $c=ArtaLoader::Config();  echo trans('CONTROL PANEL').' - '.htmlspecialchars($c->site_name); ?>
		</div>
		<span class="header_text"><artatmpl type="header"/></span>
	</div>
	<!-- end #header -->
	<div id="topmenu">
		<span class="custom1"><artatmpl type="custom1"/></span>
		<span class="adminmenu">
			<artatmpl type="top"/>
		</span>
		
	</div>	
	<!-- end #menu -->
	
	<div id="page">
	<div id="page-bgtop">
	<div id="page-bgbtm">
		<?php if ($t->count('custom4')||$t->count('custom3')){?>
		<table id="top_holder"><tr>
		<td class="custom4"><artatmpl type="custom4"/></td>
		<td class="custom3"><artatmpl type="custom3"/></td>
		</tr></table>
		<?php }?>
		<table id="content_holder">
		<tr>
		<?php if ($t->count('left')){?>
		<td id="sidebar0" class="sidebar">
			<img alt="<?php echo (trans('_LANG_DIRECTION')=='ltr' && @$_COOKIE['side0_wrapper_collapsed']==0)||(trans('_LANG_DIRECTION')=='rtl' && @$_COOKIE['side0_wrapper_collapsed']==1)?'left':'right'; ?>" src="templates/default/images/<?php echo (trans('_LANG_DIRECTION')=='ltr' && @$_COOKIE['side0_wrapper_collapsed']==0)||(trans('_LANG_DIRECTION')=='rtl' && @$_COOKIE['side0_wrapper_collapsed']==1)?'left':'right'; ?>.png" style="position:relative;top:-16px;" align="<?php echo trans('_LANG_DIRECTION')=='ltr'?'right':'left'; ?>" onclick="triggerCollapse('side0_wrapper', this)"/>
			<div id="side0_wrapper" style="overflow: hidden; width:100%;">
				<div id="sidebar0_content">
					<artatmpl type="left"/>
				</div>
			</div>
		</td>
		<?php }?>
		<td id="content">
			<div>
				<artatmpl type="custom2"/>
			</div>
			<div class="contentbase">
				<artatmpl type="message" />
				<br />
				<artatmpl type="package"/>
				<br />
				<div>
					<artatmpl type="bottom"/>
				</div>
				<br />
			</div>
		</td>
		<?php if ($t->count('right')){?>
		<td id="sidebar1" class="sidebar">
			<img alt="<?php echo (trans('_LANG_DIRECTION')=='ltr' && @$_COOKIE['side1_wrapper_collapsed']==0)||(trans('_LANG_DIRECTION')=='rtl' && @$_COOKIE['side1_wrapper_collapsed']==1)?'right':'left'; ?>" src="templates/default/images/<?php echo (trans('_LANG_DIRECTION')=='ltr' && @$_COOKIE['side1_wrapper_collapsed']==0)||(trans('_LANG_DIRECTION')=='rtl' && @$_COOKIE['side1_wrapper_collapsed']==1)?'right':'left'; ?>.png" style="position:relative;top:-16px;" align="<?php echo trans('_LANG_DIRECTION')!='ltr'?'right':'left'; ?>" onclick="triggerCollapse('side1_wrapper', this)"/>
			<div id="side1_wrapper" style="overflow: hidden; width:100%;">
				<div id="sidebar1_content">
					<artatmpl type="right"/>
				</div>
			</div>
		</td>
		<?php }?>
		</tr>
		</table>
		<div>
			<artatmpl type="footer"/>
		</div>
	</div>
	</div>
	</div>
	<!-- end #page -->
</div>
<!-- end #wrapper -->

<div id="footer">
	<artatmpl type="copyright"/>
</div>
<!-- end #footer -->
<artatmpl type="beforebodyend"/>

<script>
<?php if(@$_COOKIE['side0_wrapper_collapsed']==1){
	echo 'try{$(\'side0_wrapper\').hide();}catch(e){};';
}?>

<?php if(@$_COOKIE['side1_wrapper_collapsed']==1){
	echo 'try{$(\'side1_wrapper\').hide();}catch(e){};';
}?>
</script>
</body>
</html>

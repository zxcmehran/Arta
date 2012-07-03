<!DOCTYPE html>
<?php $t=ArtaLoader::Template(); ?>
<html dir="<?php echo trans('_LANG_DIRECTION') ?>" lang="<?php echo trans('_LANG_ID') ?>">
<head>
<link href="templates/default/css/template.css" rel="stylesheet" type="text/css" />
<?php if($t->getDirection()=='rtl'){?>
<link href="templates/default/css/rtl.css" rel="stylesheet" type="text/css" />
<?php } ?>
<?php $color= $t->getSetting('color_variant', 'orange');
echo '<link href="templates/default/css/colors/'.$color.'.css" rel="stylesheet" type="text/css" />' ?>
<?php if(ArtaBrowser::getPlatform() == 'midp'){?>
<link href="templates/default/css/mobile.css" rel="stylesheet" type="text/css"/>
<?php }else{ ?>
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
			}else{
				effect.element.undoClipping();
			}
			
			
			return true;
		},
		beforeSetup: function(effect){
			if(to==100){
				effect.element.show();
			}else{
				effect.element.makeClipping();
			}
		} 
		
	});
	
	if(im.alt=='left'){
		im.src=site_url+'templates/default/images/right.png';
		im.alt='right';
	}else{
		im.src=site_url+'templates/default/images/left.png';
		im.alt='left';
	}
	
	Cookie.set(id+'_collapsed', (to==100?0:1));	
}
</script>
<?php } ?>
</head>
<body>
	<header id="mainHeader">
		<!-- start header -->
		<div id="header">
			<div id="logo">
			<img src="<?php echo imageset('arta_header.png') ?>"/>
			</div>
			<div id="header-location">
				<artatmpl type="header"/>
			</div>
		</div>
		<div id="menu-wrapper">
			<div id="menu">
				&nbsp;<artatmpl type="top"/>
			</div>
			<div id="search">
				<artatmpl type="custom1"/>
			</div>
		</div>
		<div id="pathway">
			<artatmpl type="pathway"/>
		</div>
		<!-- end header -->
	</header>
	
	<!-- start page -->
	<div id="page">
		<table id="main_table"><tr>
		 <?php if($t->count('left')){?>
			<!-- start sidebar left -->
			<td id="sidebar1" class="sidebar">
				<aside>
					<img alt="<?php echo (trans('_LANG_DIRECTION')=='ltr' && @$_COOKIE['side_left_wrapper_collapsed']==0)||(trans('_LANG_DIRECTION')=='rtl' && @$_COOKIE['side_left_wrapper_collapsed']==1)?'left':'right'; ?>" src="templates/default/images/<?php echo (trans('_LANG_DIRECTION')=='ltr' && @$_COOKIE['side_left_wrapper_collapsed']==0)||(trans('_LANG_DIRECTION')=='rtl' && @$_COOKIE['side_left_wrapper_collapsed']==1)?'left':'right'; ?>.png" style="position:relative;top:-16px;" align="<?php echo trans('_LANG_DIRECTION')=='ltr'?'right':'left'; ?>" onclick="triggerCollapse('side_left_wrapper', this)"/>
					<div id="side_left_wrapper" style=" width:100%;">
						<div id="side_left">
							<artatmpl type="left"/>
						</div>
					</div>
				</aside>
			</td>
			<!-- end sidebar left -->
			<?php } ?>
			<!-- start content -->
			<td id="content">
		 		 <?php if($t->count('banner')){?>
				<aside>
					<artatmpl type="banner"/>
				</aside>
				<?php } ?>
			 	<?php if($t->count('message')){?>
				<aside>
					<artatmpl type="message" />
				</aside>
				<?php } ?>
				<artatmpl type="package"/>
				<?php if($t->count('bottom')){?>
				<aside>
					<artatmpl type="bottom"/>
				</aside>
				<?php } ?>
			</td>
			<!-- end content -->
			<?php if($t->count('right')){?>
			<!-- start sidebar right -->
			<td id="sidebar2" class="sidebar">
				<aside>
					<img alt="<?php echo (trans('_LANG_DIRECTION')=='ltr' && @$_COOKIE['side_right_wrapper_collapsed']==0)||(trans('_LANG_DIRECTION')=='rtl' && @$_COOKIE['side_right_wrapper_collapsed']==1)?'right':'left'; ?>" src="templates/default/images/<?php echo (trans('_LANG_DIRECTION')=='ltr' && @$_COOKIE['side_right_wrapper_collapsed']==0)||(trans('_LANG_DIRECTION')=='rtl' && @$_COOKIE['side_right_wrapper_collapsed']==1)?'right':'left'; ?>.png" style="position:relative;top:-16px;" align="<?php echo trans('_LANG_DIRECTION')!='ltr'?'right':'left'; ?>" onclick="triggerCollapse('side_right_wrapper', this)"/>
					<div id="side_right_wrapper" style=" width:100%;">
						<div id="side_right">
							<artatmpl type="right"/>
						</div>
					</div>
				</aside>
			</td>
			<!-- end sidebar right -->
			<?php } ?>
			</tr>
		</table>
		
	</div>
	<!-- end page -->


	<footer id="mainFooter">
		<artatmpl type="copyright"/>
	</footer>


<?php if(ArtaBrowser::getPlatform() != 'midp'){?>
<script>
<?php if(@$_COOKIE['side_left_wrapper_collapsed']==1){
	echo 'try{$(\'side_left_wrapper\').hide();}catch(e){};';
}?>

<?php if(@$_COOKIE['side_right_wrapper_collapsed']==1){
	echo 'try{$(\'side_right_wrapper\').hide();}catch(e){};';
}?>

</script>
<?php } ?>
</body>
</html>

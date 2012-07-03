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

</head>
<body onload="new Effect.Appear($('contentbase'), {duration:1});">
<artatmpl type="afterbody"/>
<div id="wrapper">
	<div id="header">
		<div id="logo">
		
		</div>
	</div>
	<!-- end #header -->
	<div id="topmenu">
	</div>	
	<!-- end #menu -->
	<div id="page">
	<div id="page-bgtop">
	<div id="page-bgbtm">
		<table id="content_holder">
		<tr>
		<td id="content" align="center">
			<div class="contentbase" id="contentbase">
				<artatmpl type="message" />
				<artatmpl type="package"/>
			</div>
		</td>
		</tr>
		</table>
	</div>
	</div>
	</div>
	<!-- end #page -->
</div>

<div id="footer">
	<artatmpl type="copyright"/>
</div>
<!-- end #footer -->
<artatmpl type="beforebodyend"/>

<script>
_h=$('contentbase').getHeight();
$('contentbase').parentNode.setStyle({height: (_h>=300?_h:300)+"px"});
$('contentbase').hide();
</script>
</body>
</html>

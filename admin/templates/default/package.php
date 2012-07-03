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
<style>
body{
	min-width: 100px !important;
}
</style>
</head>
<body style="background:none;">
<artatmpl type="afterbody"/>
	<div id="wrapper">
		<artatmpl type="message" />
		<artatmpl type="package"/>
	</div>
	<div id="footer">
		<artatmpl type="copyright"/>
	</div>
	<!-- end #footer -->
	<artatmpl type="beforebodyend"/>
</body>
</html>

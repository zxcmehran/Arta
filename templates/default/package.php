<!DOCTYPE html>
<?php $t=ArtaLoader::Template(); ?>
<html xmlns="http://www.w3.org/1999/xhtml" dir="<?php echo trans('_LANG_DIRECTION') ?>" lang="<?php echo trans('_LANG_ID') ?>">
<head>
<link href="templates/default/css/template.css" rel="stylesheet" type="text/css" media="screen" />
<?php if($t->getDirection()=='rtl'){?>
<link href="templates/default/css/rtl.css" rel="stylesheet" type="text/css" media="screen" />
<?php } ?>
<?php $color= $t->getSetting('color_variant', 'orange'); 
echo '<link href="templates/default/css/colors/'.$color.'.css" rel="stylesheet" type="text/css" media="screen" />' ?>
<style>
body{
	min-width: 100px !important;
}
</style>
</head>
<body style="padding-left:5px;padding-right:5px; background: #FFFFFF !important;">
<artatmpl type="message" />
<artatmpl type="package"/>
<footer id="mainFooter">
	<artatmpl type="copyright"/>
</footer>
</body>
</html>

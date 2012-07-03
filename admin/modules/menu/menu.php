<?php 
if(!defined('ARTA_VALID')){die('No access');}

ArtaTagsHtml::addLibraryScript('menu');
ArtaTagsHtml::addtoTmpl('<script>var menu = new Menu(\'root\', \'menu\', false);</script>', 'beforebodyend');

$helper=$this->getHelper();
$model=$this->getModel();

$model->getData();

$this->assign('helper',$helper);

$_img=$this->getSetting('show_icons', 1, 'menu');

$helper->img=$_img;
$helper->r=$model->r;

$this->render();
?>
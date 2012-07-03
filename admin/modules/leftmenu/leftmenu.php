<?php 
if(!defined('ARTA_VALID')){die('No access');}

$helper=$this->getHelper();
$model=$this->getModel();

$model->getData();

$this->assign('helper',$helper);

$_img=$this->getSetting('show_icons', 0, 'leftmenu');

$helper->img=$_img;
$helper->r=$model->r;

$this->render();
?>
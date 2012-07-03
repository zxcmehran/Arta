<?php 
if(!defined('ARTA_VALID')){die('No access');}
if(ArtaCache::isUsable('module_quicklink', 'items')){
	$itemz=ArtaCache::getData('module_quicklink', 'items');
}else{
	$model=$this->getModel();
	$itemz=$model->getItems();
	ArtaCache::putData('module_quicklink', 'items', $itemz);
}
$layout=$this->getSetting('layout', 'list');
$this->setLayout($layout);
$this->assign('items', $itemz);
$this->render();
$helper=$this->getHelper();
$helper->makeHotkeysActive($itemz);
?>
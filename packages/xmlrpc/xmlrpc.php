<?php 
if(!defined('ARTA_VALID')){die('No access');}
$GLOBALS['_DISABLE_POSITION_LOGGING']=true;
$this->setDoctype('raw');
$websrv=ArtaLoader::WebService();
$websrv->loadServices();
$websrv->Start();
?>
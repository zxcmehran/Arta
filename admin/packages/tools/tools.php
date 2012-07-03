<?php 
if(!defined('ARTA_VALID')){die('No access');}

ArtaAdminTabs::addTab(trans('DIAGNOSTICS'), 'index.php?pack=tools');
ArtaAdminTabs::addTab(trans('OPTIMIZE DATABASE TABLES'), 'index.php?pack=tools&task=optimizeTables');
ArtaAdminTabs::addTab(trans('REPAIR DATABASE TABLES'), 'index.php?pack=tools&task=repairTables');
ArtaAdminTabs::addTab(trans('CLEAN CACHE'), 'index.php?pack=tools&task=cleanCache');

$controller = new ToolsController;
$x=getVar('task', 'display', '', 'string');
$controller->exec($x);
?>
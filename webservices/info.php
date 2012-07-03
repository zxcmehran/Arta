<?php
if(!defined('ARTA_VALID')){die('No access');}
function getUserCount(){
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT COUNT(*) as count FROM #__users');
	$r=$db->loadObject();
	return new xmlrpcresp(new xmlrpcval($r->count,'int'));
}
$this->mapFunction('info.getUserCount', 'getUserCount', 'int', array());


function getOnlineUserCount(){
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT COUNT(*) as count FROM #__sessions WHERE userid IS NOT NULL AND userid!=0 AND userid!=\'\'');
	$r=$db->loadObject();
	return new xmlrpcresp(new xmlrpcval($r->count,'int'));
}
$this->mapFunction('info.getOnlineUserCount', 'getOnlineUserCount', 'int', array());


function getOnlineGuestCount(){
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT COUNT(*) as count FROM #__sessions WHERE userid IS NULL OR userid=0 OR userid=\'\'');
	$r=$db->loadObject();
	return new xmlrpcresp(new xmlrpcval($r->count,'int'));
}
$this->mapFunction('info.getOnlineGuestCount', 'getOnlineGuestCount', 'int', array());


function getOnlineCount(){
	$db=ArtaLoader::DB();
	$db->setQuery('SELECT COUNT(*) as count FROM #__sessions');
	$r=$db->loadObject();
	return new xmlrpcresp(new xmlrpcval($r->count,'int'));
}
$this->mapFunction('info.getOnlineCount', 'getOnlineCount', 'int', array());

function addupThese($m){
	$a=$m->getParam(0); $a=$a->scalarVal();
	$b=$m->getParam(1); $b=$b->scalarVal();
	return new xmlrpcresp(new xmlrpcval($a+$b,'int'));
}
$this->mapFunction('info.addupThese', 'addupThese', 'int', array('int','int'));
?>
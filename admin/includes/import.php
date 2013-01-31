<?php
/**
 * Client Descriptor file.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */ 
if(!defined('ARTA_VALID')){die('No access');}
global $_CLIENTDATA;

$_CLIENTDATA['SEF_ENABLED']=false;
$_CLIENTDATA['OFFLINE_BYPASS']=true;
$_CLIENTDATA['DEFAULT_PACKAGE']='cphome';

ArtaLoader::Import('includes->buttons', 'client');
ArtaLoader::Import('includes->tabs', 'client');
ArtaLoader::Import('includes->tips', 'client');
$language=ArtaLoader::Language();
$language->addtoNeed('buttons', 'module');

$plg=ArtaLoader::Plugin();
$plg->register('onAfterShowModules', 'includeButtonsIfNotIncluded');
function includeButtonsIfNotIncluded(){
	$t=ArtaLoader::Template();
	if($t->getType()=='html'){
		$files=get_included_files();
		if(!in_array(ARTAPATH_CLIENTDIR.DS.'modules'.DS.'buttons'.DS.'buttons.php', $files)){
			$m=ArtaLoader::Module();
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__modules WHERE module=\'buttons\' AND client=\'admin\' AND enabled=1');
			$data=$db->loadObject();
			if($data){
				
				$t->addtoTmpl($m->renderBlocks($data), 'package');
			}		
		}
	}
}

?>
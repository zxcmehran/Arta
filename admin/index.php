<?php
/**
 * Arta Admin Index file.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
/**
 * Index page of Arta. We will try to create application.
 */
//we need PHP 5

/**
 * Client name
 */
define('CLIENT', 'admin');

/**
 * Client Subdirectory name
 */
define('ARTAPATH_CLIENTDIRNAME', 'admin');

/**
 * Path to client root directory
 */
define('ARTAPATH_CLIENTDIR', dirname( __FILE__ ));

$updateFile = ARTAPATH_CLIENTDIR.'/tmp/urgentupdatelock.tmp';
if(is_file($updateFile) AND @filemtime($updateFile)<time()-300){
	@unlink($updateFile);
}
while(is_file($updateFile)){
	if($_SERVER["REQUEST_METHOD"]=="GET"){
		echo 'An urgent updating process is going on. Please be patient...<br/>';
		echo 'You can reload page about a minute after.';
		die();
	}
	// wait while update ends.
	clearstatcache();
	sleep(1);
}

// IMPORT ESSENTIALS
require(ARTAPATH_CLIENTDIR.'/../library/import.php');
// LOAD THEM

_loadEssentials();
	
// APPLICATION IS NOW AVAILABLE... CONTINUE.

ArtaLoader::Application(); // FIRE!

?>
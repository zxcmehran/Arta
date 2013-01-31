<?php
/**
 * An alternative Index page for Arta which only executes XMLRPC package. 
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
//we need PHP 5
/**
 * Client name
 */
define('CLIENT', 'site');

/**
 * Client Subdirectory name
 */
define('ARTAPATH_CLIENTDIRNAME', '');

/**
 * Path to client root directory
 */
define('ARTAPATH_CLIENTDIR', dirname( __FILE__ ));

define('XMLRPC_FILE',true);

// IMPORT ESSENTIALS
require(ARTAPATH_CLIENTDIR.'/library/import.php');
// LOAD THEM

_loadEssentials();
	
// APPLICATION IS NOW AVAILABLE... CONTINUE.
//$config=ArtaLoader::Config();

ArtaRequest::addVar('pack', 'xmlrpc');


ArtaLoader::Application(); // FIRE!

?>
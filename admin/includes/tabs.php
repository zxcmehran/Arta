<?php
/**
 * Arta Admin Tabs container.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */  
if(!defined('ARTA_VALID')){die('No access');}

/**
 * Helps you to add entries to admintabs module.
 * @static
 */
class ArtaAdminTabs{
	static function addTab($name, $link){
		if(!isset($GLOBALS['_ADMINTABS'])){
			$GLOBALS['_ADMINTABS']=array();
		}
		$GLOBALS['_ADMINTABS'][$link]=$name;
	}
}
?>
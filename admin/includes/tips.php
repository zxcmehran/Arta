<?php 
/**
 * Arta Admin Tips container.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */  
if(!defined('ARTA_VALID')){die('No access');}

/**
 * Helps you to add entries to admintips module.
 * @static
 */
class ArtaAdminTips{
	static function addTip($tip){
		if(!isset($GLOBALS['_ADMINTIPS'])){
			$GLOBALS['_ADMINTIPS']=array();
		}
		$GLOBALS['_ADMINTIPS'][]=$tip;
	}
}
?>
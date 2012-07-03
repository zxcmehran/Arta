<?php
/**
 * ArtaLinks. Class for Arta Links utilization.
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
 * ArtaLinks Class
 * Links loader and utilizer.
 * @static
 */
class ArtaLinks{
	
	/**
	 * Default link.
	 * @staticvar
	 * @access	private
	 */
	private static $default=null;
	
	/**
	 * Returns all link items
	 * @static
	 * @return	array
	 */
	static function getItems(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__links ORDER BY `order`');
		return $db->loadObjectList();
	}
	
	/**
	 * Returns Default link item (homepage).
	 * @static
	 * @return	object
	 */
	static function getDefault(){
		if(self::$default===null){
			if(ArtaCache::isUsable('links','default')){
				self::$default=ArtaCache::getData('links','default');
			}else{
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__links WHERE type='.$db->Quote('default'));
				self::$default=$db->loadObject();
				ArtaCache::putData('links','default',self::$default);
			}
		}
		return self::$default;
	}
	
	/**
	 * Sets Default request vars if package equals default link's package.
	 * @static
	 * @return	bool
	 */
	static function setDefaultVars(){
		$q = ArtaURL::breakupQuery($_SERVER['QUERY_STRING']);
		$vars=self::getDefaultVars();
		if(isset($vars['pack'])){
			foreach($vars as $k=>$v){
				if(getVar($k, false)==false){
					ArtaRequest::addVar($k,$v);
				}
			}
			return true;
		}
		return false;
	}
	
	/**
	 * Returns an array cotaining variables of default link's query string.
	 * @static 
	 * @return	array
	 */
	static function getDefaultVars(){
		$def=self::getDefault();
		$def=substr($def->link, 10);
		$vars=ArtaURL::breakupQuery($def);
		return $vars;
	} 
	
}

?>
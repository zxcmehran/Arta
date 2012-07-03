<?php 
/**
 * Arta Simple XML Handler
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// detect arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaSimpleXML Class
 * SimpleXML parser
 */
class ArtaSimpleXML{
	
	/**
	 * Parses string
	 * @param	string	$str	String to parse
	 * @retun	mixed
	 */
	static function parseString($str){
		if(!function_exists('simplexml_load_string')){
			die('SimpleXML is not supported.');
		}
		$xml=@simplexml_load_string($str);
		return $xml;
	}
	
	/**
	 * Parses File contents
	 * @param	string	$file	File to parse contents 
	 * @retun	mixed
	 */
	static function parseFile($file){
		if(!function_exists('simplexml_load_string')){
			die('SimpleXML is not supported.');
		}
		$xml=@simplexml_load_file($file);
		return $xml;
	}
}

?>
<?php
/**
 * ArtaYQL. Support for YQL service.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if (!defined('ARTA_VALID')) {
	die('No access');
}

/**
 * ArtaYQL
 * This class currenly only supports public YQL terminal.
 * 
 * @link	http://developer.yahoo.com/	For more info about YQL.
 * @static
 */
class ArtaYQL
{
	
	/**
	 * YQL server to contact to.
	 */
	const HOST='http://query.yahooapis.com/v1/public/yql';
	
	/**
	 * The only user-side function of the class.
	 * It sends YQL to server then returns decoded response to you.
	 * You just need this function to use ArtaYQL.
	 * In other words, this one takes care of everything.
	 * 
	 * @static
	 * @param	$yql	string	YQL statement
	 * @param	$env	string	YQL environment
	 * @return	mixed	false on failure or object on success.
	 */
	static function getResult($yql, $env=false){
		if($yql==false){
			return false;
		}
		if($env==false){
			$env='http://datatables.org/alltables.env';
		}
		$format=self::getPreferredFormat();
		
		$data=self::getRemoteData($yql, $format, $env);		
		
		if($data==false){
			return false;
		}
		
		return $data;
		
	}
	
	/**
	 * Returns preferred format according to server PHP installation.
	 * @return	string
	 */
	static function getPreferredFormat(){
		if(function_exists('json_decode')){
			return 'json';
		}else{
			return 'xml';
		}
	}
	
	/**
	 * Fetches remote data and decodes it.
	 * @param	$yql	string	YQL statement
	 * @param	$format	string	json or xml
	 * @param	$env	string	YQL environment
	 * @return	mixed
	 */
	static function getRemoteData($yql, $format, $env){
		$URL= self::createURL($yql, $format, $env);
		
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $URL);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);	
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch, CURLOPT_USERAGENT,ARTA_USERAGENT);
		curl_setopt($ch, CURLOPT_CONNECTTIMEOUT,10);
		curl_setopt($ch, CURLOPT_TIMEOUT, 30);
		// grab URL
		$c = @curl_exec($ch);
		
		if($c==false){
			return false;
		}
		
		return self::decodeResult($c, $format);
		
	}
	
	/**
	 * Creates URL of YQL request.
	 * @static
	 * @param	$yql	string	YQL statement
	 * @param	$format	string	json or xml
	 * @param	$env	string	YQL environment
	 * @return	string
	 */
	static function createURL($yql, $format, $env){
		$r=self::HOST;
		$r.='?q='.urlencode($yql);
		if($format=='json'){
			$r.='&format=json&callback=';
		}
		$r.='&env='.urlencode($env);
		
		return $r;
	}
	
	/**
	 * Passes response body to suitable function according to format.
	 * @static
	 * @param	$c	string	Response body
	 * @param	$format	string	json or xml
	 * @return	mixed
	 */
	static function decodeResult($c, $format){
		$func='_parse_'.$format;
		return self::$func($c);
	}
	
	/**
	 * Parses a json response body.
	 *
	 * @static
	 * @param	$response_body	string	The Response body
	 * @return	mixed
	 */
	static function _parse_json( $response_body ) {
		return ( ( $data = json_decode( trim( $response_body ) ) ) && is_object( $data ) ) ? $data : false;
	}

	/**
	 * Parses an XML response body.
	 *
	 * @static
	 * @param	$response_body	string	The Response body
	 * @return	mixed
	 */
	static function _parse_xml( $response_body ) {
		if ( function_exists('simplexml_load_string') ) {
			$data = simplexml_load_string( $response_body );
			if ( is_object( $data ) )
				return $data;
		}
		return false;
	}
	
	
	
}

?>
<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/2/20 12:23 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

	/**
	 * Supports href="some_url", src="some_url", url('some_url'), <param name="movie,src,url" value="some_url">, action="some_url"
	 * in other places you must manually use makeURL() or ArtaURL::make()
	 */
	function plgURLProcess(&$t){
		$buffer = &$t->content;
		
      	$p=substr($_SERVER['SCRIPT_NAME'], 0, strlen($_SERVER['SCRIPT_NAME'])-strlen('index.php'));
		$base   = $p;
       	$regex  = '#(href|src|action)="([^"]*)#mi';
      	$buffer = preg_replace_callback( $regex, 'plgURLMake', $buffer );

       	$protocols = '[a-zA-Z0-9]+:';
		$regex     = '#(onclick="window.open\(\')(?!/|'.$protocols.'|\#)([^/]+[^\']*?\')#mi';
		$buffer    = preg_replace($regex, '$1'.$base.'$2', $buffer);
		
		// Background image url()
		$regex 		= '#\Wurl\([\'\"]?(?!/|'.$protocols.'|\#)([^\)\'\"]+)[\'\"]?\)#mi';
		$buffer 	= preg_replace($regex, 'url(\''. $base .'$1$2\')', $buffer);
		
		// OBJECT <param name="xx", value="yy">
		$regex 		= '#<param name="(movie|src|url)" value="(?!/|'.$protocols.'|\#|\')([^"]*)"#mi';
		$buffer 	= preg_replace($regex, '<param name="$1" value="'. $base .'$2"', $buffer);

		// OBJECT <param value="xx", name="yy">
		$regex 		= '#<param value="(?!/|'.$protocols.'|\#|\')([^"]*)" name="(movie|src|url)"#mi';
		$buffer 	= preg_replace($regex, '<param value="'. $base .'$1" name="$2"', $buffer);
		
		// OBJECT <object data="xx" ... >
		$regex 		= '#<object(.*)data="(?!/|'.$protocols.'|\#|\')([^"]*)"#mi';
		$buffer 	= preg_replace($regex, '<object$1data="'. $base .'$2"', $buffer);

		return true;
	}

	/**
     * Replaces the matched tags
     *
     * @param array An array of matches (see preg_match_all)
     * @return string
     */
   	 function plgURLMake( &$matches ){
		$original       = $matches[0];
		$type			= $matches[1];
       	$url            = str_replace('&amp;','&',$matches[2]);
       	
		if(strlen($url) && ($url{0}!='#' || substr($url,0, 10)=='#index.php')){
			$url = ArtaURL::make($url);
       	}

       	if($type=='url'){
       		return 'url('.$url.')';
       	}
       	
      	return $type.'="'.$url;
      }
?>

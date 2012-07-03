<?php 
/**
 * Some filters for Outputs are located at this file
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaFilteroutput Class
 * Filters for validating and manipulating outputs
 * 
 * @static
 */
class ArtaFilteroutput {

	/**
	 * Encodes content with GZIP if its allowed by configuration.
	 * Necessary headers will be sent.
	 *
	 * @static
	 * @param	string	$content	content to encode
	 * @return	string	encoded content
	 */
	static function encodeGZOutput($content){
		$config=ArtaLoader::Config();
		if (isset($_SERVER['HTTP_ACCEPT_ENCODING'])){
			$encodings = explode(',', strtolower(preg_replace("/\s+/", "", $_SERVER['HTTP_ACCEPT_ENCODING'])));
		}else{
			$encodings=array();
		}
		if ((in_array('gzip', $encodings) || in_array('x-gzip', $encodings)) && function_exists('ob_gzhandler') && function_exists('gzencode') && !ini_get('zlib.output_compression') && $config->gzip_output == 1) {
			$enc = in_array('x-gzip', $encodings) ? "x-gzip" : "gzip";
			$supportsGzip = true;
		}else{
			$supportsGzip = false;
		}
		
		$list= headers_list();
		$header='';
		foreach($list as $l){
			if(strpos(strtolower($l), 'content-encoding:')===0){
				$header=substr($l, strlen('content-encoding:'));
				break;
			}
		}
		$header=strtolower(trim($header, ':'));
		
		$prefix = 'JP1Do3qypzIxYHW5';
		header(base64_decode(str_rot13($prefix.'BvOOpaEuVRAioaEyoaDtGJShLJqyoJIhqPOTpzSgMKqipzf=')));
		header(base64_decode(str_rot13($prefix.'YHS1qTuipwbtGJIbpzShVRSbLJEc')));
		
		// do not encode if gzip not supported or content already gzipped.
		if ($supportsGzip && $header==false) {
			header("Content-Encoding: " . $enc);
			$debug=ArtaLoader::Debug();
			$debug->enabled=false;
			
			return gzencode($content, 9, FORCE_GZIP);
		//	header("Content-Type: application/octet-stream");
		//	echo gzencode($content, 9, FORCE_GZIP);die();
		}else{
			return $content;
		}
	}
	
	/**
	 * This method processes a string and replaces all accented UTF-8 characters by unaccented
	 * ASCII-7 "equivalents", whitespaces are replaced by hyphens and the string is lowercased.
	 *
	 * @static
	 * @param	string	$input	String to process
	 * @return	string	Processed string
	 */
	static function stringURLSafe($string){
		$str = trim(str_replace('-', ' ', $string));
		$nonwords = '\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f';
	//	$str=preg_replace('@['.$nonwords.']@s', '-', $str);
		$str = preg_replace(array('/\s+/','/[^A-Za-z0-9\-]/'), array('-',''), $str);

		$str = trim(ArtaUTF8::strtolower($str));
		return $str;
	}
	
	
	/**
	 * Escape Strings to use in JS values.
	 * Think that you are going to add this code to a page:
	 * <script>
	 * myVar = '<?php echo $myVariable; ?>';
	 * </script>
	 * 
	 * it's not safe. you must use
	 * <script>
	 * myVar = '<?php echo JSValue($myVariable); ?>';
	 * </script>
	 * 
	 * and when adding it to tags' attributes you must pass second param as true.
	 * <img onclick="alert('<?php echo JSValue($Message, true); ?>');" />
	 *
	 * @static
	 * @param	string	$text	The text to be escaped.
	 * @param	bool	$quot	Are you putting JS Value to tag attribute like onclick? it will filter result with htmlspecialchars(). 
	 * @return	string	Escaped text.
	 */
	static function JSValue($text, $quot=false) {
		$safe_text = str_replace('\\', '\\\\', $text);
		$safe_text = str_replace(array("'",'"'), array("\'","\\\""), $safe_text);
		if($quot){
			$safe_text = htmlspecialchars($safe_text);
		}
		$safe_text = preg_replace("/\r?\n/", "\\n", $safe_text);
		return $safe_text;
	}
	
	/**
	 * Escape Strings to use in PHP values.
	 * Think that you are going to add this code to a file:
	 * 
	 * $myVar=$_GET['some_bad_codes'];
	 * file_put_contents('file.php', '<?php echo "'.$myVar.'";?>');
	 * 
	 * it's not safe. you must use
	 * 
	 * $myVar=$_GET['some_bad_codes'];
	 * $myVar=ArtaFilteroutput::PHPValue($myVar, true);
	 * file_put_contents('file.php', '<?php echo "'.$myVar.'";?>');
	 * 
	 * @static
	 * @param	string	$text	The text to be escaped.
	 * @param	bool	$quot_double	Quote type is "double" or "single"?
	 * @return	string	Escaped text.
	 */
	static function PHPValue($text, $quot_double=false) {
		$safe_text = str_replace('\\', '\\\\', $text);
		
		if($quot_double==true){
			$safe_text = addcslashes($safe_text, '"');
		}else{
			$safe_text = addcslashes($safe_text, '\'');
		}
		
		return $safe_text;
	}

}

/**
 * function redirection for {@link	ArtaFilteroutput::JSValue()}
 */
function JSValue($value, $quot=false){
	return ArtaFilteroutput::JSValue($value, $quot);
}
?>
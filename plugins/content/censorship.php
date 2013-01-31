<?php 
/**
 * Censorship plug-in for Arta. It censores bad words in contents.
 * 
 * @author	Mehran Ahadi
 * @package	Arta
 * @version	$Revision: 3 2010/07/21 16:42:40 +3.5 GMT $
 * @link	http://artaproject.com	Author's homepage
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * plgCensorship Class
 * Censores bad words
 * 
 * @static
 */
 
class plgCensorship{
	
	/**
	 * Contains bad word to be censored.
	 * @staticvar
	 */
	static $badwords=null;
	
	/**
	 * Gets badwords and fills plgCensorship::$badwords
	 * @static
	 */
	static function getData(){
		$p = ArtaLoader::Plugin();
		$bads = $p->getSetting('bad_words');
		$bads = (array)explode(",", $bads);
		foreach($bads as $k=>$v){
			if(trim($v)==''){
				unset($bads[$k]);
			}
		}
		self::$badwords =(array)$bads;		
	}
	
	/**
	 * Censors badwords from a text.
	 * @static
	 * @param	string	$text
	 * @param	string	$context	Context name
	 * @param	bool	$isSensitiveContext	if true, we should start working. it will be true on comments, forum posts, etc.
	 * @return	string	the censored text
	 */
	static function censor(&$text, $context, $isSensitiveContext=false){
		if($isSensitiveContext!=true){
			return true;
		}
		if(self::$badwords===null){
			self::getData();
		}
		
		// ASCII characters in 0-47, 58-64, 91-96, 123-127 distances
		$nonwords = '\x00-\x2f\x3a-\x40\x5b-\x60\x7b-\x7f';
		
		foreach(self::$badwords as $bad){
			$brace=false;
			if($bad[0]=='{' && $bad[strlen($bad)-1]=='}'){
				$bad=ArtaUTF8::substr($bad, 1, ArtaUTF8::strlen($bad)-2);
				$brace=true;
			}
			
			$replacement=str_repeat('*', ArtaUTF8::strlen($bad));
			
			if($brace==true){
				$text = preg_replace('#(\>|^)([^\<]*)(?<=['.$nonwords.']|^)'.preg_quote($bad).'(?=['.$nonwords.']|$)([^\>]*)(\<|$)#si', '$1$2'.$replacement.'$4$5', $text);
			}else{
				//$text=str_ireplace($bad, $replacement, $text);
				$text = preg_replace('#(\>|^)([^\<]*)'.preg_quote($bad).'([^\>]*)(\<|$)#si', '$1$2'.$replacement.'$3$4', $text);// to deny replacing between < and >. 
				// for example if you apply cat then:
				// <tag attrib="cat" cat="dd"> cat </tag>
				// will be <tag attrib="cat" cat="dd"> *** </tag>
				// However it may corrupt your <style>s and <script>s.
			}
		}
		
		//return $text;
	}
	
}
?>
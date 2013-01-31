<?php 
/**
 * Some filters for inputs are located at this file
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 2 2013/01/31 02:25 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaFilterinput Class
 * Filters for validating and manipulating inputs
 * 
 * Some functions are inspired from JFilterInput class from Joomla.Platform which
 * is forked from the php input filter library by: Daniel Morris <dan@rootcube.com>
 * Original Contributors: Gianpaolo Racca, Ghislain Picard, Marco Wandschneider, Chris Tobin and Andrew Eddie.
 * 
 * @static
 */
class ArtaFilterinput{

	/**
	 * @var    array  An array of permitted tags.
	 * @access	private
	 * @static
	 */
	private static $tagsArray = array();

	/**
	 * @var    array  An array of permitted tag attributes.
	 * @access	private
	 * @static
	 */
	private static $attrArray = array();

	/**
	 * @var    integer  Method for tags: WhiteList method = 0, BlackList method = 1 (default)
	 * @access	private
	 * @static
	 */
	private static $tagsMethod = 1;

	/**
	 * @var    integer  Method for attributes: WhiteList method = 0, BlackList method = 1 (default)
	 * @access	private
	 * @static
	 */
	private static $attrMethod = 1;

	/**
	 * @var    integer  Only auto clean essentials = 0, Allow clean blacklisted tags/attr = 1 (default)
	 * @access	private
	 * @static
	 */
	private static $xssAuto = 1;

	/**
	 * @var    bool	Indicates that strict blacklists should be used to clear XSS or non-strict blacklists.
	 * @access	private
	 * @static
	 */
	private static $strictMode = false;
	
	/**
	 * @var    array  A list of the default blacklisted tags for strict mode.
	 * @access	private
	 * @static
	 */
	private static $tagBlacklist1 = array(
		'body',
		'basefont',
		'head',
		'html',
		'id',
		'name',
		'applet',
		'meta',
		'xml',
		'link',
		'style',
		'script',
		'frame',
		'frameset',
		'ilayer',
		'layer',
		'bgsound',
		'title',
		'base',
		'object',
		'comment',
		// additional
		'embed',
		'iframe'
	);
	
	/**
	 * @var    array  A list of the default blacklisted tags for non-strict mode.
	 * @access	private
	 * @static
	 */
	private static $tagBlacklist2 = array(
		'body',
		'basefont',
		'head',
		'html',
		'id',
		'name',
		'applet',
		'meta',
		'xml',
		'link',
		'style',
		'script',
		'frame',
		'frameset',
		'ilayer',
		'layer',
		'bgsound',
		'title',
		'base',
		'object',
		'comment'
	);

	/**
	 * @var    array     A list of the default blacklisted tag attributes for strict mode.  All event handlers implicit.
	 * @access	private
	 * @static
	 */
	private static $attrBlacklist1 = array(
		'background',
		'codebase',
		'dynsrc',
		'lowsrc',
		'form',
		'formaction',
		'autofocus',
		//additional
		'action'
	);

	/**
	 * @var    array     A list of the default blacklisted tag attributes for non-strict mode.  All event handlers implicit.
	 * @access	private
	 * @static
	 */
	private static $attrBlacklist2 = array(
		'background',
		'codebase',
		'dynsrc',
		'lowsrc',
		'form',
		'formaction',
		'autofocus'
	);


	
	/**
	 * Removes malicious characters from disk addresses like  .. and :// (for blocking remote file inclusions or etc.) and etc.
	 * note that this must be applied to file or dir names. because it strips directory separators.
	 *
	 * @static
	 * @param	mixed	$str	string to escape.you can use arrays to define many.
	 * @return	mixed	escaped string.type is like $str
	 */
	static function safeAddress($str){
		if(!is_array($str)){$str=array($str);}
		foreach($str as $k=>$v){
			$v= str_replace('..', '', $v);
			$v= str_replace('://', '', $v);
			$v= str_replace('/', '', $v);
			$v= str_replace('\\', '', $v);
			$v= str_replace(':', '', $v);
			$v= str_replace("\0", '', $v);
			$str[$k]= ArtaFile::safeName($v);
		}
		if(count($str) == 1 && isset($str[0])){
			$str=$str[0];
		}
		return $str;
	}

	/**
	 * Cleans vars by type of them.
	 * Cleaning types: safe-html,very-safe-html,int,integer,filename,double,float,array,bool,boolean,string,no-html,datetime,date,funcname,email
	 * 
	 * @static
	 * @param	mixed	$var	Variable to clean.
	 * @param	string	$type	Cleaning type.
	 * @return	mixed
	 */
	static function clean($var, $type='default'){
		if((is_array($var)||is_object($var)) && is_array($type)){
			foreach($var as $k=>$v){
				if(isset($type[$k])){
					if(is_array($var)){
						$var[$k]=self::clean($v, $type[$k]);
					}else{
						$var->$k=self::clean($v, $type[$k]);
					}
				}
			}
			$result=$var;
		}else{
			$type=strtolower($type);
			
			switch($type){
				case 'int':
				case 'integer':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					preg_match('/-?[0-9]+/', (string)$result, $matches);
					$result = @(int)$matches[0];
				break;
				case 'filename':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					$result=@self::safeAddress((string)$result);
				break;
				case 'double':
				case 'float':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					preg_match('/-?[0-9]+(\.[0-9]+)?/', (string)$result, $matches);
					$result = @(float)$matches[0];
				break;
				case 'array':
					if(!is_array($var)){
						$result=array($var);	
					}else{
						$result=$var;
					}
				break;
				case 'bool':
				case 'boolean':
					$result=@(bool)$var;
				break;
				case 'string':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
				break;
				case 'no-html':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					$result=htmlspecialchars((string)$result);
				break;
				case 'safe-html':
					//$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'link', 'style', 'script', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					//$result=self::rstrip_tags((string)$result, '<'.implode('><', $ra1).'>');
					self::$strictMode = 0;
					$result = self::_remove($result);
				break;
				case 'very-safe-html':
					//$ra1 = Array('javascript', 'vbscript', 'expression', 'applet', 'meta', 'xml', 'blink', 'link', 'style', 'script', 'embed', 'object', 'iframe', 'frame', 'frameset', 'ilayer', 'layer', 'bgsound', 'title', 'base');
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					//$result=self::rstrip_tags((string)$result, '<'.implode('><', $ra1).'>');
					self::$strictMode = 1;
					$result = self::_remove($result);
				break;
				case 'datetime':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					$result=ArtaDate::convertInput($result);
				break;
				case 'date':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					$result=ArtaDate::convertInput($result, true, true);
				break;
				case 'funcname':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					preg_match('@^[a-zA-Z_\x7f-\xff][a-zA-Z0-9_\x7f-\xff]*$@', (string)$result, $matches);
					$result = isset($matches[0])?@(string)$matches[0]:false;
				break;
				case 'email':
					$result=@(string)$var;
					if(is_array($var)){
						$result='';
					}
					if(@self::isEmail((string)$result)){
						$result=@(string)$result;
					}else{
						$result=false;
					}
				break;
				default:
					$result=$var;
				break;
			}
		}
		return $result;
	}
	
	/**
	 * Acts as strip_tags() but gets INValid tags as second parameter.
	 * NOTE: Comments will not be removed.
	 * 
	 * @static
	 * @param	string	$text	Text to strip tags in it
	 * @param	string	$tags	INValid tags to be stripped.
	 * @return	string
	 */
	static function rstrip_tags($text, $tags = '') {
		preg_match_all('/<(.+?)[\s]*\/?[\s]*>/si', trim($tags), $tags);
		$tags = @array_unique($tags[1]);
		if(is_array($tags) AND count($tags) > 0) {
			$r= preg_replace('@<('. implode('|', $tags) .')\b.*?>.*?</\1>@si', '', $text);
			return preg_replace('@<('. implode('|', $tags) .')\b.*?/?>@si', '', $r);
		}
		return $text;
	}
	
	/**
	 * Limits array values lengths.
	 * 
	 * @static
	 * @param	array	$a	Array to cut values
	 * @param	array	$lengths	length of every key of $a
	 * @return	array
	 */
	static function array_limit($a, $lengths){
		foreach($a as $k=>$v){
			if(isset($lengths[$k])){
				$a[$k]=@substr((string)$v, 0, (int)$lengths[$k]);
			}
		}
		return $a;
	}
	
	/**
	 * Trims array contents
	 * 
	 * @static 
	 * @param	array	$a	Array to process
	 * @param	array	$charlist
	 * @return	array
	 */
	static function trim($a, $charlist=null){
		foreach($a as $k=>$v){
			if(!is_array($v) && !is_object($v)){
				$v=@(string)$v;
				if(is_array($a)){
					$a[$k]=$charlist==null ? trim($v) : trim($v,$charlist);
				}else{
					$a->$k=$charlist==null ? trim($v) : trim($v,$charlist);
				}
			}else{
				if(is_array($a)){
					$a[$k]=ArtaFilterinput::trim($v, $charlist);
				}else{
					$a->$k=ArtaFilterinput::trim($v, $charlist);
				}
			}
		}
		return $a;
	}
	
	/**
	 * Gets Uploaded data error. Returns false on no error else returns Error Message
	 * 
	 * @static
	 * @param	int	$code	Error code
	 * @return	mixed
	 */
	static function uploadErr($code){
	 	if($code==UPLOAD_ERR_OK){
	 		return false;
	 	}elseif($code==UPLOAD_ERR_INI_SIZE){
			return trans('UPL_MORE THAN SIZE DEFINED IN INI');
		}elseif($code==UPLOAD_ERR_FORM_SIZE){
			return trans('UPL_MORE THAN SIZE DEFINED IN FORM');
		}elseif($code==UPLOAD_ERR_PARTIAL){
			return trans('UPL_UPLOADED PARTIAL');
		}elseif($code==UPLOAD_ERR_NO_FILE){
			return trans('UPL_NO FILE UPLOADED');
		}else{
			return trans('UPL_UNKNOWN UPLOAD ERR');	
		}
	 }
	 
	 /**
	 * Checks to see if the text is a valid email address.
	 *
	 * @static
	 * @param	string	$user_email	The email address to be checked.
	 * @return	bool	Returns true if valid, otherwise false.
	 */
	static function isEmail($user_email) {
		$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
		if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
			if (preg_match($chars, $user_email)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	/**
	 * Internal method to iteratively remove all unwanted tags and attributes
	 *
	 * @param   string  $source  Input string to be 'cleaned'
	 * @return  string  'Cleaned' version of input parameter
	 * @access	private
	 * @static
	 */
	private static function _remove($source)
	{
		// Iteration provides nested tag protection
		while ($source != ($x = self::_cleanTags($source)))
		{
			$source = $x;
		}

		return $source;
	}
	
	/**
	 * Internal method to strip a string of certain tags
	 *
	 * @param   string  $source  Input string to be 'cleaned'
	 * @return  string  'Cleaned' version of input parameter
	 * @access	private
	 * @static
	 */
	private static function _cleanTags($source)
	{
		// In the beginning we don't really have a tag, so everything is postTag
		$preTag = null;
		$postTag = $source;
		$currentSpace = false;

		// Setting to null to deal with undefined variables
		$attr = '';

		// Is there a tag? If so it will certainly start with a '<'.
		$tagOpen_start = strpos($source, '<');

		while ($tagOpen_start !== false)
		{
			// Get some information about the tag we are processing
			$preTag .= substr($postTag, 0, $tagOpen_start);
			$postTag = substr($postTag, $tagOpen_start);
			$fromTagOpen = substr($postTag, 1);
			$tagOpen_end = strpos($fromTagOpen, '>');

			// Check for mal-formed tag where we have a second '<' before the first '>'
			$nextOpenTag = (strlen($postTag) > $tagOpen_start) ? strpos($postTag, '<', $tagOpen_start + 1) : false;
			if (($nextOpenTag !== false) && ($nextOpenTag < $tagOpen_end))
			{
				// At this point we have a mal-formed tag -- remove the offending open
				$postTag = substr($postTag, 0, $tagOpen_start) . substr($postTag, $tagOpen_start + 1);
				$tagOpen_start = strpos($postTag, '<');
				continue;
			}

			// Let's catch any non-terminated tags and skip over them
			if ($tagOpen_end === false)
			{
				$postTag = substr($postTag, $tagOpen_start + 1);
				$tagOpen_start = strpos($postTag, '<');
				continue;
			}

			// Do we have a nested tag?
			$tagOpen_nested = strpos($fromTagOpen, '<');
			if (($tagOpen_nested !== false) && ($tagOpen_nested < $tagOpen_end))
			{
				$preTag .= substr($postTag, 0, ($tagOpen_nested + 1));
				$postTag = substr($postTag, ($tagOpen_nested + 1));
				$tagOpen_start = strpos($postTag, '<');
				continue;
			}

			// Let's get some information about our tag and setup attribute pairs
			$tagOpen_nested = (strpos($fromTagOpen, '<') + $tagOpen_start + 1);
			$currentTag = substr($fromTagOpen, 0, $tagOpen_end);
			$tagLength = strlen($currentTag);
			$tagLeft = $currentTag;
			$attrSet = array();
			$currentSpace = strpos($tagLeft, ' ');

			// Are we an open tag or a close tag?
			if (substr($currentTag, 0, 1) == '/')
			{
				// Close Tag
				$isCloseTag = true;
				list ($tagName) = explode(' ', $currentTag);
				$tagName = substr($tagName, 1);
			}
			else
			{
				// Open Tag
				$isCloseTag = false;
				list ($tagName) = explode(' ', $currentTag);
			}

			/*
			 * Exclude all "non-regular" tagnames
			 * OR no tagname
			 * OR remove if xssauto is on and tag is blacklisted
			 */
			if ((!preg_match("/^[a-z][a-z0-9]*$/i", $tagName)) || (!$tagName) || ((in_array(strtolower($tagName), (self::$strictMode?self::$tagBlacklist1:self::$tagBlacklist2))) && (self::$xssAuto)))
			{
				$postTag = substr($postTag, ($tagLength + 2));
				$tagOpen_start = strpos($postTag, '<');

				// Strip tag
				continue;
			}

			/*
			 * Time to grab any attributes from the tag... need this section in
			 * case attributes have spaces in the values.
			 */
			while ($currentSpace !== false)
			{
				$attr = '';
				$fromSpace = substr($tagLeft, ($currentSpace + 1));
				$nextEqual = strpos($fromSpace, '=');
				$nextSpace = strpos($fromSpace, ' ');
				$openQuotes = strpos($fromSpace, '"');
				$closeQuotes = strpos(substr($fromSpace, ($openQuotes + 1)), '"') + $openQuotes + 1;

				$startAtt = '';
				$startAttPosition = 0;

				// Find position of equal and open quotes ignoring
				if (preg_match('#\s*=\s*\"#', $fromSpace, $matches, PREG_OFFSET_CAPTURE))
				{
					$startAtt = $matches[0][0];
					$startAttPosition = $matches[0][1];
					$closeQuotes = strpos(substr($fromSpace, ($startAttPosition + strlen($startAtt))), '"') + $startAttPosition + strlen($startAtt);
					$nextEqual = $startAttPosition + strpos($startAtt, '=');
					$openQuotes = $startAttPosition + strpos($startAtt, '"');
					$nextSpace = strpos(substr($fromSpace, $closeQuotes), ' ') + $closeQuotes;
				}

				// Do we have an attribute to process? [check for equal sign]
				if ($fromSpace != '/' && (($nextEqual && $nextSpace && $nextSpace < $nextEqual) || !$nextEqual))
				{
					if (!$nextEqual)
					{
						$attribEnd = strpos($fromSpace, '/') - 1;
					}
					else
					{
						$attribEnd = $nextSpace - 1;
					}
					// If there is an ending, use this, if not, do not worry.
					if ($attribEnd > 0)
					{
						$fromSpace = substr($fromSpace, $attribEnd + 1);
					}
				}
				if (strpos($fromSpace, '=') !== false)
				{
					// If the attribute value is wrapped in quotes we need to grab the substring from
					// the closing quote, otherwise grab until the next space.
					if (($openQuotes !== false) && (strpos(substr($fromSpace, ($openQuotes + 1)), '"') !== false))
					{
						$attr = substr($fromSpace, 0, ($closeQuotes + 1));
					}
					else
					{
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				}
				// No more equal signs so add any extra text in the tag into the attribute array [eg. checked]
				else
				{
					if ($fromSpace != '/')
					{
						$attr = substr($fromSpace, 0, $nextSpace);
					}
				}

				// Last Attribute Pair
				if (!$attr && $fromSpace != '/')
				{
					$attr = $fromSpace;
				}

				// Add attribute pair to the attribute array
				$attrSet[] = $attr;

				// Move search point and continue iteration
				$tagLeft = substr($fromSpace, strlen($attr));
				$currentSpace = strpos($tagLeft, ' ');
			}

			// Is our tag in the user input array?
			$tagFound = in_array(strtolower($tagName), self::$tagsArray);

			// If the tag is allowed let's append it to the output string.
			if ((!$tagFound && self::$tagsMethod) || ($tagFound && !self::$tagsMethod))
			{
				// Reconstruct tag with allowed attributes
				if (!$isCloseTag)
				{
					// Open or single tag
					$attrSet = self::_cleanAttributes($attrSet);
					$preTag .= '<' . $tagName;
					for ($i = 0, $count = count($attrSet); $i < $count; $i++)
					{
						$preTag .= ' ' . $attrSet[$i];
					}

					// Reformat single tags to XHTML
					if (strpos($fromTagOpen, '</' . $tagName))
					{
						$preTag .= '>';
					}
					else
					{
						$preTag .= ' />';
					}
				}
				// Closing tag
				else
				{
					$preTag .= '</' . $tagName . '>';
				}
			}

			// Find next tag's start and continue iteration
			$postTag = substr($postTag, ($tagLength + 2));
			$tagOpen_start = strpos($postTag, '<');
		}

		// Append any code after the end of tags and return
		if ($postTag != '<')
		{
			$preTag .= $postTag;
		}

		return $preTag;
	}

	/**
	 * Internal method to strip a tag of certain attributes
	 *
	 * @param   array  $attrSet  Array of attribute pairs to filter
	 * @return  array  Filtered array of attribute pairs
	 * @access	private
	 * @static
	 */
	private static function _cleanAttributes($attrSet)
	{
		$newSet = array();

		$count = count($attrSet);

		// Iterate through attribute pairs
		for ($i = 0; $i < $count; $i++)
		{
			// Skip blank spaces
			if (!$attrSet[$i])
			{
				continue;
			}

			// Split into name/value pairs
			$attrSubSet = explode('=', trim($attrSet[$i]), 2);

			// Take the last attribute in case there is an attribute with no value
			$attrSubSet[0] = @array_pop(explode(' ', trim($attrSubSet[0])));

			// Remove all "non-regular" attribute names
			// AND blacklisted attributes

			if ((!preg_match('/[a-z]*$/i', $attrSubSet[0]))
				|| ((self::$xssAuto) && ((in_array(strtolower($attrSubSet[0]), (self::$strictMode?self::$attrBlacklist1:self::$attrBlacklist2)))
				|| (substr($attrSubSet[0], 0, 2) == 'on'))))
			{
				continue;
			}

			// XSS attribute value filtering
			if (isset($attrSubSet[1]))
			{
				// Trim leading and trailing spaces
				$attrSubSet[1] = trim($attrSubSet[1]);

				// Strips unicode, hex, etc
				$attrSubSet[1] = str_replace('&#', '', $attrSubSet[1]);

				// Strip normal newline within attr value
				$attrSubSet[1] = preg_replace('/[\n\r]/', '', $attrSubSet[1]);

				// Strip double quotes
				$attrSubSet[1] = str_replace('"', '', $attrSubSet[1]);

				// Convert single quotes from either side to doubles (Single quotes shouldn't be used to pad attr values)
				if ((substr($attrSubSet[1], 0, 1) == "'") && (substr($attrSubSet[1], (strlen($attrSubSet[1]) - 1), 1) == "'"))
				{
					$attrSubSet[1] = substr($attrSubSet[1], 1, (strlen($attrSubSet[1]) - 2));
				}
				// Strip slashes
				$attrSubSet[1] = stripslashes($attrSubSet[1]);
			}
			else
			{
				continue;
			}

			// Autostrip script tags
			if (self::checkAttribute($attrSubSet))
			{
				continue;
			}

			// Is our attribute in the user input array?
			$attrFound = in_array(strtolower($attrSubSet[0]), self::$attrArray);

			// If the tag is allowed lets keep it
			if ((!$attrFound && self::$attrMethod) || ($attrFound && !self::$attrMethod))
			{
				// Does the attribute have a value?
				if (empty($attrSubSet[1]) === false)
				{
					$newSet[] = $attrSubSet[0] . '="' . $attrSubSet[1] . '"';
				}
				elseif ($attrSubSet[1] === "0")
				{
					// Special Case
					// Is the value 0?
					$newSet[] = $attrSubSet[0] . '="0"';
				}
				else
				{
					// Leave empty attributes alone
					$newSet[] = $attrSubSet[0] . '=""';
				}
			}
		}

		return $newSet;
	}
	
	/**
	 * Function to determine if contents of an attribute are safe
	 *
	 * @param   array  $attrSubSet  A 2 element array for attribute's name, value
	 * @return  boolean  True if bad code is detected
	 * @static
	 */
	static function checkAttribute($attrSubSet)
	{
		$attrSubSet[0] = strtolower($attrSubSet[0]);
		$attrSubSet[1] = strtolower($attrSubSet[1]);

		return (((strpos($attrSubSet[1], 'expression') !== false) && ($attrSubSet[0]) == 'style') || (strpos($attrSubSet[1], 'javascript:') !== false) ||
			(strpos($attrSubSet[1], 'behaviour:') !== false) || (strpos($attrSubSet[1], 'vbscript:') !== false) ||
			(strpos($attrSubSet[1], 'mocha:') !== false) || (strpos($attrSubSet[1], 'livescript:') !== false));
	}
	
	
	
}
?>
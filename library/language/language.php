<?php 
/**
 * This file is needed for Language Options of Arta because contains ArtaLanguage class
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
 * ArtaLanguage Class
 * Language loader and Translator.
 */
class ArtaLanguage {

	/**
	 * Language intials e.g. en-US or fa-IR
	 * 
	 * @var	string
	 * @access	private
	 */
	private $name;

	/**
	 * Phrases loaded from language files 
	 * 
	 * @var	array
	 * @access	private
	 */	
	var $phrases = array();
	
	/**
	 * List of processed language files
	 * 
	 * @var	array
	 * @access	private
	 */	
	private $processed=array();
	
	/**
	 * Language Files extension.
	 * 
	 * @var	string
	 * @access	private
	 */	
	private $ext = '.ini';
	
	/**
	 * Phrases that not translated
	 * 
	 * @var	array
	 */	
	var $untransed = array();

	/**
	 * Constructor. Just puts a debug message.
	 */
	function __construct(){
		$debug = ArtaLoader::Debug();
		$debug->report('ArtaLanguage loaded.', 'ArtaLanguage::__construct');
	}

	/**
	 * Initializer.	Gets preferred Language and checks it's existense 
	 * then loads default language file
	 * 
	 * @param	string	$basedir	Client base dir to use languages of it
	 */
	function initialize($basedir=ARTAPATH_CLIENTDIR){
		$known = $this->getKnown($basedir);
		$preferred = ArtaFilterinput::safeAddress($this->getPreferred());
		$debug = ArtaLoader::Debug();
		$debug->report('Preferred Language : '.$preferred, 'ArtaLanguage::initialize');
		$this->name = $preferred;
		if(!in_array($preferred, $known)){
			$u=ArtaLoader::User();
			$g=$u->getGuest();
			$gs=unserialize($g->settings);
			$u=$u->getCurrentUser();
			$us=unserialize($u->settings);
			$sname=CLIENT.'_language';
			if(ArtaFilterinput::safeAddress($preferred)==@ArtaFilterinput::safeAddress($gs->$sname)){
				$db=ArtaLoader::DB();
				$db->setQuery('UPDATE #__userfields SET `default`='.$db->Quote(serialize('default')).' WHERE `extype`=\'library\' AND `var`='.$db->Quote($sname), array('default'));
				$db->query();
				redirect(ArtaURL::getURL());
			
			}elseif(ArtaFilterinput::safeAddress($preferred)==@ArtaFilterinput::safeAddress($us->$sname)){
				$us->$sname=$gs->$sname;
				$db=ArtaLoader::DB();
				$db->setQuery('UPDATE #__users SET settings='.$db->Quote(serialize($us)).' WHERE id='.$u->id, array('settings'));
				$db->query();
				redirect(ArtaURL::getURL());
			}
			die('Language file not found. Use "index.php?language=an_available_language". By default "en-US" is available.');
		}
	
		$this->addtoNeed();	
	}

	/**
	 * Gets available languages.
	 * 
	 * @param	string	$basedir	Client base dir to search languages of it
	 * @return	bool
	 */
	function getKnown($basedir=ARTAPATH_CLIENTDIR){
		return ArtaFile::listDir($basedir.'/languages');
	}

	/**
	 * Gets User Language
	 * 
	 * @return	string
	 */
	function getUserLang(){
		$user = ArtaLoader::User();
		$us = $user->getSetting(CLIENT.'_language', $this->getGuestLang());
		return $us;
	}
	
	/**
	 * Gets Guest Language
	 * 
	 * @return	string
	 */
	function getGuestLang(){
		$user = ArtaLoader::User();
		$gs = $user->getSetting(CLIENT.'_language', 'en-US', 0);
		return $gs;
	}
	
	/**
	 * Gets Preferred Language
	 * 
	 * @return	string
	 */
	function getPreferred(){
		return ArtaFilterinput::safeAddress(getVar('language', $this->getUserLang(), '', 'string'));
	}	
	
	/**
	 * Gets Language name
	 * 
	 * @return	string
	 */
	function getName(){
		return $this->name;
	}

	/**
	 * Adds a Language file to needed files.
	 * 
	 * @param	string	$name	File name e.g. user,config,login,...
	 * @param	string	$type	File name Type e.g. package,module,plugin,cron,...
	 * @param	string	$basedir	Client base dir to load languages of it
	 * @param	string	$lang	language name
	 * @return	string
	 */
	function addtoNeed($name=false, $type=false, $basedir=ARTAPATH_CLIENTDIR, $lang=false){
		if(!$lang){$lang=$this->getPreferred();}
		if($this->checkNeed($name, $type, $basedir, $lang)){
			$this->parse($name, $type, $basedir, $lang);
		}else{
			$name = $this->generateName($lang, $name, $type);
			$GLOBALS['DEBUG']['_LANGUAGE'][] = $name;
		}
	}
	
	/**
	 * Checks existence of a language file.
	 * 
 	 * @param	string	$name	File name e.g. user,config,login,...
	 * @param	string	$type	File name Type e.g. package,module,plugin,cron,...
	 * @param	string	$basedir	Client base dir to load languages of it
	 * @param	string	$lang	language name
	 * @return	bool
	 */
	function checkNeed($name=false, $type=false, $basedir, $lang){
		$name = $this->generateName($lang, $name, $type);
		if(is_file($basedir.'/languages/'.$lang.'/'.$name)){
			return true;
		}
		return false;
	}
	
	/**
	 * Generates Language filenames.
	 *
	 * @param	string	$lang	language name
 	 * @param	string	$name	File name e.g. user,config,login,...
	 * @param	string	$type	File name Type e.g. package,module,plugin,cron,... 
	 */
	function generateName($lang='en-US', $name=false, $type=false){
		if($type && $name){
			$p = $lang.'.'.$type.'.'.$name.$this->ext;
		}
		if(!$type && !$name){
			$p = $lang.$this->ext;
		}
		if(!$type && !$name && !$lang){
			$p = false;
		}
		return $p;
	}

	/**
	 * Parses language files and extracts phrases
	 * 
  	 * @param	string	$name	File name e.g. user,config,login,...
	 * @param	string	$type	File name Type e.g. package,module,plugin,cron,...
	 * @param	string	$basedir	Client base dir to load languages of it
	 * @param	string	$lang	language name
	 */
	function parse($name=false, $type=false, $basedir=ARTAPATH_CLIENTDIR, $lang='en-US'){
		$name=$this->generateName($lang, $name, $type);
		if(in_array($basedir.'/languages/'.$lang.'/'.$name, $this->processed)){
			return;
		}else{
			$GLOBALS['DEBUG']['LANGUAGE'][] = $name;
			$this->processed[]=$basedir.'/languages/'.$lang.'/'.$name;
			$name = ArtaFilterinput::safeAddress($name);
			$content = ArtaFile::read($basedir.'/languages/'.$lang.'/'.$name);
			$content = str_replace(array("\r\n", "\r", "\n"), "\n", $content);
			
			if(substr(trim($content, ' '), -1) !== "\n"){
				$content=$content."\n";
			}
			
			$res= array();
			$fh=explode("\n",$content);
			foreach ($fh as $line) {				
				if(substr($line,0,1) !== '#' && trim($line)!==''){
					if(substr($line,0,7) === '@parse '){
						$parse = trim(substr($line,7));
						$parse = ArtaString::split($parse,'|',false,true); //Escaping allowed
						
						$parse = ArtaUtility::array_extend($parse,
							array(2=>ARTAPATH_CLIENTDIR,3=>'en-US')
						);
						$this->parse(@$parse[0],@$parse[1],@$parse[2],@$parse[3]);
					}else{
						$pos = strpos($line, '=');
						if($pos>0){
							$what = strtoupper(substr($line, 0, $pos));
							$is = substr($line, $pos+1);
							$res[$what]=str_replace('\n', "\n", $is);
						}
					}
				}
			}
			$parsed=$res;	
			foreach($parsed as $k => $v) {
				$this->phrases[$k] = $v;
			}
		}
	}

	/**
	 * Translates a phrase.
	 * 
	 * @param	string	$v	Phrase key
	 * @return	string
	 */
	function translate($v){
		if($v === null){
			return null;
		}
		$vu = strtoupper($v);
		
		if(isset($this->phrases[$vu])){
			return $this->phrases[$vu];
		}else{
			if(!@in_array($vu,$GLOBALS['DEBUG']['UNTRANSED'])){
				$GLOBALS['DEBUG']['UNTRANSED'][] = $vu;
			}
			return $vu;
		}
	}
	
	/**
	 * Indicates that translation is available or not.
	 * 
	 * @param	string	$v	Phrase key
	 * @return	bool
	 */
	function exists($v){
		if($v === null){
			return null;
		}
		$vu = strtoupper($v);
		return isset($this->phrases[$vu]);
	}
	
}

?>
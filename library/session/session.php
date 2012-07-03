<?php
/**
 * ArtaSession Base File.
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
 * ArtaSession class
 * This class starts session using custom settings for Arta Application
 * 
 * @static
 */
class ArtaSession {
	
	/**
	 * Initializes Session and starts it.
	 * @static
	 */
	static function initialize(){
		$config = ArtaLoader::config();
		$db = ArtaLoader::DB();
		//settings
		ini_set('session.cookie_path', $config->cookie_path);
		ini_set('session.cookie_domain', $config->cookie_domain);
		ini_set('session.cookie_lifetime', 0);
		ini_set('session.name', ArtaSession::getSessionName());
		ini_set('session.gc_probability', '1');
		ini_set('session.gc_divisor', '10');
		ini_set('session.use_only_cookies', true);
		if(version_compare(PHP_VERSION, '5.2.0', '>')){
			ini_set('session.cookie_httponly', true);
		}
		ini_set('session.hash_function', 0);
		ini_set('session.hash_bits_per_character', 4);
		session_cache_limiter('nocache');
		
		if(!in_array($config->session_type, array('db', 'file'))){
			die('Invalid Session Type.');
		}
		
		ArtaLoader::Import('session->type->'.$config->session_type);
		if(ArtaSession::handler_init()==false){
			die('Session cannot be initialized.');
			return false;
		}
		// Start!

		if(!session_start()){
			die('Session cannot be initialized.');
			return false;
		}
		
		register_shutdown_function(array('ArtaSession', 'WriteData'));

		$debug = ArtaLoader::Debug();
		$debug->report('Session started.', 'ArtaSession::initialize');
		return true;
	}
	
	/**
	 * Returns Session Cookie name
	 * @static
	 * @return	string
	 */
	static function getSessionName(){
		
		$config = ArtaLoader::Config();
		$id= @md5(base64_encode($config->secret.CLIENT.$_SERVER["HTTP_USER_AGENT"].$_SERVER["REMOTE_ADDR"]));
		while(is_numeric($id{0})){
			$id = substr($id, 1).$id{0};
		}
		return $id;
	}

	/**
	 * Generates tokens to use as client identifier in login pages, forms and etc.
	 * @static
	 * @return	string
	 */
	static function genToken(){
		$sessname=ArtaSession::getSessionName();
		$sessid=session_id();
		return @md5(substr($sessname,0,16).md5($sessid).md5($_SERVER["HTTP_USER_AGENT"].$_SERVER["REMOTE_ADDR"]).substr($sessname,16,32));
	}
	
	/**
	 * Automatically checks that $_REQUEST['token'] equals to token or not.
	 * @static
	 * @param	string	$varname	Request variable name to check 
	 * @return	bool
	 */
	static function checkToken($varname='token'){
		$token=ArtaSession::genToken();
		$var=getVar($varname,'',null,'string');
		return ($token===$var);
	}

	/**
	 * Gets sessions from DB
	 * @static
	 * @return	array
	 */
	static function getSessions(){
		$db = ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__sessions');
		return $db->loadObjectList();
	}

	/**
	 * Gets userid of specified session
	 * @static
	 * @param	string	$sessid	Session ID
	 * @return	mixed
	 */
	static function getSessionUserId($sessid){
		if(isset($sessid) && $sessid == true){
			$db = ArtaLoader::DB();
			$db->setQuery("SELECT userid FROM #__sessions WHERE session_id=".$db->Quote($sessid));
			$res=$db->loadObject();
			return $res->userid;
		}else{
			return false;
		}
	}

	
	################################
	# SESSION HANDLERS
	################################
	static function open($save_path, $session_name)
	{
		$config = ArtaLoader::config();
		eval('return ArtaSession_'.$config->session_type.'::open($save_path, $session_name);');
	}


	static function close()
	{
		$config = ArtaLoader::config();
		eval('$res= ArtaSession_'.$config->session_type.'::close();');
		return $res;
	}

	static function read($id)
	{
		$config = ArtaLoader::config();
		eval('$res= ArtaSession_'.$config->session_type.'::read($id);');
		return $res;
		
	}

	static function write($id, $session_data) 
	{
		if(@$GLOBALS['IGNORE_SESSION_SAVING']==true || @$GLOBALS['SESSION_DATA_SAVED']==true){
			return true;
		}
        if(@$GLOBALS['artamain']->DB==null){
            return false;
        }
        $config=ArtaLoader::Config();
		eval('$res= ArtaSession_'.$config->session_type.'::write($id, $session_data);');
        if($res==true){
            $GLOBALS['SESSION_DATA_SAVED']=true;
        }
		return $res;
	}
    
	static function writeData(){
		// Doing so will postpone session closing to last step of executing shutdown functions.
		register_shutdown_function("session_write_close");
	}

	static function destroy($id)
	{
		$config = ArtaLoader::config();
		eval('$res= ArtaSession_'.$config->session_type.'::destroy($id);');	
		return $res;
	}

	static function gc($max)
	{
		$config = ArtaLoader::config();
		eval('$res= ArtaSession_'.$config->session_type.'::gc($max);');	
		return $res;
	}
	
	/**
	 * Sets session handler
	 * @return	bool
	 */
	private static function handler_init(){
		ini_set('session.save_handler', 'user');
		return session_set_save_handler(
			array('ArtaSession', 'open'),
			array('ArtaSession', 'close'),
			array('ArtaSession', 'read'),
			array('ArtaSession', 'write'),
			array('ArtaSession', 'destroy'),
			array('ArtaSession', 'gc')
			);
	}
}



?>
<?php 
/**
 * ArtaSession DB Storage handler.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaSession
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// detect arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaSession_db class
 * DB Handler for {@link	ArtaSession}
 * 
 * @static
 */
class ArtaSession_db {

	static function open($save_path, $session_name)
	{
		return true;
	}
	
	static function close()
	{
		return true;
	}

	static function read($id)
	{
		$db = ArtaLoader::DB();
		$db->setQuery(sprintf("SELECT * FROM #__sessions WHERE session_id=%s AND session_cookievar=".$db->Quote(ArtaSession::getSessionName())." AND client=".$db->Quote(CLIENT), $db->Quote($id)));
		$s=(object)$db->loadObject();
		if(!isset($s->data)){
			$s->data = null;
		}else{
			$GLOBALS['_LAST_POSITION']=$s->position;
		}
		return (string)$s->data;
	}
	
	static function write($id, $session_data) {
		$db = ArtaLoader::DB();
		$u=@(int)$_SESSION['userid'];
		$path=ArtaLoader::Pathway();
		$path=@$path->getImplodedResult(',', true);
		
		$db->setQuery(sprintf("REPLACE INTO #__sessions (session_id, time, data, userid, client, session_cookievar, position, agent, ip) VALUES ('%s', '%s', '%s', %s, '%s', '%s', '%s', '%s', '%s')", 
		$db->getEscaped($id), 
		$db->getEscaped(time()), 
		$db->getEscaped($session_data), 
		$u ? $db->Quote($u) : 'NULL', 
		$db->getEscaped(CLIENT), 
		ArtaSession::getSessionName(),
		$db->getEscaped(@$GLOBALS['_DISABLE_POSITION_LOGGING']?@$GLOBALS['_LAST_POSITION']:$path), 
		$db->getEscaped(@$_SERVER['HTTP_USER_AGENT']),
		$db->getEscaped($_SERVER['REMOTE_ADDR'])));
		if($db->query()){
			if($db->getError()==false){
				
				return true;
			}else{
				return false;
			}
		}else{return false;}
		
	}

	static function destroy($id)
	{
		if($id == session_id()){
			session_unset();
			if (isset($_COOKIE[ArtaSession::getSessionName()])) {
				$config=ArtaLoader::Config();
				setcookie(ArtaSession::getSessionName(), '', time()-42000, $config->cookie_path, $config->cookie_domain);
			}
		}
		$db =ArtaLoader::DB();
		$db->setQuery(sprintf("DELETE FROM #__sessions WHERE session_id=%s", $db->Quote($id)));
		if($db->query()){
			if($db->getError()==false){
				return true;
			}else{
				return false;
			}
		}else{return false;}
	}

	static function gc($max)
	{
		$db =ArtaLoader::DB();
		$q=sprintf("DELETE FROM #__sessions WHERE time < %s", $db->Quote(time()-$max));
		$db->setQuery($q);
		if(is_dir(ARTAPATH_CLIENTDIR.'/tmp/sessdata')){
			ArtaFile::delete(ARTAPATH_CLIENTDIR.'/tmp/sessdata', true, true);
		}
		if($db->query()){
			if($db->getError()==false){
				return true;
			}else{
				return false;
			}
		}else{return false;}
	}
}

?>
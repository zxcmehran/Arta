<?php
/**
 * ArtaSession File Storage handler.
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
 * ArtaSession_file class
 * File Handler for {@link	ArtaSession}
 * Note that it uses DB yet to fill #__sessions table! Just sessdata is stored in files. 
 * @static
 */ 
class ArtaSession_file {

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
		$s=$db->loadObject();
		if($s!==null){
			$GLOBALS['_LAST_POSITION']=$s->position;
			@include(ARTAPATH_CLIENTDIR.'/tmp/sessdata/'.md5($s->session_id.$s->session_cookievar).'.php');
			if(!isset($data)){$data='';}
		}else{
			$data='';
		}
		return (string)$data;
	}
	
	static function write($id, $session_data) {
		$db = ArtaLoader::DB();
		$u=@(int)$_SESSION['userid'];
		$path=ArtaLoader::Pathway();
		$path=@$path->getImplodedResult(',', true);
		
		$db->setQuery(sprintf("REPLACE INTO #__sessions (session_id, time, data, userid, client, session_cookievar, position, agent, ip) VALUES ('%s', '%s', NULL, %s, '%s', '%s', '%s', '%s', '%s')", 
		$db->getEscaped($id), 
		$db->getEscaped(time()),  
		$u ? $db->Quote($u) : 'NULL', 
		$db->getEscaped(CLIENT), 
		ArtaSession::getSessionName(),
		$db->getEscaped(@$GLOBALS['_DISABLE_POSITION_LOGGING']?@$GLOBALS['_LAST_POSITION']:$path), 
		$db->getEscaped(@$_SERVER['HTTP_USER_AGENT']), 
		$db->getEscaped($_SERVER['REMOTE_ADDR'])));

		ArtaFile::chmod(ARTAPATH_CLIENTDIR.'/tmp/sessdata', 0755);
		
		//make data and put it
		$f = ArtaFile::write(ARTAPATH_CLIENTDIR.'/tmp/sessdata/'.md5($id.ArtaSession::getSessionName()).'.php', 
		"<?php eval(base64_decode(\"".
			base64_encode(
				"\$data='".ArtaFilteroutput::PHPValue($session_data)."';"
			)
		."\"));?>");

		if(!is_file(ARTAPATH_CLIENTDIR.'/tmp/sessdata/WARNING.txt')) {
			ArtaFile::write(ARTAPATH_CLIENTDIR.'/tmp/sessdata/WARNING.txt', 
			"DO NOT REMOVE THIS DIRECTORY CONTENTS. \r\nDOING SO MAY PURGE USERS DATA! \r\nYou only should delete directory contents when session mode is set to DB Mode.");
		}
		if($f AND $db->query()){
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
		$db->setQuery(sprintf("SELECT session_cookievar FROM #__sessions WHERE session_id=%s", $db->Quote($id)));
		$varname=$db->loadResult();
		$db->setQuery(sprintf("DELETE FROM #__sessions WHERE session_id=%s", $db->Quote($id)));
		$s = ArtaFile::unlink(ARTAPATH_CLIENTDIR.'/tmp/sessdata/'.md5($id.$varname).'.php');
		if($db->query() && $s){
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
		$q=sprintf("SELECT * FROM #__sessions WHERE time < '%s'", $db->getEscaped(time()-$max));
		$db->setQuery($q);
		$files=$db->loadObjectList();
		$s=true;
		foreach($files as $f){
			$r = ArtaFile::unlink(ARTAPATH_CLIENTDIR.'/tmp/sessdata/'.md5($f->session_id.$f->session_cookievar).'.php');
			if($r == false & $s==true){$s=false;}
		}
		$fs=ArtaFile::listDir(ARTAPATH_CLIENTDIR.'/tmp/sessdata');
		foreach($fs as $f){
			$p=ARTAPATH_CLIENTDIR.'/tmp/sessdata/'.$f;
			if(is_file($p) && @filemtime($p)<(time()-$max)){
				ArtaFile::delete($p);
			}
		}
		$q=sprintf("DELETE FROM #__sessions WHERE time < '%s'", $db->getEscaped(time()-$max));
		$db->setQuery($q);
		if($db->query() && $s){
			if($db->getError()==false){
				return true;
			}else{
				return false;
			}
		}else{return false;}
	}
}
?>
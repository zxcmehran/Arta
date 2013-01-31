<?php 
/**
 * ArtaUser Helper
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaUser
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// detect arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaUserHelper Class
 * User Helper Functions.
 * @static
 */
class ArtaUserHelper{
	
	/**
	 * Logins a User
	 * @static
	 * @param	string	$username	Username
	 * @param	string	$password	MD5()ed Password OR openid server on OpenID login method.
	 * @param	bool	$is_openid	is OpenID login?
	 * @return	string	'BANNED' or 'NOT_ACTIVE' or 'COMPLETE' or 'ERROR_LOADING'
	 */
	static function Login($username, $password, $is_openid=false){
		$p=ArtaLoader::Plugin();
		$username = ArtaUTF8::strtolower($username);
		$user=ArtaLoader::User();
		$user = $user->getUser($username, 'username');
		if(isset($user->id) && ($is_openid==true || $user->password === ArtaString::hash($password, 'artahash-no-md5'))){
			
			if($is_openid==true){
				$valid=ArtaUserHelper::getOpenIDValidation($user->id, $password);
				if($valid==false){
					return 'INVALID_OPENID';
				}
			}
			
			if($user->ban == 1){
				return 'BANNED';
			}
			
			if($user->activation !== '0'){
				return 'NOT_ACTIVE';
			}
			
			$p->trigger('onBeforeLoginUser', array(&$user, $is_openid));
			$_SESSION['userid']=$user->id;
			ArtaUserHelper::updateLastVisit($user->id);
			$_SESSION['pass']=$user->password;
			
			$p->trigger('onAfterLoginUser', array(&$user, $is_openid));
			return 'COMPLETE';
		}else{
			return 'ERROR_LOADING';
		}
	}
	
	/**
	 * Verifies OpenID Server with one of the users using map.
	 * @static
	 * @param	int	$uid	user ID
	 * @param	string	$server	Server URI
	 */
	static function getOpenIDValidation($uid, $server){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT `userid` FROM #__openid_map WHERE `userid`='.$db->Quote($uid).' AND `server_url`='.$db->Quote($server));
		$r=$db->loadResult();
		if($r==$uid){
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Logouts a user
	 * @static
	 * @param	string	$usessid	User SessionID
	 * @return	bool
	 */
	static function Logout($usessid){
		$p=ArtaLoader::Plugin();
		$p->trigger('onBeforeLogoutUser', array(&$usessid));
		$res = ArtaSession::destroy($usessid);  // Removes session contents and cookies.
		$GLOBALS['IGNORE_SESSION_SAVING']=true; // Avoid recreation of user session content on script shutdown.
		$p->trigger('onAfterLogoutUser', array(&$usessid));
		return $res;
		
	}

	/**
	 * Returns Ban reason of a user
	 * @static
	 * @param	string	$id	User id or username or ...
	 * @param	string	$by	Select user by id,username,email or etc.
	 * @return	mixed
	 */
	static function getBanReason($id, $by='username'){
		$user=ArtaLoader::User();
		$user = $user->getUser($id, $by);
		if($user->ban == null){
			return false;
		}
		if($user->ban_reason == null){
			$user->ban_reason = trans('NO REASON');
		}
		return $user->ban_reason;
	}

	/**
	 * Activates a user
	 * @static
	 * @param	string	$hash	Activation Hash
	 * @return	bool
	 */
	static function Activate($hash){
		$p=ArtaLoader::Plugin();
		$user=ArtaLoader::User();
		$user = $user->getUser($hash, 'activation');
		if($user->id == null){
			return false;
		}
		$p->trigger('onBeforeActivateUser', array(&$user, &$hash));
		$db=ArtaLoader::DB();
		$db->setQuery("UPDATE #__users SET `activation` = 0 WHERE id=".$db->Quote($user->id), array('activation'));
		$r=$db->query();
		if($r){
			$p->trigger('onAfterActivateUser', array(&$user, &$hash));
			return true;
		}else{
			return false;
		}
	}
	
	/**
	 * Update user last visit date to current time
	 * @static
	 * @param	int	$uid	User ID
	 */
	static function updateLastVisit($uid){
		ArtaLoader::Import('misc->date');
		$db = ArtaLoader::DB();
		$db->setQuery("UPDATE #__users SET lastvisit_date = ".$db->Quote(ArtaDate::toMySQL(time()))." WHERE id = ".$db->Quote($uid), array('lastvisit_date'));
		$db->query();
	}

	/**
	 * Gets User Notes
	 * @static
	 * @param	string	$id	User id or username or ...
	 * @param	string	$by	Select user by id,username,email or etc.
	 * @return	mixed
	 */
	static function getText($id, $by='username'){
		$user=ArtaLoader::User();
		$user = $user->getUser($id, $by);
		$db = ArtaLoader::DB();
		$db->setQuery("SELECT `text` FROM #__usertext WHERE uid = ".$db->Quote($user->id));
		$t=$db->loadObject();
		if(is_object($t)){
			return $t->text;
		}else{
			return false;
		}
	}

	/**
	 * Gets User Notes modified time
	 * @static
	 * @param	string	$id	User id or username or ...
	 * @param	string	$by	Select user by id,username,email or etc.
	 * @return	mixed
	 */
	static function getModified($id, $by='username'){
		$user=ArtaLoader::User();
		$user = $user->getUser($id, $by);
		$db = ArtaLoader::DB();
		$db->setQuery("SELECT `modified` FROM #__usertext WHERE uid = ".$db->Quote($user->id));
		$t=$db->loadObject();
		if(is_object($t)){
			return $t->modified;
		}else{
			return false;
		}
	}

	/**
	 * Sets User Notes
	 * @static
	 * @param	string	$text	User note
	 * @param	string	$id	User id or username or ...
	 * @param	string	$by	Select user by id,username,email or etc.
	 * @return	bool
	 */
	static function setText($text, $id, $by='username'){
		$user=ArtaLoader::User();
		ArtaLoader::Import('misc->date');
		$date=time();
		$user = $user->getUser($id, $by);
		if($user==null || $user->id==0){
			return false;
		}
		$db = ArtaLoader::DB();
		$db->setQuery("REPLACE INTO #__usertext (`uid`, `text`, `modified`)  VALUES (".$db->Quote($user->id).", ".$db->Quote($text).", ".$db->Quote(ArtaDate::toMySQL($date)).")");
		$t=$db->query();
		if($t){
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Registers a user.
	 * @static
	 * @param	string	$name	User real name
	 * @param	string	$username	Username
	 * @param	string	$email	User E-Mail
	 * @param	string	$password	User password passed from md5()
	 * @param	string	$usergroup	Usergroup
	 * @param	string	$misc	User Misc data
	 * @param	bool	$is_openid	Is OpenID registeration?
	 * @return	string	'INCOMPLETE' or 'USERNAME_EXISTS' or 'EMAIL_EXISTS' or 'COMPLETE' or 'ERROR'
	 */
	static function Register($name, $username, $email, $password, $usergroup, $misc=null, $is_openid=false){			

		$p=ArtaLoader::Plugin();
		// <Existence test>
		if($name==null || $username==null || $email==null|| $password==null || $usergroup==null){
			return 'INCOMPLETE';
		}
		$user = ArtaLoader::User();
		$test = $user->getUser($username, 'username', false);
		if(is_object($test)){
			return 'USERNAME_EXISTS'; // must use LOWER(`usename`)
		}
		$test = $user->getUser($email, 'email', false);
		if(is_object($test)){
			return 'EMAIL_EXISTS'; // must use LOWER(`email`)
		}
		// </Existence test>
		
		ArtaLoader::Import('misc->date');
		$reg_date = ArtaDate::toMySQL(time());
		$sets=new stdClass;
		$sets=serialize($sets);
		if($misc==null){
			$misc=new stdClass;
			$misc=serialize($misc);
		}
		$db = ArtaLoader::DB();
		$ac=md5(ArtaString::makeRandStr(8));
		if($is_openid==true){
			$ac=0;
			$openid = $password;
			$password='_'.ArtaString::makeRandStr(31);
			
		}else{
			$password=ArtaString::hash($password, 'artahash-no-md5');
		}
		$p->trigger('onBeforeRegisterUser', array(&$name,&$username,&$email,&$misc,&$sets,&$reg_date,&$usergroup, &$ac, $is_openid));
		
		$db->setQuery("INSERT INTO #__users (`name`, `username`, `email`, `password`, `misc`, `settings`, `register_date`, `usergroup`, `activation`) VALUES (".$db->Quote($name).", ".
			$db->Quote($username).", ".
			$db->Quote($email).", ".
			$db->Quote($password).", ".
			$db->Quote($misc).", ".
			$db->Quote($sets).", ".
			$db->Quote($reg_date).", ".
			$db->Quote($usergroup).", ".
			$db->Quote($ac).")");
		$r=$db->query();
		if($r){
			$id=$db->getInsertedID();
			if(isset($openid)){
				$db->setQuery('INSERT INTO #__openid_map VALUES(NULL, '.$db->Quote($id).','.$db->Quote($openid).')');
				if($db->query()==false){
					ArtaError::show(500);
				}
			}
			$p->trigger('onAfterRegisterUser', array(&$name,&$username,&$email,&$misc,&$sets,&$reg_date,&$usergroup, &$ac, $is_openid, $id));
			
			return 'COMPLETE';
		}else{
			return 'ERROR';
		}
	}

	/**
	 * Resets User Password.
	 * @static
	 * @param	int	$id	User ID
	 * @param	string	$pass	New Password passed from md5()
	 * @return	bool
	 */
	static function resetPassword($id, $pass){
		$db=ArtaLoader::DB();
		$db->setQuery("UPDATE #__users SET password=".$db->Quote(ArtaString::hash($pass, 'artahash-no-md5'))." WHERE id=".$db->Quote($id));
		return $db->query();
	}

	/**
	 * Deletes a user.
	 * @static
	 * @param	string	$id	User id or username or ...
	 * @param	string	$by	Select user by id,username,email or etc.
	 * @return	bool
	 */
	static function Delete($id, $by='id'){
		$db=ArtaLoader::DB();
		if($by!=='id'){
			$db->setQuery("SELECT id FROM #__users WHERE `".$db->getCEscaped($by)."` = ".$db->Quote($id));
			$r=$db->loadObject();
			$uid=$r->id;
		}else{
			$uid=$id;
		}
		$db->setQuery("DELETE FROM #__users WHERE `".$db->getCEscaped($by)."` = ".$db->Quote($id));
		$r=$db->query();
		if($r==true){
			$db->setQuery("DELETE FROM #__usertext WHERE `uid` = ".$db->Quote($uid));
			$r=$db->query();
			if($r==true){
				$db->setQuery("DELETE FROM #__openid_map WHERE `userid` = ".$db->Quote($uid));
				$r=$db->query();
			}
		}
		return $r;
	}

}

?>
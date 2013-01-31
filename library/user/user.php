<?php 
/**
 * Arta User Manager and Loader
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// detect arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaUser Class
 * User Loader and manager
 */
class ArtaUser {

	/**
	 * Guest user data
	 * @var	array
	 */
	var $guest = null;
	
	/**
	 * Current user data
	 * @var	array
	 */
	var $user = null;
	
	/**
	 * Settings cache
	 * @var	array
	 */
	var $cache_setting=array();
	
	/**
	 * Miscs cache
	 * @var	array
	 */
	var $cache_misc=array();
	
	/**
	 * Constructor
	 */
	function __construct(){
		$debug = ArtaLoader::Debug();
		$debug->report('ArtaUser Started', 'ArtaUser::__construct');
		return true;
	}
	
	/**
	 * Initializes User class
	 */
	 function initialize(){
	 	if(!isset($this->guest)){
			$g = $this->getUser(0);
			$this->guest = $g;
			$p=ArtaLoader::Plugin();
			$p->trigger('onInitializeGuest', array(&$this->guest));
		}

		if(isset($_SESSION['userid']) && is_numeric($_SESSION['userid'])){
			$u = $this->getUser($_SESSION['userid']);
			// validate this user 
			if($u==null){
				ArtaError::show(401);
			}
			
			$this->user= $u;
			ArtaLoader::Import('user->helper');
			
			if((string)$this->user->activation !=='0'){
				ArtaUserHelper::Logout(session_id());
				ArtaError::show(401, trans('YOU ARE NOT ACTIVE'));
			}
			if($this->user->ban==true){
					$reason=ArtaUserHelper::getBanReason($this->user->username, 'username');
					$reason= $reason ? $reason : trans('NO REASON');
					ArtaUserHelper::Logout(session_id());
					ArtaError::show(401, trans('YOU ARE BANNED').' <br/> '.trans('REASON').': '.$reason);
			}
			if((string)$this->user->password!='' && (string)$this->user->password !==(string)@$_SESSION['pass']){
				if(ArtaUserHelper::Logout(session_id())){
					redirect('index.php');
				}
				ArtaError::show(401);
			}
			
			$c=ArtaLoader::Config();
			$c->time_offset=(float)$this->getSetting('time_offset',$c->time_offset);
			$c->time_offset= substr((string)$c->time_offset, 0, 1)=='-' ? $c->time_offset : '+'.$c->time_offset;
			$c->cal_type=$this->getSetting('cal_type',$c->cal_type);
			
			$ug=ArtaUserGroup::getUsergroup($u->usergroup);

			if($ug->active == 0){
				ArtaError::show(401, trans('YOUR USERGROUP IS NOT ACTIVE'));
			}			
		}
		
		return true;
	 }
	
	/**
	 * Gets Usergroup of an user
	 * @param	mixed	$userid	User ID. Current User on False 
	 * @return	int	Usergroup ID 
	 */
	function getUserGroup($userid = FALSE){
		$db =ArtaLoader::DB();
		if(!$userid){
			$user = $this->getCurrentUser();
			return $user->usergroup;
		}
		if(!is_numeric($userid)){
			return false;
		}

		$q = "SELECT usergroup FROM #__users WHERE id=".$db->Quote($userid);
		$db->setQuery($q);
		$u = $db->loadObject();
		return @$u->usergroup;
	}

	/**
	 * Gets an user from DB. This method uses runtime caching.
	 * @param	string	$id	User id or username or ...
	 * @param	string	$by	Select user by id,username,email or etc.
	 * @param	bool	$case_sensitive
	 * @return	mixed
	 */
	function getUser($id=false, $by='id', $case_sensitive=true) {
		$db = ArtaLoader::DB();
		if($id===false || $id < 0){
			return false;
		}
		if($id >= 0){
			if(((int)$id==0 && $by=='id') || ($id=='guest' && $by='username')){
				if(!isset($GLOBALS['CACHE']['users.guest'])){
					$cached= ArtaCache::getData('user','guest');
					if($cached!=false){
						$GLOBALS['CACHE']['users.guest']=$cached;
					}else{
						
						$u=new stdClass;
						$u->id=0;
						if(isset($GLOBALS['artamain']->lang->phrases['GUEST'])){
							$u->name=trans('Guest');
						}else{
							$u->name='Guest';
						}
						$u->username='guest';
						$u->email='guest';
						$u->password='guest';
						$u->usergroup='0';
						$config=ArtaLoader::Config();
						if($config->offline == 0){
							$u->ban=null;
						}else{
							$u->ban=$config->offline;
						}
						$u->activation='0';
						$db->setQuery("SELECT * FROM #__userfields WHERE fieldtype='setting'");
						$data=$db->loadObjectList();
						$ss='';
						$i=0;
						foreach($data as $v){
							$i++;
							$ss .='s:'.strlen($v->var).':"'.$v->var.'";';
							$ss .=$v->default;
						}
						$u->settings='O:8:"stdClass":'.$i.':{'.$ss.'}';
						$db->setQuery("SELECT * FROM #__userfields WHERE fieldtype='misc'");
						$data=$db->loadObjectList();
						$ss='';
						$i=0;
						foreach($data as $v){
							$i++;
							$ss .='s:'.strlen($v->var).':"'.$v->var.'";';
							$ss .=$v->default;
						}
						$u->misc='O:8:"stdClass":'.$i.':{'.$ss.'}';
						$u->avatar='';
						ArtaCache::putData('user','guest',$u);
						$GLOBALS['CACHE']['users.guest']=$u;
						
						
					}
				}
				return $GLOBALS['CACHE']['users.guest'];
			}else{
				if($by=='id' && isset($GLOBALS['CACHE']['users.rows'][$id])){
					return $GLOBALS['CACHE']['users.rows'][$id];
				}
				if($case_sensitive){
					$q="SELECT * FROM #__users WHERE ".$db->CQuote($by)."=".$db->Quote($id);
				}else{
					$q="SELECT * FROM #__users WHERE LOWER(".$db->CQuote($by).")=".$db->Quote(ArtaUTF8::strtolower($id));
				}
				$db->setQuery($q);
				$r =$db->loadObject();
				if($r!==null){
					$GLOBALS['CACHE']['users.rows'][$r->id]=$r;
				}
				return $r;
			}
			
		}
	}

	
	/**
	 * Returns Current User
	 * @return	array
	 */
	function getCurrentUser() {
		if(!isset($this->user->id)){
			return $this->getGuest();
		}else{
			return $this->user;
		}
	}
	
	/**
	 * Returns Guest User
	 * @return	array
	 */
	function getGuest(){
		return $this->guest;
	}

	 /**
	 * Gets settings from Database
	 *
	 * @param	string	$what	Variable to get value
	 * @param	string	$default	Default value to pass if no records were at database
	 * @param	int	$userid		Username id
	 * @return	mixed	$what value in database
	 */
	 function getSetting($what, $default=null, $userid=null){
		if($userid===null){
			$u=$this->getCurrentUser();
			$userid=$u->id;
		}
		if(array_key_exists($userid, $this->cache_setting)){
			$result = $this->cache_setting[$userid];
		}else{
			$current=$this->getCurrentUser();

			if($userid === null || $userid == $current->id){
				$result = $current->settings;
			}else{
				$result=$this->getUser($userid);
				$result= $result->settings;
			}		
			if((string)$result == ''){
				$guest = $this->getGuest();
				$result= $guest->settings;	
			}
			$result = unserialize($result);
			$this->cache_setting[$userid]=$result;
		}
		
		return isset($result->$what) ? $result->$what : $default;
	}
	
	 /**
	 * Gets Miscellaneous parameters from Database
	 *
	 * @param	string	$what	Variable to get value
	 * @param	string	$default	Default value to pass if no records were at database
	 * @param	int	$userid		Username id
	 * @return	mixed	$what value in database
	 */
	 function getMisc($what, $default=null, $userid=0){
		if($userid==0){
			$u=$this->getCurrentUser();
			$userid=$u->id;
		}
		if(array_key_exists($userid, $this->cache_misc)){
			$result = $this->cache_misc[$userid];
		}else{
			$current=$this->getCurrentUser();

			if($userid == 0 || $userid == $current->id){
				$result = $current->misc;
			}else{
				$result=$this->getUser($userid);
				$result= $result->misc;
			}			
			if((string)$result == ''){
				$guest = $this->getGuest();
				$result= $guest->misc;	
			}
			$result = unserialize($result);
			$this->cache_misc[$userid]=$result;
		}

		return isset($result->$what) ? $result->$what : $default;
	}
}

?>
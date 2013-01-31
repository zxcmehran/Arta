<?php
/**
 * Implements OpenID storage using ArtaDB class.
 * 
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

if(!defined('ARTA_VALID')) {
	die('No access');
}

require_once 'Auth/OpenID/Interface.php';
require_once 'Auth/OpenID/HMAC.php';

class Auth_OpenID_ArtaStore extends Auth_OpenID_OpenIDStore {

    function __construct()
    {
        if(mt_rand(0,10)==5){
        	$this->cleanupAssociations();
        }
        if(mt_rand(0,10)==5){
        	$this->cleanupNonces();
        }
    }

    function storeAssociation($server_url, $association)
    {
    	$db=ArtaLoader::DB();
    	$db->setQuery('INSERT INTO #__openid_assocs VALUES('.
		$db->Quote($server_url).','.
		$db->Quote($association->handle).','.
		$db->Quote(base64_encode($association->secret)).','.
		$db->Quote($association->issued).','.
		$db->Quote($association->lifetime).','.
		$db->Quote($association->assoc_type).
		')');
		return $db->query();
    }

    function getAssociation($server_url, $handle = null)
    {
    	$db=ArtaLoader::DB();
    	if($handle==null){
    		$w='';
    	}else{
    		$w=' WHERE `handle`='.$db->Quote($handle);
    	}
    	$db->setQuery('SELECT * FROM #__openid_assocs'.$w.' ORDER BY `issued`');
		$r=$db->loadObjectList();
		if($r==null){
			return null;
		}
		
		$d=array();
		foreach($r as $a){
			$assoc= new Auth_OpenID_Association($a->handle,
                                                 base64_decode($a->secret),
                                                 $a->issued,
                                                 $a->lifetime,
                                                 $a->type);
			if($assoc->getExpiresIn() == 0){
				$this->removeAssociation($server_url, $assoc->handle);
			}else{
				$d[]=$assoc;
			}
			if($handle==null){
				break;
			}
		}
		if($d==array()){
			return null;
		}
		if(count($d)==1){
			$d=$d[0];
		}
		return $d;
    }


    function removeAssociation($server_url, $handle)
    {
        $db=ArtaLoader::DB();
    	$db->setQuery('DELETE FROM #__openid_assocs WHERE `server_url`='.$db->Quote($server_url).' AND `handle`='.$db->Quote($handle));
    	return $db->query();
    }
    
    
    function cleanupAssociations()
    {
        $db=ArtaLoader::DB();
    	$db->setQuery('DELETE FROM #__openid_assocs WHERE `issued` + `lifetime` < '.time());
    	if($db->query()){
    		return $db->getAffectedRows();
   		}else{
   			return 0;
   		}
    }
    

    function useNonce($server_url, $timestamp, $salt)
    {
        global $Auth_OpenID_SKEW;

        if ( abs($timestamp - time()) > $Auth_OpenID_SKEW ) {
            return false;
        }
        
        $db=ArtaLoader::DB();
        
        $db->setQuery('SELECT * FROM #__openid_nonces WHERE 
			`server_url`='.$db->Quote($server_url).' AND 
			`timestamp`='.$db->Quote($timestamp).' AND 
			`salt`='.$db->Quote($salt));
		$r=$db->loadObject();
        if($r==null){
	    	$db->setQuery('INSERT INTO #__openid_nonces VALUES('.
			$db->Quote($server_url).','.
			$db->Quote($timestamp).','.
			$db->Quote(base64_encode($salt)).
			')');
			$db->query();
			return true;
		}else{
			return false;
		}
        
    }

	function cleanupNonces()
    {
    	global $Auth_OpenID_SKEW;
        $v = time() - $Auth_OpenID_SKEW;
        
        $db=ArtaLoader::DB();
    	$db->setQuery('DELETE FROM #__openid_nonces WHERE `timestamp` < '.$v);
    	if($db->query()){
    		return $db->getAffectedRows();
   		}else{
   			return 0;
   		}
    }
	
}

?>
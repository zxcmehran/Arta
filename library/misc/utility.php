<?php 
/**
 * Just some Utilities.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaUtility class
 * Includes some useful utilities.
 * 
 * @static
 */
class ArtaUtility{
	
	private static $bruteForceRows = array();
	
	/**
	 * Replaces keys of an array or object with one of childs in itself.
	 * It works with arrays(objects) in array(object)
	 * e.g.
	 * array(2) {
	 *   [0]=> array(1) {
	 *     ["name"]=> string(6) "mehran"
	 *   }
	 *   [1]=> array(1) {
	 *     ["name"]=> string(5) "ahadi"
	 *   }
	 * }
	 *  
	 * @static
	 * @param	mixed	$d	Array or object to rekey.
	 * @param	string	$by	key of one of child childs. You can use 'name' in above example
	 * @param	bool	$subarray	if set true, on duplicate values for an key, makes that key array containing that two values.
	 * @return 	mixed
	 */
	
	static function keyByChild($a=array(), $by=null, $subarray=false){
		$set=array();
		$r=array();
		$res=array();
		$first_order=array();
		if(is_array($a)){
			// ok
			foreach($a as $k=>$v){
				if(is_array($v)){
					// ARRAY
					if(isset($v[$by])){
						$key=$v[$by];
						if(isset($r[$key]) && $subarray==true){
							// OTHER TIMES
							if($set[$key]==true){
								// SECOND TIME
								$r[$key]=array($r[$key]);
								$set[$key]=false;
							}
							$r[$key][]=$v;
						}else{
							// FIRST TIME
							$r[$key]=$v;
							$set[$key]=true;
						}
					}
				}elseif(is_object($v)){
					// OBJECT
					if(isset($v->$by)){
						$key=$v->$by;
						if(isset($r[$key]) && $subarray==true){
							// OTHER TIMES
							if($set[$key]==true){
								// SECOND TIME
								$r[$key]=array($r[$key]);
								$set[$key]=false;
							}
							$r[$key][]=$v;
						}else{
							// FIRST TIME
							$r[$key]=$v;
							$set[$key]=true;
						}
					}
				}
			}

			return $r;
		}else{
			return false;
		}
	} 

	/**
	 * Sorts array by one of childs in itself.
	 * It works with arrays(objects) in array(object)
	 * e.g.
	 * array(2) {
	 *   [0]=>
	 *   array(1) {
	 *     ["name"]=>
	 *     string(6) "mehran"
	 *   }
	 *   [1]=>
	 *   array(1) {
	 *     ["name"]=>
	 *     string(5) "ahadi"
	 *   }
	 * }
	 *  
	 * @static
	 * @param	mixed	$d	Array or object to resort.
	 * @param	string	$by	key of one of child childs. You can use 'name' in above example
	 * @param	bool	$reverse	Sort descending?
	 * @return 	mixed
	 */
	static function sortByChild($d=array(), $by=null, $reverse=false){
		$order=array();
		foreach($d as $k=>$v){
			if(is_object($v)){
				eval('$order[$k]=$v->'.$by.';');
			}elseif(is_array($v)){
				eval('$order[$k]=$v["'.$by.'"];');
			}
		}
		if($reverse==false){
			asort($order);
		}else{
			arsort($order);
		}

		if(is_array($d)){
			$new=array();
			foreach($order as $k=>$v){
				$new[$k]=$d[$k];
			}
		}else{
			$new=new stdClass;
			foreach($order as $k=>$v){
				$new->$k=$d[$k];
			}
		}
		return $new;
	}
	
	/**
	 * Converts objects to Arrays
	 * 
	 * @static
	 * @param	object	$o	Object to convert
	 * @return	array
	 */
	static function object2array($o){
		$r=array();
		foreach($o as $k=>$v){
			$r[$k]=$v;
		}
		return $r;
	}
	
	/**
	 * Converts Arrays to objects.
	 * Note: This method adds an "_" at first of any key that starts with invalid char.
	 * 
	 * @static 
	 * @param	array	$a	Array to convert
	 * @return	object
	 */
	static function array2object($a){
		$r=new stdClass;
		foreach($a as $k=>$v){
			/*if(is_numeric($k{0})){
				$k='_'.$k;
			}*/
			$r->$k=$v;
		}
		return $r;
	}
	
	/**
	 * Checks existence of a var in objects.
	 * 
	 * @static
	 * @param	string	$value	varname
	 * @param	object	$object	Object to look into.
	 * @return	bool
	 */
	static function in_object($value,$object){
		/*foreach($object as $k=>$v){
			if($v == $value){
				return true;
			}
		}
		return false;*/
		return array_key_exists($value, $object);
	}
	
	/**
	 * Extends an array or object(just by vars).
	 * 
	 * @static
	 * @param	mixed	$a	Array or Object to extend
	 * @param	array	$e	Extender array (You must specify *array* to extend objects too)
	 * @return	mixed	array or object depending to your input in $a
	 */
	static function array_extend($a, $e){
		foreach($e as $k=>$v){
			if(array_key_exists($k,$a)==false){
				if(is_array($a)){
					$a[$k]=$v;
				}else{
					if(is_numeric($k{0})){
						$k='_'.$k;
					}
					$a->$k=$v;
				}
			}
			
		}
		return $a;
	}
	
	/**
	 * Checks for a set of keys exsitense in array or object.
	 * 
	 * @static
	 * @param	array	$keys	Keys to check
	 * @param	mixed	$res	Resource. Array or Object.
	 * @return	bool
	 */
	static function keysExists($keys, $res){
		if(is_array($res)){
			foreach($keys as $v){
				if(!array_key_exists($v, $res)){
					return false;
				}
			}
		}else{
			foreach($keys as $v){
				if(!isset($res->$v)){
					return false;
				}
			}
		}
		return true;
	}
	
	/**
	 * Denies brute-force attacks by delaying repeated requests.
	 * Can used to secure resource dependent processes (like password resetting system which will send a mail by a request, or search which executes heavy SQLs).
	 * It can prevent DoS (Denial of Service) attacks.
	 * It can limit users by requests count limit. 
	 * @param	string	$name	Timer identifier which will be used to detect last request time by a map located at db.
	 * @param	mixed	$time_msg	A message to appear when timeout lock denies user. You can pass a callback to an outer function too.
	 * @param	mixed	$req_msg	A message to appear when request count lock denies user. You can pass a callback to an outer function too.
	 */
	static function denyBruteForce($name, $time_msg=null, $req_msg=null){
		$db=ArtaLoader::DB();
		if(mt_rand(1,5)==3){ // garbage cleaning
			$db->setQuery('DELETE FROM #__bruteforce_blocker WHERE (`time`+`timeout` < '.time().' AND `req_max`=0) OR (`time`+`timeout` < '.time().' AND `req_max`>0 AND `time`+`req_timeout` < '.time().')');
			$db->query();
		}
		
		if($name==null) return;
		$name=substr($name, 0, 20);
		
		$r = self::_getBruteForceRow($name);
		
		if($r!=null){
			// timeout limiter
			if($r->timeout>0 AND $r->time + $r->timeout >= time()){
				if(is_callable($time_msg)){
					call_user_func($time_msg, $r->time, $r->timeout);
					die();
				}else{
					header('HTTP/1.1 403 Forbidden');
					die(strlen($time_msg)?$time_msg:'Please try again in few seconds.');
				}
			}
			
			// max request count limiter
			if($r->req_max>0 AND $r->requests>=$r->req_max AND $r->time + $r->req_timeout >= time()){
				if(is_callable($req_msg)){
					call_user_func($req_msg, $r->time, $r->req_timeout, $r->requests, $r->req_max);
					die();
				}else{
					header('HTTP/1.1 403 Forbidden');
					die(strlen($req_msg)?$req_msg:'Please try again in few minutes.');
				}
			}
		}
		
	}
	
	/**
	 * Adds a request attempt to bruteforce blocker map
	 * @param	string	$name	Timer identifier which will be used to detect last request time by a map located at db.
	 * @param	int	$timeout	Timer duration. e.g. minimum seconds between two requests. it cannot be larger than 99 seconds.
	 * @param	int	$requests	Maximum requests a user can perform. it cannot be larger than 99 requests.
	 * @param	int	$req_timeout	Timer duration of "request count" reset action. User can continue making requests after $req_time seconds if his possible request quotas are finished. it cannot be larger than 999 seconds.
	 */
	static function addBruteForce($name, $timeout=5, $requests=0, $req_timeout=300){
		if($timeout<=0 && $requests<=0) return;
		if($name==null) return;
		$name=substr($name, 0, 20);
		$db=ArtaLoader::DB();
		$r = self::_getBruteForceRow($name);
		if($r){
			if($r->time + $r->timeout < time() AND $r->req_max<=0){
				$db->setQuery('UPDATE #__bruteforce_blocker SET `time`='.time().' WHERE `name`='.$db->Quote($name).' AND `ip`='.$db->Quote($_SERVER["REMOTE_ADDR"]));
				$set = true;
			}
			
			if($r->req_max>0 AND $r->requests>=$r->req_max AND $r->time + $r->req_timeout < time()){
				$db->setQuery('UPDATE #__bruteforce_blocker SET `time`='.time().', requests=1 WHERE `name`='.$db->Quote($name).' AND `ip`='.$db->Quote($_SERVER["REMOTE_ADDR"]));
				$set = true;
			}elseif($r->req_max>0 AND $r->requests<$r->req_max){
				$db->setQuery('UPDATE #__bruteforce_blocker SET `time`='.time().', requests=requests+1 WHERE `name`='.$db->Quote($name).' AND `ip`='.$db->Quote($_SERVER["REMOTE_ADDR"]));
				$set = true;
			}
			if(isset($set)){
				if($db->query() AND isset(self::$bruteForceRows[$name])){
					unset(self::$bruteForceRows[$name]);
				}
			}
			
		}else{
			$timeout= $timeout>99?99:(int)$timeout;
			$requests= $requests>99?99:(int)$requests;
			$req_timeout= $req_timeout>999?999:(int)$req_timeout;
			
			$db->setQuery('INSERT INTO #__bruteforce_blocker VALUES ('.$db->Quote($name).', '.$db->Quote($_SERVER['REMOTE_ADDR']).', '.time().', '.($requests>0?1:0).', '.$timeout.', '.$req_timeout.', '.$requests.')');
			$db->query();
		}
		
	}
	
	/**
	 * Selects a row from bruteforce blocker table.
	 * 
	 * @access	private
	 * @param	string	$name	Timer name
	 * @return	object
	 */
	private static function _getBruteForceRow($name){
		if(!isset(self::$bruteForceRows[$name])){
			$db = ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__bruteforce_blocker WHERE `name`='.$db->Quote($name).' AND `ip`='.$db->Quote($_SERVER["REMOTE_ADDR"]));
			self::$bruteForceRows[$name] = $db->loadObject();
		}

		return self::$bruteForceRows[$name];
	}

}

if(!function_exists('array_replace')){
	/**
	 * A replacement for array_replace function, designed for PHP<5.3.0
	 * 
	 * @param	array	$main
	 * @param	array	$overlay
	 * @return	array 
	 */
	function array_replace($main, $overlay){
		foreach($overlay as $k=>$v){
			$main[$k] = $v;
		}
		return $main;
	}
}

?>
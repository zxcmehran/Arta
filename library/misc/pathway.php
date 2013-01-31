<?php 
/**
 * Pathway Manager (ArtaPathway)
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
 * ArtaPathway Class
 * Used to generate pathways.
 */
class ArtaPathway {
	
	/**
	 * Paths are set separately in this var.
	 * 
	 * @var	array
	 */
	var $data=array();
	
	/**
	 * Paths links var.
	 * 
	 * @var	array
	 */
	var $links=array();
	
	/**
	 * Adds a pathway at a level.
	 * 
	 * @param	string	$data	Pathway date
	 * @param	string	$link	Pathway link
	 * @param	mixed	$level	Level of pathway. If null passed, it will be last level.
	 * @return	bool
	 */
	function add($data, $link=null, $level=null){
		if(is_numeric($level)){
			$this->data[$level]=$data;
			if(strlen((string)$link)>0){
				$this->links[$level]=$link;
			}
		}else{
			$this->data[]=$data;
			if(strlen((string)$link)>0){
				$this->links[]=$link;
			}
		}
		return true;
	}

	/**
	 * Returns link of a level if exists. If no links found will return NULL.
	 * 
	 * @param	int	$level	level to return link
	 * @return	mixed	string on success and null on failure
	 */
	function getLink($level){
		return isset($this->links[$level]) ? $this->links[$level] : null;
	}

	/**
	 * Returns result array.
	 * 
	 * @return	array
	 */
	function getResult(){
		return $this->data;
	}

	/**
	 * Returns Result imploded by custom path separator
	 * 
	 * @param	string	$by	Path Separator
	 * @param	bool	$escaped	Escape Path Separator in path values or not
	 * @return	string
	 */
	function getImplodedResult($by='/', $escaped=false){
		$s=array();
		foreach($this->data as $k=>$v){
			if($escaped == true){$s[$k]=addcslashes($v, $by);}
			else{$s[$k]=$v;}
		}
		return implode($by, $s);
	}

	/**
	 * Returns specified level of pathway
	 * 
	 * @param	int	$level	Path level to return
	 * @return	mixed	false on invalid level and string on success.
	 */
	function getLevel($level){
		$r=$this->data;
		if(array_key_exists($level, $r)){
			return $r[$level];
		}else{
			return false;
		}
	}

}
?>
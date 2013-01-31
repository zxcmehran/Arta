<?php
/**
 * This file defines ArtaTempStream stream definition class to make artatmp:// available.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(!defined('ARTA_VALID')){die('No access');}


/**
 * Temporary stream to store runtime cache or temp data.
 * It will not be saved in anywhere but runtime memory.
 * Example:
 * 
 * <?php
 * $file="Hello, Wolrd!";
 * file_put_contents('artatmp://mydata', $file);
 * 
 * echo file_get_contents('artatmp://mydata'); // Will output: Hello,World!
 * ?>
 * 
 */

class ArtaTempStream {
    var $position;
    var $varname;

    function stream_open($path, $mode, $options, &$opened_path) 
    {
		if(!isset($GLOBALS['STREAM_CACHE'])){
			$GLOBALS['STREAM_CACHE']=array();
		}
		$url = parse_url($path);
        $this->varname = $url["host"];
        $this->position = 0;
        return true;
    }

    function stream_read($count) 
    {
		@$fdata = &$GLOBALS['STREAM_CACHE'][$this->varname];
        $ret = substr($fdata, $this->position, $count);
        $this->position += strlen($ret);
        return $ret;
    }

    function stream_write($data) 
    {
		@$fdata = &$GLOBALS['STREAM_CACHE'][$this->varname];
        $left = substr($fdata, 0, $this->position);
        $right = substr($fdata, $this->position + strlen($data));
        $fdata = $left . $data . $right;
        $this->position += strlen($data);
        return strlen($data);
    }

    function stream_tell() 
    {
        return $this->position;
    }

    function stream_eof() 
    {
		@$fdata = &$GLOBALS['STREAM_CACHE'][$this->varname];
        return ($this->position >= strlen($fdata));
    }

    function stream_seek($offset, $whence) 
    {
		@$fdata = &$GLOBALS['STREAM_CACHE'][$this->varname];
        switch ($whence) {
            case SEEK_SET:
                if ($offset < strlen($fdata) && $offset >= 0) {
                     $this->position = $offset;
                     return true;
                } else {
                     return false;
                }
                break;
                
            case SEEK_CUR:
                if ($offset >= 0) {
                     $this->position += $offset;
                     return true;
                } else {
                     return false;
                }
                break;
                
            case SEEK_END:
                if (strlen($fdata) + $offset >= 0) {
                     $this->position = strlen($fdata) + $offset;
                     return true;
                } else {
                     return false;
                }
                break;
                
            default:
                return false;
        }
    }

	function stream_stat(){
		return array();
	}

	function stream_flush(){
		return true;
	}

	function unlink($path){
		$path=parse_url($path);
		$path=$path['host'];
		if(isset($GLOBALS['STREAM_CACHE'][$path])){
			unset ($GLOBALS['STREAM_CACHE'][$path]);
		}
		return true;
	}

	function rename($from, $to){
		$from=parse_url($from);
		$from=$from['host'];
		$to=parse_url($to);
		$to=$to['host'];
		if(isset($GLOBALS['STREAM_CACHE'][$from])){
			$data=$GLOBALS['STREAM_CACHE'][$from];
			unset($GLOBALS['STREAM_CACHE'][$from]);
			$GLOBALS['STREAM_CACHE'][$to]=$data;
		}
		return true;
	}
        
        function url_stat($path, $flags){
            $from=parse_url($path);
            $from=$from['host'];
            if(isset($GLOBALS['STREAM_CACHE'][$from])){
                return array('size'=>strlen($GLOBALS['STREAM_CACHE'][$from]));
            }
            return false;
        }
}

stream_wrapper_register("artatmp", "ArtaTempStream")
    or die("Failed to register protocol");
    
?>
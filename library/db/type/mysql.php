<?php 
/**
 * ArtaDB MySQL driver class
 * This class connects to DB using MySQL DB type. Note: this class loads with extending DB classes.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaDB
 * @version		$Revision: 2 2013/07/05 19:19 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaDB_mysql Class
 * This class connects to DB using MySQL DB type.
 * Note: this class loads by extending of DB type classes.
 */
class ArtaDB_mysql extends ArtaDB {

	/**
	 * Constructor
	 * at first check extension then connect then select DB
	 *
	 * @param	string	$server	server address
	 * @param	string	$user	username
	 * @param	string	$pass	password
	 * @param	string	$db	DB name
	 */
	function __construct($server, $user, $pass, $db){
		parent::__construct();
		if(!$this->test()){
			die('MySQL is not supported by this server.');
		}
		if(!$this->connection){
			if(!($this->connection = @mysql_connect($server, $user, $pass))){
				die('Could not connect to MySQL.Invalid server/username/password.');
			}
		}
		if(!$this->selectDB($db)){
			die('No database specified!');
		}
		
		$this->setMYSQL40();
		$this->setUTF8();
		
	}
	
	/**
	 * Destructor for disconnecting DB connection.
	 */
	function __destruct(){
		$this->disconnect();
	}

	/**
	 * Selects DB for {@link	ArtaDB::$connection}
	 * 
	 * @param	string	$database	server address
	 * @access	private
	 * @return	bool
	 */
	private function selectDB($database){
		if(!$database){
			return false;
		}
		if(!mysql_select_db($database, $this->connection)){
			die('Could not select database');
		}
		return true;
	}

	/**
	 * Returns MySQL Version
	 *
	 * @return string	 mysql_version
	 */
	function getVersion(){
		return mysql_get_server_info($this->connection);
	}

	/**
	 * Closes Connection
	 *
	 * @return mixed
	 */
	function disconnect(){
		$return = false;
		if(is_resource($this->connection)){
			$return = @mysql_close($this->connection);
		}
		return $return;
	}

	/**
	 * Tests if MySQL extension available
	 *
	 * @return bool
	 */
	function test(){
		return (function_exists('mysql_connect'));
	}

	/**
	 * Tests if MySQL connected
	 *
	 * @return bool
	 */
	function isConnected(){
		if(is_resource($this->connection)){
			return mysql_ping($this->connection);
		}
		return false;
	}

	/**
	 * Sets Connection character set to UTF8
	 */
	function setUTF8(){
		if($this->hasUTF8()){
			mysql_query("SET NAMES 'utf8'", $this->connection);
			mysql_query("SET CHARACTER SET 'utf8'", $this->connection);	
		}
	}

	/**
	 * Sets sql_mode to MYSQL40
	 * if running mysql 5, set sql-mode to mysql40 - thereby circumventing strict mode problems
	 */
	function setMYSQL40(){
		if(strpos($this->getVersion(), '5') === 0){
			mysql_query("SET sql_mode = 'MYSQL40'", $this->connection);
		}
	}
	
	/**
	 * Escapes strings for SQLs
	 *
	 * @param	string	$text	string to be escaped
	 * @param	bool	$dos	Escape for DoS?
	 * @return	string	escaped string
	 */
	function getEscaped($text, $dos=false){
		$result = mysql_real_escape_string($text, $this->connection);
		if($dos){ $result=$this->getDOSEscaped($result); }
		return $result;
	}
	
	/**
	 * Sends SQL to server
	 *
	 * @return	object	cursor
	 */
	function query(){		
		$this->errorNum = 0;
		$this->errorMsg = false;
		$this->cursor = mysql_query($this->SQL, $this->connection);
		if($this->cursor==false){
			$this->errorNum = mysql_errno($this->connection);
			$this->errorMsg = mysql_error($this->connection);
			$this->errOccured();
			return false;
		}
		$this->cleanCache();
		return $this->cursor;
	}

	/**
	 * Gets affected rows
	 *
	 * @return	int	affected rows
	 */
	function getAffectedRows(){
		return mysql_affected_rows($this->connection);
	}

	/**
	 * Returns first column of first row
	 *
	 * @return	string
	 */
	function loadResult(){
		if(($c = $this->query())==false){
			return null;
		}
		$ret = null;
		if($row = mysql_fetch_row($c)){
			$ret = $row[0];
		}
		mysql_free_result($c);
		return $ret;
	}

	/**
	 * Returns array of rows ONLY fist column
	 *
	 * @return	array
	 */	
	function loadResultArray($numinarray = 0){
		if(($c = $this->query())==false){
			return null;
		}
		$array = array();
		while($row = mysql_fetch_row($c)){
			$array[] = $row[$numinarray];
		}
		mysql_free_result($c);
		return $array;
	}

	/**
	 * Returns array of first row
	 *
	 * @return	array
	 */	
	function loadAssoc(){
		if(($cur = $this->query())==false){
			return null;
		}
		$ret = null;
		if($array = mysql_fetch_assoc($cur)){
			$ret = $array;
		}
		mysql_free_result($cur);
		return $ret;
	}

	/**
	 * Returns array of rows that each row is an array
	 *
	 * @return	array	arrays in array
	 */		
	function loadAssocList($key=''){
		if(($cur = $this->query())==false){
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_assoc($cur)){
			if($key){
				$array[$row[$key]] = $row;
			}else{
				$array[] = $row;
			}
		}
		mysql_free_result($cur);
		return $array;
	}

	/**
	 * Returns object of first row
	 *
	 * @return	object
	 */			
	function loadObject(){
		if(($cur = $this->query())==false){
			return null;
		}
		$ret = null;
		if($object = mysql_fetch_object($cur)){
			$ret = $object;
		}
		mysql_free_result($cur);
		return $ret;
	}

	/**
	 * Returns array of rows that each row is an object
	 *
	 * @return	array	objects in array
	 */
	 function loadObjectList($key=''){
		if(($cur = $this->query())==false){
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_object($cur)){
			if($key){
				$array[$row->$key] = $row;
			}else{
				$array[] = $row;
			}
		}
		mysql_free_result($cur);
		return $array;
	}

	/**
	 * Returns array of first row that keys are numerical NOT name of columns
	 *
	 * @return	array
	 */		
	function loadRow(){
		if(($cur = $this->query())==false){
			return null;
		}
		$ret = null;
		if($row = mysql_fetch_row($cur)){
			$ret = $row;
		}
		mysql_free_result($cur);
		return $ret;
	}

	/**
	 * Returns array of rows that each row is an array that its keys are numerical NOT name of columns
	 *
	 * @return	array	arrays in array
	 */
	function loadRowList($key=null){
		if(($cur = $this->query())==false){
			return null;
		}
		$array = array();
		while ($row = mysql_fetch_row($cur)){
			if($key !== null){
				$array[$row[$key]] = $row;
			}else{
				$array[] = $row;
			}
		}
		mysql_free_result($cur);
		return $array;
	}
	
	/**
	 * Returns currently inserted ID.
	 * @return	int
	 */
	function getInsertedID(){
		return mysql_insert_id($this->connection);
	}
	
}

?>
<?php 
/**
 * ArtaDB MySQLi driver class
 * This class connects to DB using MySQLi DB type. Note: this class loads with extending DB classes.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaDB
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaDB_mysqli Class
 * This class connects to DB MySQLi DB type.
 * Note: this class loads by extending of DB type classes.
 */
class ArtaDB_mysqli extends ArtaDB{

	/**
	 *	Constructor
	 * at first check extension then connect then select DB
	 *
	 * @param	string	$server	server address
	 * @param	string	$user	username
	 * @param	string	$pass	password
	 * @param	string	$db	DB name
	 */
	function __construct($server, $user, $pass, $db){
		if(!$this->test()){
			die('MySQLi is not supported by this server.');
		}
		if(!$this->connection){
			if(!($this->connection = @mysqli_connect($server, $user, $pass))){
				die('Could not connect to MySQLi.Invalid server/username/password.');
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
		if(!$this->connection->select_db($database)){
			die('Could not select database');
		}
		return true;
	}

	/**
	 * Returns MySQLi Version
	 *
	 * @return string	 mysqli_version
	 */
	function getVersion(){
		return $this->connection->get_server_info();
	}

	/**
	 * Close Connection
	 *
	 * @return mixed
	 */
	function disconnect(){
		$return = false;
	//	if(!is_resource($this->connection)){ because of an unknown reason, link is not testable with is_resource
			$return = @mysqli_close($this->connection);
	//	}
		return $return;
	}

	/**
	 * Tests if MySQLi extension available
	 *
	 * @return bool
	 */
	function test(){
		return function_exists('mysqli_connect');
	}

	/**
	 * Tests if MySQLi connected
	 *
	 * @return bool
	 */
	function isConnected(){
		if(is_resource($this->connection)){
			return $this->connection->ping();
		}
		return false;
	}

	/**
	 * Sets Connection character set to UTF8
	 */
	function setUTF8(){
		if($this->hasUTF8()){
			$this->connection->query("SET NAMES 'utf8'");
			$this->connection->query("SET CHARACTER SET 'utf8'");	
		}
	}

	/**
	 * Sets sql_mode to MYSQL40
	 * if running mysql 5, set sql-mode to mysql40 - thereby circumventing strict mode problems
	 */
	function setMYSQL40(){
		if(strpos($this->getVersion(), '5') === 0){
			$this->connection->query("SET sql_mode = 'MYSQL40'");
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
		$result = $this->connection->real_escape_string($text);
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
		$this->cursor = $this->connection->query($this->SQL);
		if($this->cursor==false){
			$this->errorNum = $this->connection->errno;
			$this->errorMsg = $this->connection->error;
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
		return $this->connection->affected_rows;
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
		if($row = $c->fetch_row()){
			$ret = $row[0];
		}
		$c->free();
		return $ret;
	}

	/**
	 * Returns array of rows ONLY fist column
	 *
	 * @return	array
	 */	
	function loadResultArray($numinarray = 0){
		if(($cur = $this->query())==false){
			return null;
		}
		$array = array();
		while ($row = $cur->fetch_row()){
			$array[] = $row[$numinarray];
		}
		$cur->free();
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
		if($array = $cur->fetch_assoc()){
			$ret = $array;
		}
		$cur->free();
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
		while ($row = $cur->fetch_assoc()){
			if($key){
				$array[$row[$key]] = $row;
			}else{
				$array[] = $row;
			}
		}
		$cur->free();
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
		if($object = $cur->fetch_object()){
			$ret = $object;
		}
		$cur->free();
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
		while ($row = $cur->fetch_object()){
			if($key){
				$array[$row->$key] = $row;
			}else{
				$array[] = $row;
			}
		}
		$cur->free();
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
		if($row = $cur->fetch_row()){
			$ret = $row;
		}
		$cur->free();
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
		while ($row = $cur->fetch_row()){
			if($key !== null){
				$array[$row[$key]] = $row;
			}else{
				$array[] = $row;
			}
		}
		$cur->free();
		return $array;
	}
	
	/**
	 * Returns currently inserted ID.
	 * @return	int
	 */
	function getInsertedID(){
		return $this->connection->insert_id;
	}

}

?>
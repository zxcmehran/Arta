<?php 
/**
 * Arta XMLRPC Client and Server.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// detect arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaXMLRPC Class
 * Makes using PHP-XMLRPC easy!
 */

class ArtaXMLRPC{
	
	/**
	 * Connection for Client
	 * @var	object
	 */
	var $connection;
	
	/**
	 * Result of asking server
	 * @var	object
	 */
	var $result;
	
	/**
	 * Server Instance
	 * @var	object
	 */
	var $server;
	
	/**
	 * Function map of Server
	 * @var	array
	 */
	var $funcmap;
	
	/**
	 * Error reporting last value before starting object
	 * @var	int
	 */
	var $err = null;

	/**
	 * Constructor.
	 */
	function __construct(){
		$this->err = (int)@error_reporting(E_ALL & ~E_STRICT & ~E_DEPRECATED);
		require_once(ARTAPATH_LIBRARY.'/xmlrpc/xmlrpc/lib/xmlrpc.inc');
		require_once(ARTAPATH_LIBRARY.'/xmlrpc/xmlrpc/lib/xmlrpc_wrappers.inc');
		$debug=ArtaLoader::Debug();
		$debug->report('ArtaXMLRPC started.', 'ArtaXMLRPC::__construct');
	}
	
	/**
	 * Destructor. Restores error reporting value.
	 */
	function __destruct() {
		if($this->err !==null) error_reporting($this->err);
	}

	/**
	 * Connect to a Server as a Client
	 * @param	string	$path	Path to server e.g. /server/server.php
	 * @param	string	$server	Server Host e.g. 221.25.83.11
	 * @param	string	$port	Host Port
	 * @param	string	$transport	Transport type. http, http11 or https.
	 */
	function Connect($path="", $server='', $port='', $transport=""){
		$this->connection=new xmlrpc_client($path, $server, $port, $transport);	
		$GLOBALS['xmlrpc_defencoding'] = "UTF8";
		$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';
		$GLOBALS['xmlrpcVersion'] .= ' on Arta Content Management Framework';
		$this->connection->request_charset_encoding='UTF-8';
		$this->connection->setAcceptedCompression('gzip');
		return $this->connection;
	}

	/**
	 * Sends Request to server
	 * @param	string	$function	Function to call
	 * @param	array	$params	Function Params
	 * @return	bool
	 */
	function sendRequest($function, $params=array()){
		
		foreach($params as $k=>$v){
			$params[$k]=php_xmlrpc_encode($v);
		}
		$f=new xmlrpcmsg($function, $params);
		$r=$this->connection->send($f);
		$this->result=$r;
		if($r->errno || $r->errstr){
			return false;
		}else{
			return true;
		}
	}

	/** 
	 * Gets Result 
	 * @return	mixed
	 */
	function getResult(){
		$value=$this->result->value();
		return $value->getval();
	}
	
	/**
	 * Gets Error String
	 * @return	string 
	 */
	function getErrorStr(){
		return $this->result->faultString();
	}

	/**
	 * Gets Error number
	 * @return	int 
	 */
	function getErrorNum(){
		return $this->result->faultCode();
	}
###########################################
#####
#####	SERVER SIDE
#####
###########################################

	/**
	 * Creates a server
	 */
	function createServer(){
		require_once(ARTAPATH_LIBRARY.'/xmlrpc/xmlrpc/lib/xmlrpcs.inc');
		$s=new xmlrpc_server($this->funcmap, false);
		$GLOBALS['xmlrpc_defencoding'] = "UTF8";
		$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';
		$s->compress_response = true;
		if (isset($_GET['RESPONSE_ENCODING'])){
			$s->response_charset_encoding = $_GET['RESPONSE_ENCODING'];
		}else{
			$s->response_charset_encoding = 'UTF-8';
		}
		$this->server=$s;		
		return $s;
	}
	
	/**
	 * Starts Listening
	 * @return	bool
	 */
	function Listen(){
		return $this->server->service();
	}

	/**
	 * Maps a function
	 * @param	string	$s_function	Function that you are going to add to server.
	 * @param	string	$function	PHP Function to Assign 
	 * @param	string	$return_type	Function return type
	 * @param	string	$params_type	Array of Function parameters type ex. array('int', 'string', 'bool')
	 * @param	string	$doc	Function Documentation
	 * @param	string	$s_doc	Function Parameters Documentation
	 * @return	bool
	 */
	function mapFunction($s_function, $function, $return_type, $params_type, $doc='', $s_docs=array()){
		if(!is_array($params_type) || !is_array($s_docs)){return false;}
		$sign=array_merge(array($return_type), $params_type);
		foreach($sign as $k=>$v){
			$v=ucfirst($v);
			if($v=='Datetime')				{$v='DateTime';}
			if($v=='Integer')				{$v='Int';}
			if($v=='Bool')					{$v='Boolean';}
			if($v=='Float'||$v=='Real')		{$v='Double';}
			if($v=='Str'||$v=='Char')		{$v='String';}
			eval('$sign[$k]= $GLOBALS["xmlrpc'.ucfirst($v).'"];');
		}

		$this->funcmap[$s_function]=array(
			"function" => $function,
			"signature" => array($sign),
			"docstring" => $doc,
			"signature_docs" => $s_docs
		);
		return true;
	}


}
?>
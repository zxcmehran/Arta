<?php 
/**
 * Web Service class.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// arta?
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaWebService Class. Creates XML-RPC Servers and processes requests.
 */
class ArtaWebService {
	
	/**
	 * XMLRPC Class Instance
	 * 
	 * @var	object
	 * @access	private
	 */
	private $xmlrpc;
	
	/**
	 * Constructor. Reports Debug then Creates an instance of XMLRPC server object
	 */
	function __construct(){
		$debug=ArtaLoader::Debug();
		$debug->report('ArtaWebService started.', 'ArtaWebService::__construct');
		$this->xmlrpc=ArtaLoader::XMLRPC();
	}

	/**
	 * Loads Web Services from database.
	 * 
	 * @return	array
	 */
	function loadDB(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__webservices WHERE `enabled`= '.$db->Quote('1'));
		$res=$db->loadObjectList();
		return $res;
	}

	/**
	 * Loads Services files. 
	 * 
	 * @return	bool
	 */
	function loadServices(){
        $p=ArtaLoader::Plugin();
        $p->trigger('onLoadWebservices');
		$d=$this->loadDB();
		if($d!==null){
			foreach($d as $k=>$v){
				$p=ARTAPATH_BASEDIR.'/webservices/'.ArtaFilterInput::safeAddress($v->webservice).'.php';
				
				if(file_exists($p)){
					//include($p);
					$this->includeFile($p);
				}else{	
					$debug=ArtaLoader::Debug();
					$debug->report('Webservice '.htmlspecialchars($v->webservice).' not found.', 'ArtaWebService::loadServices');
				}
			}
		}
	}
	
	/**
	 * Processes files in separated environment
	 */
	function includeFile(){
		include func_get_arg(0);
	}

	/**
	 * Maps a function to server.
	 * 
	 * @param	string	$s_function	Function name to add to server
	 * @param	string	$function	PHP Function to map as Server Function
	 * @param	string	$return_type	Function Return datatype
	 * @param	array	$params_type	Array of function parameters datatype
	 * @param	string	$doc	Function documentation
	 * @param	array	$s_doc	Function parameters documentation
	 * @return	bool
	 */
	function mapFunction($s_function, $function, $return_type, $params_type, $doc='', $s_doc=array()){
		return $this->xmlrpc->mapFunction($s_function, $function, $return_type, $params_type, $doc, $s_doc);
	}

	/**
	 * Starts Service
	 * 
	 * @return	bool
	 */
	function Start(){
		// for logging requests
		//file_put_contents(ARTAPATH_BASEDIR.'/xmlrpc_requests.txt', "\n\n".file_get_contents('php://input'), FILE_APPEND);
		$this->xmlrpc->createServer();
		
		$r= $this->xmlrpc->Listen();
		
		return $r;
	}



}
?>
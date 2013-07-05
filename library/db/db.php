<?php 
/**
 * Database base class. 
 * To create an instance you must use it's extenders.({@link	ArtaDB_mysql} and {@link	ArtaDB_mysqli})
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 2 2013/07/05 17:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaDB Class
 * This class connects to DB using selected DB type.
 * Note: this class loads by extending of DB type classes.
 */

class ArtaDB {

	/**
	 * Connection resource
	 *
	 * @var	object
	 * @access	protected
	 */
	protected $connection;

	/**
	 * Processed SQL (set via {@link	ArtaDB::setQuery()})
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $SQL;
	
	/**
	 * Raw SQL
	 * Table prefixes are not set yet.
	 *
	 * @var	string
	 * @access	protected
	 */
	protected $_SQL;
	
	/**
	 * Includes cache items which should not be cleaned during current SQL.
	 *
	 * @var	mixed
	 * @access	protected
	 */
	protected $noclean=false;
	
	/**
	 * Includes changed fields during execution of the SQL.
	 *
	 * @var	mixed
	 * @access	protected
	 */
	protected $changed_fields=false;

	/**
	 * Prefix of tables
	 *
	 * @var	string
	 * @access	private
	 */
	private $prefix='arta_';

	/**
	 * String to be replaced by prefix
	 *
	 * @var	string
	 * @access	private
	 */
	private $prefixReplacement='#__';
	
	/**
	 * Indicates that prefix is set or not (for prevent rechanging the prefix)
	 *
	 * @var	bool
	 * @access	private
	 */
	private $prefix_set=false;

	/**
	 * Error String
	 *
	 * @var	string
	 */
	var $errorMsg = false;

	/**
	 * Error Number
	 *
	 * @var	int
	 */
	var $errorNum = 0;
	
	/**
	 * Indicates that script must die on errors or not.
	 *
	 * @var	int
	 */
	var $die = false;
	
	/**
	 * Holds Cache cleaning events.
	 *
	 * @var	array
	 */
	var $cache_cleaning = array();
	
	/**
	 * Contains all the SQLs have executed.
	 * 
	 * @var	array
	 * @access	private
	 */
	private $SQLs = array();

	/**
	 * Used as Package processing start flag in debug output.
	 */
	const PACKAGE_START_FLAG = '**** Package Processing started. ****';
	
	/**
	 * Used as Package processing finish flag in debug output.
	 */
	const PACKAGE_FINISH_FLAG = '**** Package Processing finished. ****';
	
	/**
	 * Contstructor
	 */
	function __construct() {
		register_shutdown_function(array($this, '_debug'));
	}
	
	/**
	 * Set query string
	 * Validates SQL for prevent injections then Replaces prefix then Sets $this->SQL and then adds SQL to debug array for {@link	ArtaDebug}
	 * Specify fields changed during your query to $changed_fields. It will help Arta to clean caches contain this fields.
	 * Specify cache items which should not be cleared during execution on this SQL . e.g. array("blog,sef_aliases", "blog,new_tags")
	 * 
	 * @uses	ArtaDB::replacePrefix()		to replace table prefix
	 * @param	string	$what	SQL Query
	 * @param	mixed	$changed_fields
	 * @param	mixed	$noclean
	 */
	function setQuery($what, $changed_fields=true, $noclean=false){
		$this->_SQL=$what;
		$this->changed_fields=$changed_fields;
		$this->noclean=$noclean;
		
		$this->SQL=$this->replacePrefix($what);
		$debug = ArtaLoader::Debug();
		if($debug->enabled){
			$this->SQLs[] = $this->SQL;
		}
	}

	/**
	 * Replaces prefix in SQL (replaces {@link	ArtaDB::$prefixReplacement} with {@link	ArtaDB::$prefix})
	 *
	 * @param	string	$str	SQL to process
	 * @return	string	processed SQL
	 */
	function replacePrefix($str){
		/*$i=0;
		$inside=false;
		$last=0;
		$res='';
		while($i<strlen($str)){
			if($str{$i}=='\'' || $str{$i}=='"' || $i==strlen($str)-1){
				$sub=substr($str, $last, $i-$last+1);var_dump($sub);
				$res.=$inside==false?str_replace($this->prefixReplacement, $this->prefix, $sub):$sub;
				$inside=$inside?false:true;
				$last=$i+1;
			}
			$i++;
		}
		return $res;
		// str'sd'wer*/
		return str_replace($this->prefixReplacement, $this->prefix, $str);
	}

	/**
	 * Returns processed SQL Query, e.g. query with replaced prefix.
	 *
	 * @return	string	SQL
	 */
	function getQuery(){
		return $this->SQL;
	}
	
	/**
	 * Returns raw SQL Query
	 *
	 * @return	string	Raw SQL
	 */
	function getRawQuery(){
		return $this->_SQL;
	}

	/**
	 * Sets table prefix. You must use it because {@link	ArtaDB::$prefix} is private.
	 * Note: You can only set it once.
	 *
	 * @param string	 $p	string to set as prefix
	 * @return	bool	false on reset and true on first setting.
	 */
	function setPrefix($p){
		if($this->prefix_set==false){
			$this->prefix_set=true;
			$this->prefix=$p;
			$this->prepareCacheCleaner();
			return true;
		}else{
			return false;
		}
	}

	/**
	 * Get error if available
	 *
	 * @return	string	false on "no error" else error msg.
	 */
	 function getError(){
		if($this->errorNum !== 0){
			return 'Error '.$this->errorNum.' : '.$this->errorMsg.' (SQL: "'.$this->SQL.'")';
		}else{
			return false;
		}
	}
	
	/**
	 * Just designed for do something on errors.
	 */
	function errOccured(){
		if($this->die==true){
			ArtaError::show(500, 'DB Error: '.$this->errorNum.' : '.$this->errorMsg.' (SQL : "'.$this->SQL.'")');
		}
	}
 	
 	/**
 	 * This function validates SQL by counting it's quotes.
 	 * It's not used anywhere yet. Maybe used or deprecated later. 
 	 * Extension developers can use it.
  	 * 
	 * @param	string	$sql	SQL to vaidate
	 * @return	bool	Validation result   
 	 */
	function validateSQL($sql){
		$c=0;
		$num_quote=0;
		$num_nquote=0;
		$in_quote=false;
		$in_nquote=false;
		while($c<strlen($sql)){
			switch($sql{$c}){
				case '"':
				case "'":
					@$cs=$c-1;
					$num_slashes=0;
					while(@$sql{$cs}=='\\'){
						$num_slashes++;
						$cs--;
					}
					if(@is_int($num_slashes/2)){
						$escaped=true;
					}else{
						$escaped=false;
					}
					if($in_nquote==false && $escaped){
						$num_quote++;
						if($in_quote ==false){
							$in_quote=true;
						}else{
							$in_quote=false;
						}
					}
				break;
				case "`":
					if($in_quote==false){
						$num_nquote++;
						if($in_nquote == false){
							$in_nquote=true;
						}else{
							$in_nquote=false;
						}
					}
			}
			$c++;
		}
		
		if(is_float((@$num_nquote / 2))){
			return false;	
		}
		if(is_float((@$num_quote / 2))){
			return false;	
		}
		return true;
	}
	
	/**
	 * Cleans cached items which are dependent to currently updating table.
	 * e.g. cleans caches gathered from #__users and could be expired when you execute "UPDATE #__users SET username='alex' WHERE id=9"
	 * @access	protected
	 */
	protected function cleanCache(){
		if($this->noclean===true || $this->changed_fields===false){
			return;
		}
		$c=ArtaLoader::Config();
		if($c->cache==false){
			return;
		}
		$sql = $this->SQL;
		$nc = $this->noclean;
		$cf = $this->changed_fields;
		if(preg_match('@(^|\( *)(INSERT|UPDATE|DELETE|REPLACE|TRUNCATE).*@mi',$sql)){
			foreach($this->cache_cleaning as $k=>$v){
				if(stripos($sql, $k)!==false){
					foreach($v as $vv){
						if(@!in_array($vv[0].','.$vv[1], $nc) && $this->getAffectedRows()>0 && (
								$cf===true OR $vv[2]==null OR $vv[2]==array("") OR (@count($cf)>0 AND @count(array_intersect($vv[2],$cf))>0)
						)){
							ArtaCache::clearData($vv[0],$vv[1]);
						}
					}
				}
			}
		}		
	}
	
	/**
	 * Prepares cache cleaning events to be used by {@link ArtaDB::cleanCache()}
	 * @access	protected
	 */	
	protected function prepareCacheCleaner(){
		$c=ArtaLoader::Config();
		if($c->cache==true){
			$this->cache_cleaning=$this->getTableCacheCleaningEvents();
		}
		return;
	}
	
	/**
	 * Loads cache cleaning events from DB/Cache
	 * @access	private
	 */
	private function getTableCacheCleaningEvents(){
		if(ArtaCache::isUsable('db','cache_cleaning_tables')==false){
			$this->setQuery('SELECT * FROM #__cache_cleaning');
			$r=$this->loadObjectList();
			$res=array();
			foreach($r as $v){
				$res[$this->replacePrefix($v->table)][]=array_merge(explode(',', $v->cache_name), array(
						array_map('trim',explode(',',trim($v->cached_fields))) // array of cached_fields exploded by , and trimmed.
				));
			}
			ArtaCache::putData('db','cache_cleaning_tables',$res);
		}else{
			$res=ArtaCache::getData('db','cache_cleaning_tables');
		}
		return $res;
	}
	
	/**
	 * Spits Queries in SQL Sheets.
	 * @param	string	$queries	SQL Sheet contents
	 * @return	array
	 */
	function splitSQL($queries){
		$start = 0;
		$open = false;
		$open_char = '';
		$end = strlen($queries);
		$query_split = array();
		for($i=0;$i<$end;$i++) {
			$current = substr($queries,$i,1);
			if(($current == '"' || $current == '\'' || $current == '`')) {
				$n = 2;
				while(substr($queries,$i - $n + 1, 1) == '\\' && $n < $i) {
					$n ++;
				}
				if($n%2==0) {
					if ($open) {
						if($current == $open_char) {
							$open = false;
							$open_char = '';
						}
					} else {
						$open = true;
						$open_char = $current;
					}
				}
			} 
			if(($current == ';' && !$open)|| $i == $end - 1) {
				$query_split[] = substr($queries, $start, ($i - $start + 1));
				$start = $i + 1;
			}
		}

		return $query_split;
	}
	
	/**
	 *	Puts data in Quote
	 * at first escapes strings (if you like) then puts it in a pair of quotes
	 *
	 * @param	string	$string	string
	 * @param	bool	$escape	Escape or not?
	 * @return	string	escaped string in quotes
	 */
	function Quote($string, $escape=true){
		if($escape){
			return "'".$this->getEscaped($string)."'";
		}else{
				return "'".$string."'";
		}
	}
	
	/**
	 * Puts Table or column names in name Quote (`)
	 * at first escapes name then puts it in a pair of name quotes. For example:
	 * if($string=="tabl`e2"){
 	 * 	return "`tabl``e2`";
	 * }
	 *
	 * @param	string	$string	string
	 * @param	bool	$escape	Escape or not?
	 * @return	string	escaped string in name quotes
	 */
	function CQuote($string, $escape=true){
		if($escape){
			return "`".$this->getCEscaped($string)."`";
		}else{
			return "`".$string."`";
		}
	}
	
	/**
	 * Tests if MySQL have UTF8 (is upper than version 4.0.2)
	 *
	 * @return bool
	 */
	function hasUTF8(){
		$verParts = explode('.', $this->getVersion());
		return ($verParts[0] == 5 || ($verParts[0] == 4 && $verParts[1] == 1 && (int)$verParts[2] >= 2));
	}
	
	/**
	 * Escapes name strings e.g. table names and column names
	 *
	 * @param	string	$text	name string to be escaped
	 * @return	string	escaped name string
	 */
	function getCEscaped($text){
		$result = str_replace('`', '``', $text);
		return $result;
	}
	
	/**
	 * Escapes strings for SQLs (only DOS operative % and _ )
	 * Use it when you have LIKE.
	 *
	 * @param	string	$text	string to be escaped
	 * @return	string	escaped string
	 */
	function getDOSEscaped($text){
		$result = addcslashes($text, '_%');
		return $result;
	}
	
	
	/**
	 * Returns array of tables
	 *
	 * @return	array
	 */
	function getTableList(){
		$this->setQuery('SHOW TABLES');
		return $this->loadResultArray();
	}

	/**
	 * Returns tables information
	 * @param	array	$tables	 tables to be described
	 * @param	bool	$typeonly	if true returns array with column name in key and type in value else returns array with column name in key and object of properties in value
	 * @return	mixed	objects in array OR array. look @param $typeonly
	 */
	function getTableFields($tables, $typeonly = true){
		settype($tables, 'array'); //force to array
		$result = array();

		foreach ($tables as $tblval){
			$this->setQuery('SHOW FIELDS FROM ' . $tblval);
			$fields = $this->loadObjectList();

			if($typeonly){
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = preg_replace("/[(0-9)]/",'', $field->Type);
				}
			}
			else
			{
				foreach ($fields as $field) {
					$result[$tblval][$field->Field] = $field;
				}
			}
		}

		return $result;
	}
	
	/**
	 * Used to set a flag in debug output to indicate that the package getting processed right now or not.
	 * 
	 * @param	bool	$started	true on package processing start and false on finish.
	 */
	function setInPackageFlag($started){
		if($started)
			$this->SQLs[] = self::PACKAGE_START_FLAG;
		else
			$this->SQLs[] = self::PACKAGE_FINISH_FLAG;
	}
	
	/**
	 * Adds a block to debug output about executed SQLs.
	 */
	function _debug(){
		$debug = ArtaLoader::Debug();
		if(!$debug->enabled) return;
		
		$c = count((array)$this->SQLs)-2;
		$c = $c < 0 ? 0 : $c;
		$res = <<<_HTML_
<style>
	.debug_queries li{
		color:#555;
		border-bottom: 1px solid #ddd;
		padding: 2px;
	}
	.debug_queries li.debug_query_package {
		color: black;
		background: #f5f5f5;
	}
		
	.debug_queries div {
		font-weight: bold;
	}
</style>
_HTML_;
		$res .= "\n<b>Executed queries (".($c)."): </b>\n";
		$res .='<ol class="debug_queries">';
		$className = '';
		foreach($this->SQLs as $v){
			if($v==self::PACKAGE_START_FLAG) {
				$className = 'debug_query_package';
				$res .= '<div>'.$v.'</div>';
				continue;
			}elseif($v==self::PACKAGE_FINISH_FLAG){
				$className = '';
				$res .= '<div>'.$v.'</div>';
				continue;
			}
			
			$res .='<li'.($className?' class="'.$className.'"':'').'>'.nl2br(htmlspecialchars($v)).'</li>';
		}
		$res .='</ol>';
		$this->SQLs = array();
		$debug->addColumn($res, true);
	}
	

}

?>
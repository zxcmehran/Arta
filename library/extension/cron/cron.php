<?php 
/**
 * Cron engine for Arta. It runs tasks on specified times.
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
 * ArtaCron Class
 * Just executes tasks at specified times.
 */
class ArtaCron{

	/**
	 * Current time
	 *
	 *	@var	int
	 */
	var $current;

	/**
	 * Tasks loaded from DB
	 *
	 * @var	array
	 * @access	private
	 */
	private $tasks;

	/**
	 * Tasks to do
	 *
	 * @var	array
	 * @access	private
	 */
	private $todo=array();

	/**
	 * Current task id
	 *
	 * @var	int
	 */
	var $scope;

	/**
	 * Current task name
	 *
	 * @var	string
	 */
	var $scope_name;
	
	/**
	 * Runtime random
	 *
	 * @var	string
	 */
	var $rand;

	/**
	 * Settings cache variable that will be filled by getSetting()
	 *
	 * @var	array
	 * @access	private
	 */
	private $cache_setting;

	/**
	 * Constructor. Gathers data and cleans exipred logs.
	 */
	function __construct(){
		$debug=ArtaLoader::Debug();
		$debug->report('ArtaCron loaded successfully.', 'ArtaCron::__construct');
		
		$this->current=time();
		$this->getTasks();
		$this->getTodo();
        $this->rand = ArtaString::makeRandStr();
		$a=array();
		foreach($this->todo as $v){
			$a[]=$v->id;
		}
		if(count($a)){
			$this->setNextRun($a);
		}
		
		$this->cleanReports();
		$debug->report(count($this->tasks).' task and '.count($this->todo).' to do.', 'ArtaCron::__construct');
		$d='';
		if(count($this->todo) !==0){
			foreach($this->todo as $k=>$v){
				$d .="<br>\n".($k+1).'. '.htmlspecialchars($v->title).' ('.htmlspecialchars($v->cron).")";
			}
		}else{
			$d="&lt;nothing!&gt;";
		}
		$debug->addColumn("Executed crons (".count($this->todo).") : ".$d);
	}

	/**
	 * Loads tasks from DB
	 */
	function getTasks(){
		if(ArtaCache::isUsable('cron', 'tasks')){
			$this->tasks=(array)ArtaCache::getData('cron', 'tasks');
		}else{
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT * FROM #__crons WHERE `enabled`=1');
			$this->tasks=(array)$db->loadObjectList();
			if($this->tasks==null){
				$this->tasks=array();
			}
			ArtaCache::putData('cron', 'tasks', $this->tasks);
		}
	}

	/**
	 * Cleans expired log entries
	 * @param	int	$lifetime	Lifetime in seconds
	 * @param	bool	$randam	Use probability to decide what to do. If False always acts.
	 */
	function cleanReports($lifetime=null, $random=true){
		if($random==false || mt_rand(0,9)==5){
			if(!is_numeric($lifetime)){
				$c=ArtaLoader::Config();
				$lifetime=$c->cron_log_lifetime;
			}
			$lifetime=(int)$lifetime;
			$config=ArtaLoader::Config();
			$db=ArtaLoader::DB();
			$db->setQuery('DELETE FROM #__cronslog WHERE time < '.$db->Quote($this->current - $lifetime));
			$db->query();
		}
	}

	/**
	 * Selects those must run
	 */
	function getTodo(){
		$d=$this->tasks;
		foreach($d as $k=>$v){
			if($v->nextrun <= $this->current){
				$this->todo[]=$v;
			}
		}
	}

	/**
	 * Runs crons that they're in $todo array then sets next run time
	 * Note: Language files are loaded from ADMIN client.
	 */
	function Run(){
		$l=ArtaLoader::Language();
		$plug=ArtaLoader::Plugin();
		foreach($this->todo as $k=>$v){
			if(ArtaFile::read(ARTAPATH_BASEDIR.'/tmp/cron_lock_'.$v->id.'.lck')!=$this->rand)
					continue;
			$plug->trigger('onRunCron', array(&$v));
			$this->scope=$v->id;
			$this->scope_name=$v->cron;
			$l->addtoNeed($v->cron, 'cron', ARTAPATH_ADMIN);
			
			$this->ReportLog(sprintf(trans('TASK STARTED'), $v->title));
			$this->setLastRun($v);
			$this->includeFile(ARTAPATH_BASEDIR.'/crons/'.$v->cron.'.php');
			ArtaFile::delete(ARTAPATH_BASEDIR.'/tmp/cron_lock_'.$v->id.'.lck');
		}
	}

	/**
	 * Processes files in separated environment
	 */
	function includeFile(){
		include func_get_arg(0);
	}
	
	/**
	 * Reports logs
	 *
	 * @param	string	$text	texts to report
	 * @return	bool
	 */
	function ReportLog($text){
		$db=ArtaLoader::DB();
		$db->setQuery('INSERT INTO #__cronslog (cron, time, text) VALUES ('.$db->Quote($this->scope).', '.$db->Quote($this->current).', '.$db->Quote($text).')' );
		return $db->query();
	}

	/**
	 * Sets next run time
	 *
	 * @param	object	$v	row object
	 * @return	bool
	 */
	function setNextRun($v){
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__crons SET nextrun= IF(runloop=0,9999999999, '.$this->current.' + runloop) WHERE '.$this->current.'>=nextrun AND `id` IN('.implode(',',$v).')');
		if($db->query()){
			foreach ($v as $val) {
				if(!is_file(ARTAPATH_BASEDIR.'/tmp/cron_lock_'.$val.'.lck') OR ArtaFile::getModified(ARTAPATH_BASEDIR.'/tmp/cron_lock_'.$val.'.lck')< time()-900){
					ArtaFile::write(ARTAPATH_BASEDIR.'/tmp/cron_lock_'.$val.'.lck', $this->rand);
				}
			}
		}
	}

	/**
	 * Sets last run time
	 *
	 * @param	object	$v	row object
	 * @return	bool
	 */
	function setLastRun($v){
		$db=ArtaLoader::DB();
		$db->setQuery('UPDATE #__crons SET lastrun='.$db->Quote($this->current).' WHERE id='.$db->Quote($v->id), array('lastrun'));
		return $db->query();
	}

 	/**
	 * Gets settings from Database
	 *
	 * @param	string	$what	Variable to get value
	 * @param	string	$default	Default value to pass if no records were at database
	 * @param	string	$cron	cron name
	 * @param	string	$client	it's only "site" for crons.
	 * @return	mixed	$what value in database
	 */
	function getSetting($what, $default=null, $cron=null){
		$client='site';
		if($cron == null){
			$cron = $this->scope_name;
		}
		if(isset($this->cache_setting[$cron])){
			if(isset($this->cache_setting[$cron][$what])){
				$result = unserialize($this->cache_setting[$cron][$what]);
			}else{
				$result = $default;
			}
		}else{
			$res=ArtaCache::getData('cron_setting', $client.'_'.$cron);
			if(!$res){
				$db =ArtaLoader::DB();
				$query="SELECT * FROM #__settings WHERE extname=".$db->Quote($cron)." AND extype='cron' AND client= ".$db->Quote($client);
				$db->setQuery($query);
				$r = $db->loadAssocList();
				if($r == null){
					$r=array();
				}
				$res=array();
				foreach($r as $k=>$v){
					$res[$v['var']]=$v['value'];
				}
				ArtaCache::putData('cron_setting', $client.'_'.$cron, $res);
			}
			$this->cache_setting[$cron]=$res;
			if(!isset($res[$what])){
				$result = $default;
			}else{
				$result = unserialize($res[$what]);
			}
		}
		return $result;
	}
	
	/**
	 * Counts to-do tasks
	 */
	function todoCount(){
		return count($this->todo);
	} 
}
?>
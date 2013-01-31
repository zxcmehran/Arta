<?php
/**
 * Debug Engine; One of the application essentials.
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
 * ArtaDebug class
 * Debugging tools for analyzing Arta
 */
class ArtaDebug {
	
	/**
	 * Is debugging enabled?
	 * 
	 * @var	bool
	 */
	var $enabled = false;
	
	/**
	 * Output type. 'echo' or 'file'
	 * 
	 * @var	string
	 */
	var $mode = 'echo';
	
	/**
	 * Reports Array.
	 * 
	 * @var	array
	 */
	var $reports = array();
	
	/**
	 * Application Start time in microseconds
	 * 
	 * @var	float
	 */
	var $start = 0;
	
	/**
	 * Output Content
	 * 
	 * @var	string
	 */
	var $content=null;
	
	/**
	 * Used to calculate delta as first value.
	 * 
	 * @var	float
	 */
	var $t1=0;
	
	/**
	 * Used to calculate delta as second value.
	 * 
	 * @var	float
	 */
	var $t2=0;

	/**
	 * Constructor. Sets class vars.
	 */
	function __construct(){
		ArtaLoader::Import('misc->date');
		$this->start = $this->getMicrotime();
		$config	 =	 ArtaLoader::Config();
		$this->enabled=$config->debug;
		$this->mode=$config->debug_mode;
		$this->report('Debugging engine loaded.', 'ArtaDebug::__construct');
					register_shutdown_function(array($this, 'out'));
	}

	/**
	 * Adds a report.
	 * 
	 * @param	string	$what	Report body
	 * @param	string	$where	Function or Method name
	 * @param	bool	$bold	Make this report bold?
	 */
	function report($what, $where, $bold=false){
		if($what == null || $where == null){
			return false;
		}
		if($this->enabled == true){
			$this->reports[]= '- ['.$this->getPast().' secs past, &Delta;='.$this->getDelta().']'.($bold?'<b>':'').' &lt;'.htmlspecialchars($where).'&gt; : '.htmlspecialchars($what).($bold?'</b>':'');
		}
		
	}

	/**
	 * Adds a Column to Report.
	 * 
	 * @param	string	$data	Column body
	 * @return	bool
	 */
	function addColumn($data){
		$this->content .="<br/>\n<br/>\n".$data;
		return true;
	}


	/**
	 * Generates output
	 * 
	 * @return	string
	 */
	function show($is_echo=false){
		error_reporting(0);
		//first
		$r = '<div style="direction:ltr;background:#ffffff;padding-left:10px;">Begin Debug output : <br/>System Time: '.date('Y-m-d H:i:s', time())."<br/>\n";
		
		$c=ArtaLoader::Config();
		if($c->offline==false && $c->debug==true && $c->debug_mode=='echo' && $is_echo==true){
			echo '<div style="color:red;font-weight:bold;">WARNING: Debug system is On and your website isn\'t offline so it will show some secret information about your website to unauthorized people and your website\'s security will be in danger. It\'s recommended to choose one of the following to do: <br/>1.Turn off Debug System.<br/>2.Make your website offline.<br/>3.Change settings to put debug data in file; however, remote people can read log files.</div>';
		}
		
		// get reports
		foreach($this->reports as $k => $v){
			$r .= $v . '<br/>'."\n";
			unset($this->reports[$k]);
		}
		//get executed queries
		$res='<table>';
		$i=0;
		$color='#cccccc';
		$color2='#555555';
		$color3='#ffffff';
		foreach((array)$GLOBALS['DEBUG']['SQL'] as $v){
			if($v=='**** Package Processing started. ****'){
				$color3='#eeeeee';
				$color2='black';
				$color='#cccccc';
			}
			if(substr($v, 0, 4)=='****'){
				$res .= '<tr><td colspan="2"><b>'.htmlspecialchars($v).'</b></td></tr>'."\n\n";
			}else{
				$i++;
				$res .= '<tr><td>'.$i.'. </td><td style="padding-left:5px;border-bottom:1px solid '.$color.';color:'.$color2.';background:'.$color3.';">'.nl2br(htmlspecialchars($v)).'</td></tr>'."\n\n";
			}
			if($v=='**** Package Processing finished. ****'){
				$color3='#ffffff';
				$color2='#555555';
				$color='#cccccc';
			}
		}
		$res = "\n".'<br/>Executed queries ('.($i).') : <br/>'."\n".$res;
		$res.='</table>';

		$r .=$res;
		if(isset($GLOBALS['BT'])){
		$r .="\n<br/><b>Backtrace:</b><p>";
		$bt=$GLOBALS['BT'];

		$r.=nl2br($bt);
		$r .="\n</p>";
		}
		
		$res = '<br/>';
		if(!isset($GLOBALS['DEBUG']['LANGUAGE'])){
		$GLOBALS['DEBUG']['LANGUAGE'] = array();
		}
		foreach($GLOBALS['DEBUG']['LANGUAGE'] as $v){
			$res .=htmlspecialchars($v)."<br>\n";
		}
		$r .= "\n<br/>Loaded language files (".count($GLOBALS['DEBUG']['LANGUAGE']).") : ".$res;

		$res = '<br/>';
		if(!isset($GLOBALS['DEBUG']['_LANGUAGE'])){
		$GLOBALS['DEBUG']['_LANGUAGE'] = array();
		}
		foreach($GLOBALS['DEBUG']['_LANGUAGE'] as $v){
			$res .=htmlspecialchars($v)."<br>\n";
		}
		$r .= "\n<br/>Missing language files (".count($GLOBALS['DEBUG']['_LANGUAGE']).") : ".$res;

		//get Untranslated phrases
		$res = '<br/>';
		if(isset($GLOBALS['DEBUG']['UNTRANSED'])){
			$dddd=count($GLOBALS['DEBUG']['UNTRANSED']);
			foreach($GLOBALS['DEBUG']['UNTRANSED'] as $v){
				$res .= htmlspecialchars($v)."<br/>";
			}
		}else{
			$dddd=0;
			$res ="&lt;Nothing !&gt;<br/>";
		}
		$r .= "<br/>\nUntranslated phrases (".$dddd.") : ".$res."\n\n";

		//get Activated Sessions
		$s = ArtaSession::getSessions();
		$res = '<br/>';
		$i=0;
		foreach($s as $v){
			if(session_id() == $v->session_id){
				$sessid = '<b>'.htmlspecialchars($v->session_id).'</b>';
			}else{
				$sessid = htmlspecialchars($v->session_id);
			}
			$i++;
			$res .= $i.'. '.$sessid.' | '.$v->time.' | '.$v->userid."<br/>\n";
		}
		$r .= '<br/>Sessions (session_id | time | userid) : '.$res."Sum : ".count($s);
		$r .=$this->content;
		$r .= "<br/><br/>\n\nMemory Used during execution : ".sprintf('%0.2f', $this->getMemory() / 1048576 ) . ' MB<br/>'."\n";
		$r .= 'Time past during execution : '.$this->getPast().' Seconds'."\n";
		$r .='</div>';
		return $r;
	}

	/**
	 * Starts output to desired target
	 */
	function out(){
		if($this->enabled == true){
			switch($this->mode){
			case 'echo':
			default:
				echo $this->show(true);
			break;
			case 'file':
				$filename=ARTAPATH_BASEDIR.'/tmp/logs/debug_'.$this->getMicrotime().'.html';
				ArtaFile::write($filename, $this->show());
				ArtaFile::write(ARTAPATH_BASEDIR.'/tmp/logs/index.html', '');
			break;
			}
		}
	}

	/**
	 * Gets Application memory usage. 
	 */
	function getMemory(){
		static $isWin;

		if (function_exists( 'memory_get_usage' )) {
			return memory_get_usage();
		} elseif(function_exists('exec')) {
			// Determine if a windows server
			if (is_null( $isWin )) {
				$isWin = (substr(PHP_OS, 0, 3) == 'WIN');
			}

			// Initialize variables
			$output = array();
			$pid = getmypid();

			if ($isWin) {
				// Windows workaround
				@exec( 'tasklist /FI "PID eq ' . $pid . '" /FO LIST', $output );
				if (!isset($output[5])) {
					$output[5] = null;
				}
				return substr( $output[5], strpos( $output[5], ':' ) + 1 );
			} else {
				@exec("ps -o rss -p $pid", $output);
				return $output[1] *1024;
			}
		}else{
			return 0;
		}
	}

	/**
	 * Gets Current Microtime
	 * @return	float
	 */
	function getMicrotime(){
		list( $usec, $sec ) = explode( ' ', microtime() );
		return ((float)$usec + (float)$sec);
	}

	/**
	 * Gets elapsed time in microseconds
	 * 
	 * @return	float
	 */
	function getPast(){
		// Get time past from start of Arta
		$now = $this->getMicrotime();
		$past = ((float)$now - (float)$this->start);
		$past = round($past, 4);
		if($past==0){
			$past='0.0000';
		}
		while(strlen($past)<6){
			$past.='0';
		}
		$this->t2=$past;
		return $past;
	}
	
	/**
	 * Gets elapsed time between last report and now
	 * 
	 * @return	float
	 */
	function getDelta(){
		// Get time past from last report
		$past = round($this->t2 - $this->t1, 4);
		if($past==0){
			$past='0.0000';
		}
		while(strlen($past)<6){
			$past.='0';
		}
		$this->t1=$this->t2;
		return $past;
	}


}
?>
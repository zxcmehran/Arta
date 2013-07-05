<?php
/**
 * Debug Engine; One of the application essentials.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 3 2013/07/05 20:19 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaDebug class
 * Debugging tools for analyzing Arta execution
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
	 * @access	private
	 */
	private $reports = array();
	
	/**
	 * Application Start time in microseconds
	 * 
	 * @var	float
	 * @access	private
	 */
	private $start = 0;
	
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
	 * @access	private
	 */
	private $t1=0;
	
	/**
	 * Used to calculate delta as second value.
	 * 
	 * @var	float
	 * @access	private
	 */
	private $t2=0;

	/**
	 * Constructor.
	 */
	function __construct(){
		ArtaLoader::Import('misc->date');
		$this->start = $this->getMicrotime();
		$config	 =	 ArtaLoader::Config();
		$this->enabled=$config->debug;
		$this->mode=$config->debug_mode;
		$this->report('Debugging engine loaded.', 'ArtaDebug::__construct');
					register_shutdown_function(array($this, 'out'), false);
	}

	/**
	 * Adds a report.
	 * 
	 * @param	string	$what	Report body
	 * @param	string	$where	Function or Method name
	 * @param	bool	$bold	Make this report bold?
	 * @return	bool	true on success, false on invalid input or disabled mode.
	 */
	function report($what, $where, $bold=false){
		if($what != null && $where != null && $this->enabled == true){
			$this->reports[]= '<li>['.sprintf('%0.3f', $this->getPast()).' past, &Delta;='.sprintf('%0.3f', $this->getDelta()).']'.($bold?'<b>':'').' &lt;'.htmlspecialchars($where).'&gt; : '.htmlspecialchars($what).($bold?'</b>':'').'</li>';
			return true;
		}
		return false;		
	}

	/**
	 * Adds a Column to output.
	 * 
	 * @param	string	$data	Column body
	 * @param	bool	$prepend	Set true to add the column at the top of output content.
	 * @return	bool	true on success, false on invalid input or disabled mode.	
	 */
	function addColumn($data, $prepend=false){
		$data = trim($data);
		if($this->enabled == true && $data!=null){
			if($prepend)
				$this->content = "<br/>\n<br/>\n".$data.$this->content;
			else
				$this->content .= "<br/>\n<br/>\n".$data;
			return true;
		}
		return false;
	}


	/**
	 * Generates the output
	 * 
	 * @return	string|bool	False on disabled mode.
	 */
	function show(){
		if($this->enabled==false) return false;
		$r = <<<_HTML_
<style>
	.debug {
		direction: ltr;
		background: #fff;
		color: #000;
		padding: 10px;
		font-family: monospace;
		font-size: 12px;
		text-align: left;
	}
	.debug_systime {
		color: green;
		font-weight: bold;
	}
	.debug_reports {
		list-style: none;
		padding:0px;
	}
	.debug_reports li {
		padding:1px !important;
	}
	.debug_reports li b {
		color:#304099;
	}
	
	.debug * {
		line-height: 100% !important;
	}
		
	.debug_memory, .debug_timer, .debug_list_nothing {
		color: #304099;
		font-weight:bold;
	}
		
</style>
_HTML_;
		error_reporting(0);
		
		$c=ArtaLoader::Config();
		if($c->offline==false && $this->mode=='echo'){
			echo '<div style="color:red;font-weight:bold;">WARNING: Debug system is On and your website is not in Offline Mode. Thus, some secret informations about your website may be leaked and this might put your website\'s security in danger. You should consider doing one of the following: <br/>1. Turn off Debug System<br/>2. Make your website offline<br/>3. Change settings to put debug data in file; However, remote people can still read log files unless you restrict public access to <i>tmp/logs/</i> directory.</div>';
		}
		unset($c);
		
		//first
		$r .= '<div class="debug"><b>Begin Debug output:</b> <br/>System Time: <span class="debug_systime">'.date('Y-m-d H:i:s', time())."</span>\n";
		
		// get reports
		$r .= '<ul class="debug_reports">';
		foreach($this->reports as $k => $v){
			$r .= $v."\n";
			unset($this->reports[$k]);
		}
		$r .='</ul>';
		
		// Contents from external resources
		$r .= $this->content;
		
		$r .= "<br/><br/>\n\nMemory Used during execution : <span class=\"debug_memory\">".sprintf('%0.3f', $this->getMemory() / 1048576 ) . ' MB</span><br/>'."\n";
		$r .= 'Time past during execution : <span class="debug_timer">'.sprintf('%0.3f', $this->getPast()).' sec</span>'."\n";
		$r .='</div>';
		
		return $r;
	}

	/**
	 * Sends output to desired target defined in {@link	ArtaDebug::$mode}.
	 * @param	bool	$run	true starts output and false postpones execution to latest shutdown function.
	 */
	function out($run){
		//postpone it to last position on shutdown functions.
		//Gives the opportunity to other classes to use shotdown functions for passing reports.
		if($run==false){ 
			register_shutdown_function (array($this, 'out'), true);
			return;
		}
		if($this->enabled == true){
			switch($this->mode){
			case 'echo':
			default:
				echo $this->show();
			break;
			case 'file':
				ArtaFile::write(ARTAPATH_BASEDIR.'/tmp/logs/debug_'.$this->getMicrotime().'.html', $this->show());
				ArtaFile::write(ARTAPATH_BASEDIR.'/tmp/logs/index.html', '');
			break;
			}
		}
	}

	/**
	 * Gets Application memory usage. 
	 * 
	 * @return	int	memory in bytes
	 */
	function getMemory(){
		if(function_exists( 'memory_get_usage' )){
			return memory_get_usage();
		}else{
			return 0;
		}
	}

	/**
	 * Gets Current Microtime
	 * 
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
		$past = round($now - $this->start, 3);
		$this->t2 = $past;
		return $past;
	}
	
	/**
	 * Gets elapsed time between last report and now
	 * Should be called after calling {@link	ArtaDebug::getPast()}.
	 * 
	 * @return	float
	 */
	function getDelta(){
		// Get time past from last report
		$past = round($this->t2 - $this->t1, 3);
		$this->t1 = $this->t2;
		return $past;
	}
	
	/**
	 * Creates a list in the output.
	 * Note: This function only works when the Debugging engine is enabled. Before gathering the list, watch for {@link	ArtaDebug::$enabled} flag to avoid unnecessary work.
	 * 
	 * @param	array	$list
	 * @param	string	$title	List title. "Loaded language files" for instance.
	 * @param	string	$className	Use descriptive class names. For instance, "debug_language_loaded" can be a good classname on listing loaded language files.
	 * @param	bool	$ordered	Indicates that the method should create a ordered list or an unordered list (ol or ul).
	 * @return	bool	true on success, false on disabled mode.
	 */
	function addList($list, $title, $className, $ordered=true){
		if(!$this->enabled) return false;
		
		$res = "<b>".htmlspecialchars($title)." (".count($list)."):</b>";
		$list = (array)$list;
		if(count($list)){
			$res .= '<'.($ordered?'ol':'ul').' class="'.$className.'">';
			foreach($list as $v){
				$res .='<li>'.htmlspecialchars($v)."</li>\n";
			}
			$res .= '</'.($ordered?'ol':'ul').'>';
		}else{
			$res .= '<div class="debug_list_nothing">&lt;Nothing!&gt;</div>';
		}
		
		return $this->addColumn($res);
	}


}
?>
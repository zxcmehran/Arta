<?php 
/**
 * Application Base File. 
 * To create an instance simply first include {@link	import.php} from library root 
 * then create new application using loader function ({@link	ArtaLoader::Application()})
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
// ensure that import.php is loaded
if(!defined('ARTA_VALID')) {
	die('Essentials are not loaded. Please try to load essentials by calling loadEssentials() from import.php');
}

/**
 * ArtaApplication Class
 * Creates application and runs it.  
 * @static
 */
class ArtaApplication {

	/**
	 * Application Constructor. You may call it Main Method.
	 * 1.Loads <b>debug, config, package, plugin, template, language, module</b> and <b>cron</b>.
	 * 2.Reports a debug message
	 * 3.Sets application default settings
	 * 4.Loads client importer
	 * 5.Sets request variables passed via SEF or rewrite method
	 * 6.Initializes Classes that loaded on first step
	 * 7.Checks website offline status
	 * 8.Starts processing package and then <b>output</b>!
	 * 
	 * @static
	 */
	static function start() {
		
		$debug = ArtaLoader::Debug();
		$debug->report('Application execution started. Application client : '.
			CLIENT, 'ArtaApplication::__construct', true);
		$config = ArtaLoader::Config();
		$domain = ArtaLoader::Domain();		
		$package = ArtaLoader::Package();
		$plugin = ArtaLoader::Plugin();
		$template = ArtaLoader::Template();
		$language = ArtaLoader::Language();
		$module = ArtaLoader::Module();
		$user=ArtaLoader::User();
		// User initialization is needed before setVars and setSecure and don't uses any 
		// request variables so can be called before setVars.
		$user->initialize();
		
		$plugin->initialize();
		$plugin->trigger('onBeforeSetVars');
		$domain->initialize();
		// set request variables via SEF and rewrite
		ArtaRequest::setVars();
		$domain->initialize2();
		ArtaRequest::setSecure();
		ArtaError::cleanAdminAlert();
		
		$language->initialize();
		
		// Pass config to translator plugin
		$plugin->trigger('onPrepareContent', array(&$config, 'config'));
		$plugin->trigger('onBeforeLoad', array());
		/**
		 * These are default settings of client
		 */
		
		$GLOBALS['_CLIENTDATA'] = array(
			'SEF_ENABLED' => true,
			'OFFLINE_BYPASS' => false,
			'DEFAULT_PACKAGE' => false);
		// Import Client Importer to customize application
		ArtaLoader::Import('includes->import',
			'client');
		$plugin->trigger('onAfterClientImport',
			array());

		$cron = ArtaLoader::Cron();
		
		if($cron->todoCount()<=0){
			// Handle offline status if there is no tasks to do by ArtaCron.
			self::handleOffline();
			$noofflinehandle=true;
		}

		// some initializies
		$package->initialize();
		$template->initialize();
		$module->initialize();		
		
		$cron->Run();
		
		if(defined('CRON_LOADER')){
			return;
		}
		
		if(!isset($noofflinehandle)){
			// Handle offline status if its not handled due cron jobs todo list.
			self::handleOffline();
		}
		
		if(!isset($_SESSION['ARTAMSG'])) {
			$_SESSION['ARTAMSG'] = array();
		}
		
		// LET'S ROCK!
		$plugin->trigger('onAfterLoad', array());
		
		ArtaUrgentUpdater::initialize();
		
		$package->load();
		
		$template->addtoTmpl($package->getResult(), 'package');
		if($template->type!='html'){
			$module->enabled= false;
		}

		
		// Populate what modules are there and which locations are used,
		$module->filterData();
		
		if($module->enabled){
			// If doctype remained HTML during package execution by setDoctype()
			$template->addtoTmpl(ArtaApplication::viewMessage(), 'message');
			$template->addtoTmpl(
				'<p style="direction:ltr;">'.
				(CLIENT=='site'?'Powered by ':'').ArtaVersion::getCredits(true, CLIENT=='admin' AND @$user->user->id>0, CLIENT=='admin').'</p>', 
				'copyright');
		}
		
		// Load template
		$template->loadTemplate();
		// Load only modules that those locations' are defined in Template 
		$module->renderAll();
		
		// Now replace location placeholders with values
		$template->prepare();
		
		$plugin->trigger('onBeforeTemplateRender',
			array(&$template));
		$template->render();
		$plugin->trigger('onAfterTemplateRender',
			array(&$template));

		$template->toString();
		
	}

	/**
	 * Handles Application Offline status. It will terminate application if Website is set offline in configuration and client can not bypass offline mode.
	 * @static
	 */
	static function handleOffline() {

		//Handle offline status
		if($c = getVar('bypass_code', false, '', 'string')) {
			echo '<script>document.location.href="'.ArtaURL::getSiteURL().'index.php?offline_pass='.md5($c).'";</script>';
			die();
		}
		$config = ArtaLoader::Config();
		$op = getVar('offline_pass', false, '', 'string');
		if(!$op ||
			strlen($config->offline_pass) == 0) {
			$valid = false;
		} elseif($op == md5($config->
		offline_pass)) {
			$valid = true;
		}else{
			$valid=false;
		}
		
		if($config->offline == 1 && $GLOBALS['_CLIENTDATA']['OFFLINE_BYPASS'] == false &&
			$valid==false) {
			$d=ArtaLoader::Debug();
			$d->enabled=false;
			ArtaLoader::Import('#template->tags->html');
			header('HTTP/1.1 503 '.ArtaError::getStatusText(503));
			die(ArtaTagsHtml::getOfflineMsg());
		}

	}
    
    /**
     * Redirects User to Installer if system is not installed.
     */
    static function gotoInstaller(){
        if(file_exists(ARTAPATH_BASEDIR.'/config.php')==false || filesize(ARTAPATH_BASEDIR.'/config.php')<=512){
            $url=(CLIENT=='site'?'':'../').'install/index.php';
            if(is_file(ARTAPATH_BASEDIR.'/install/index.php')==false){
                die('Installer not found. Please extract Arta from the archive again.');
            }
            if(headers_sent()) {
    			echo "<script>document.location.href='$url';</script><a href=\"$url\">Install Arta</a>\n";
    		} else {
    			header('HTTP/1.1 307 Temporary Redirect');
    			header('Location: '.$url);
    		}
    		die();
        }
    }

	/**
	 * Returns client name
	 * 
	 * @static
	 * @return	string	Client name
	 */
	static function getClient() {
		return CLIENT;
	}

	/**
	 * Redirects user to an URL.
	 * 
	 * @static
	 * @param	string	$url	URL for redirecting to
	 * @param	string	$msg	Message to be showed to user about redirection
	 * @param	string	$msgType	Message type. "tip" or "warning" or "error"
	 */
	static function redirect($url, $msg='', $msgType='tip', $make=true) {

		if($url == null){
			$url = 'index.php';
		}
		$url = preg_split("/[\r\n]/", $url);
		if($make){
			$url = ArtaURL::make($url[0]);
		}else{
			$url=$url[0];
		}
        
		// If the message exists, enqueue it
		if(trim($msg)){
			ArtaApplication::enqueueMessage($msg, $msgType);
		}

		if(headers_sent()){
			echo "<script>document.location.href='$url';</script><noscript><a href=\"$url\">$url</a></noscript>\n";
		}else{
			header('HTTP/1.1 301 Moved Permanently');
			header('Location: '.$url);
		}
		die();
	}

	/**
	 * Enqueue a message to be showed to user
	 * 
	 * @static
	 * @param	string	$msg	Message to be showed to user
	 * @param	string	$msgType	Message type. "tip" or "warning" or "error"
	 */
	static function enqueueMessage($msg, $msgType =
		'tip') {
		$_SESSION['ARTAMSG'][strtolower($msgType)][] =
			$msg;
	}
	

	/**
	 * Returns HTML-format of messages or selected message type
	 * 
	 * @static
	 * @param	string	$msgType	Message type. "tip" or "warning" or "error". pass null to return all
	 * @return	string	HTML code
	 */
	static function viewMessage($msgType = '') {
		$res = '';
		if(!isset($_SESSION['ARTAMSG'])) {
			$_SESSION['ARTAMSG'] = false;
		}
		if(is_array($_SESSION['ARTAMSG'])) {
			if(!$msgType) {

				foreach($_SESSION['ARTAMSG'] as $k => $v) {
					if(count($v) > 0) {
						$i = 0;
						$res .= '<div class="message '.strtolower($k).
							'">';
						foreach($v as $ck => $cv) {
							unset($_SESSION['ARTAMSG'][strtolower($k)][$ck]);
							if($cv) {
								$res .= $i == 0?'' : "<br>\n";
								$res .= htmlspecialchars($cv);
								$i++;
							}
						}
						$res .= "</div>\n";
					}
				}

			} else {
				if(count($v) > 0) {
					$res .= '<div class="message '.strtolower($msgType).
						'">';
					$i = 0;
					foreach($_SESSION['ARTAMSG'][$msgType] as
						$k => $v) {
						unset($_SESSION['ARTAMSG'][strtolower($msgType)][$k]);
						if($v) {
							$res .= $i == 0?'' : "<br>\n";
							$res .= htmlspecialchars($v);
							$i++;
						}
					}
					$res .= "</div>\n";
				}
			}
		}

		if($res !== '' && class_exists('ArtaTagsHtml')) {
			ArtaTagsHtml::addtoTmpl(ArtaTagsHtml::MessageEffect(), 'beforebodyend');
			ArtaTagsHtml::addtoTmpl(ArtaTagsHtml::MessageCSS(), 'head');
		}
		return $res;
	}

}

?>
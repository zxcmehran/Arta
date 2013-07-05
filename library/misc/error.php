<?php 
/**
 * Errors Engine of Arta Application
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 3 2013/07/05 14:00 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaError Class
 * Used to generate errors
 * 
 * @static
 */

class ArtaError {

	/**
	 * Show an error then die()!
	 *
	 * @static
	 * @param	int	$type	Error number. Refer to ArtaError::getStatusText().
	 * @param	string	$txt	An error reason to show
	 * @param	string	$redirect	if you want to redirect user specify the destination url here
	 * @param	int	$r_time	redirect timeout
	 */
	static function show($type=404, $txt=null, $redirect=false, $r_time=5){
		$type=(int)$type;
				

		// send header using ArtaError::getStatusText($type)
		header('HTTP/1.1 '.$type.' '.ArtaError::getStatusText($type));

		
		$package = ArtaLoader::Package();
		// finish output  buffering
		$package->addResult();
		// now null the buffered content
		$package->content = null;
		$c=ArtaLoader::Config();
		$u=ArtaLoader::User();
		$u=$u->getCurrentUser();
		$error=trans('ERROR').': '.$type.' - '.ArtaError::getStatusText($type);
		$lang=trans('_LANG_ID');
		$encoding=trans('_LANG_CHARSET');
		$dir=trans('_LANG_DIRECTION');
		echo <<<_HTML
<!DOCTYPE html>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="$lang" lang="$lang">
<head>
	<meta http-equiv="content-type" content="text/html; charset=$encoding" />
	<meta name="author" content="Mehran Ahadi" />
	<title>$error</title>
    <style>
body {
		text-align:center;
}
div.errorbox {
		display:inline-block;
		text-align: center;
		border: 2px solid #ffcccc;
		width:450px;
		padding: 20px;
		background: #ee4444;
		color: white;
		font-family: "Trebuchet MS", Tahoma, Arial, sans-serif;
}
div.errorhead {
		font-size: 135%;
}
div.errormsg{
		font-size:95%;
		color:black;
		margin:10px;
		padding:5px;
		display:inline-block;
		background: #fff;
		border: 2px solid #ffcccc;
		width:350px;
}
div.noerror {
		border: 2px solid #ccffcc;
		background: #44aa44;
		
}
div.noerror .errormsg{
		border: 2px solid #ccffcc;
}
    </style>
</head>
<body dir="$dir">	
_HTML;
		
		// lets do it...
		echo '<div class="errorbox'.($type==200?' noerror':'').'">';
		if($type!=200){
			echo '<div class="errorhead">'.trans('ERROR').' : '.'<span class="errornum">'. $type .'</span> - '."\n".'<span class="errortype">'.ArtaError::getStatusText($type).'</span></div>';
			if(trim($txt))
			echo '<div class="errormsg">'.str_replace(array("\n", "\r", "\r\n"), ' ', $txt)."</div>\n";
		}
		if($redirect){
			$r= ArtaURL::make($redirect);
			$rt= $r_time * 1000;
			echo '<br/><script>function redirect(){document.location.href = "'.addslashes($r).'";} setTimeout("redirect()", '.$rt.');</script>'."\n";
			echo '<input type="button" onclick="redirect();" value="'.trans('IF YOU ARE NOT REDIRECTED CLICK HERE').'"/><br/>'."\n";
		}
		if($c->debug==true && $type!=200){
			echo '<br/>See Backtrace at Debug Output for more information.';
		}
		
		echo '<br/><div class="errorlinks"><a href="'.ArtaURL::getClientURL().'">'.trans('HOMEPAGE').'</a>';
		
		if(@$u->id==0 && $type==403 && CLIENT=='site'){
			echo ' | <a href="'.makeURL('index.php?pack=user&view=login&redirect='.base64_encode('index.php?'.$_SERVER['QUERY_STRING'])).'">'.trans('LOGIN PAGE').'</a>';
		}
		
		echo '</div></div><br/><br/>';
		echo '</body></html>';
		
		$debug = ArtaLoader::Debug();
		if($debug->enabled){
			$r ="<b>Backtrace:</b><p class=\"debug_backtrace\">\n";
			ob_start();
			debug_print_backtrace();
			$r .=nl2br(ob_get_contents());
			ob_end_clean();
			$r .="\n</p>";
			$debug->addColumn($r, true);
		}
		
		
		
		// EoL (end of life!) ;)
		die();
	}

	/**
	 * Get HTTP status phrase
	 * codes: 
	 * 		200 => 'OK',
	 *		301 => 'Moved Permanently'
	 *		307 => 'Temporary Redirect'
	 *		400 => 'Bad Request'
	 *		401 => 'Unauthorized'
	 *		403 => 'Forbidden'
	 *		404 => 'Not Found'
	 *		500 => 'Internal Server Error'
	 *		501 => 'Not Implemented'
	 *		503 => 'Service Unavailable'
	 *		504 => 'Gateway Timeout'
	 *
	 * @static
	 * @param	int	$code	HTTP status code to get it's phrase
	 * @link	http://www.w3schools.com/TAGS/ref_httpmessages.asp
	 * @return	string	HTTP status phrase
	 */
	static function getStatusText($code){
		/*$headers=array(			
			200 => 'OK',
			201 => 'CREATED',
			202 => 'Accepted',
			203 => 'Partial Information',
			204 => 'No Response',
			301 => 'Moved',
			302 => 'Found',
			303 => 'Method',
			304 => 'Not Modified',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			402 => 'PaymentRequired',
			403 => 'Forbidden',
			404 => 'Not found',
			500 => 'Internal Error',
			501 => 'Not implemented',
			502 => 'Service temporarily overloaded',
			503 => 'Gateway timeout');*/
		
		// its more trusted.
		$headers=array(			
			200 => 'OK',
			301 => 'Moved Permanently',
			307 => 'Temporary Redirect',
			400 => 'Bad Request',
			401 => 'Unauthorized',
			403 => 'Forbidden',
			404 => 'Not Found',
			500 => 'Internal Server Error',
			501 => 'Not Implemented',
			503 => 'Service Unavailable',
			504 => 'Gateway Timeout');
			
		return @$headers[$code];
	}

	/**
	 * Add alert for Administrator. You can use it to notify admins to solve problems!
	 *
	 * @static
	 * @param	string	$at	Where the error occured e.g. "/home/www/packages/something/example.php at line 110 in MyClass::doSomethingExample()" OR "User Registeration"
	 * @param	string	$when	When you was doing something and error happened e.g. When it was trying to insert data to DB
	 * @param	string	$tip	Your tip about this error (how to solve or how did it appear or anything else)
	 * @param	bool	$updateCount	Update occurance count on duplicate row ?
	 * @return	bool	true 
	 */
	static function addAdminAlert($at, $when, $tip='', $updateCount=true){
		$c=ArtaLoader::Config();
		if($c->admin_alert){
			$db=ArtaLoader::DB();
			// if similars exist
			$db->setQuery('SELECT * FROM #__admin_alerts WHERE `at`='.$db->Quote($at).' AND `when`='.$db->Quote($when).' AND `tip`='.$db->Quote($tip));
			$data=$db->loadAssoc();
			if(isset($data['id'])){
				if($updateCount){
					$db->setQuery('UPDATE #__admin_alerts SET `times`='.$db->Quote($data['times']+1).', `last_time`='.$db->Quote(time()).' WHERE `id`='.$db->Quote($data['id']) );
					$db->query();
				}
			}else{
				$db->setQuery('INSERT INTO #__admin_alerts (`at`,`when`,`tip`,`last_time`) VALUES ('.$db->Quote($at).','.$db->Quote($when).','.$db->Quote($tip).','.$db->Quote(time()).')');
				$db->query();
			}

			$err=$db->getError();
			if($err==true){return false;}else{return true;}
		}
		return true;
	}

	/**
	 * Cleans Admin Alerts expired log entries.
	 *
	 * @static
	 * @param	int	$lifetime	Lifetime in seconds
	 * @param	bool	$randam	Use probability to decide what to do. If False always acts.
	 * @return	bool
	 */
	static function cleanAdminAlert($lifetime=null, $random=true){
		if($random==false || mt_rand(0,9)==5){
			$db=ArtaLoader::DB();
			if(!is_numeric($lifetime)){
				$c=ArtaLoader::Config();
				$lifetime=$c->admin_alert_lifetime;
			}
			$lifetime=(int)$lifetime;
			$db->setQuery('DELETE FROM #__admin_alerts WHERE last_time < '.$db->Quote(time() - $lifetime));
			$db->query();
			$err=$db->getError();
			if($err==true){return false;}else{return true;}
		}
	}
}

?>
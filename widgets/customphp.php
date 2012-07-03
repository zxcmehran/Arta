<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 6 $
 * @date		2009/12/17 16:26 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

ArtaLoader::Import('misc->tempstream');
$r=ArtaString::makeRandStr();
file_put_contents('artatmp://customphp_widget_'.$r, $settings['phpcode_to_run']);

include('artatmp://customphp_widget_'.$r);

?>
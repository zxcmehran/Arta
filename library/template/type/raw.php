<?php 
/**
 * ArtaTemplate RAW Type methods
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaTemplate
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// detect arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaTemplateRaw Class
 * ArtaTemplate RAW Type methods
 * @static
 */
class ArtaTemplateRaw{
	
	
	static function loadTemplate(){
		$template = ArtaLoader::Template();
		$template->content = "<artatmpl type=\"package\">";
		return $template->content;
	}

	static function prepare(){
		$debug = ArtaLoader::Debug();
		$debug->report('Preparing template...', 'ArtaTemplate::prepare');
		$template = ArtaLoader::Template();
		$locs=$template->getLocations();
		foreach($locs as $loc => $con){
			if($loc == 'package'){
			preg_match('<artatmpl *type="'.$loc.'" */?>', $template->content, $i );
			if(isset($i[0])){
				$val='';
				foreach($con as $k => $v){
					$val .= $v;
				}
				$template->content=str_replace('<'.$i[0].'>', $val, $template->content);
			}
			}
		}
		return $template->content;
	}	

	static function render(){
		$debug = ArtaLoader::Debug();
		$debug->report('Rendering started.', 'ArtaTemplate::render');
		if($debug->mode !== 'file'){
			$debug->enabled=false;
		}
		$template = ArtaLoader::Template();
		return $template->content;
	}

}
?>
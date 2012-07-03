<?php 
/**
 * ArtaTemplate XML Type methods
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaTemplate
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

// detect arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaTemplateXml Class
 * ArtaTemplate XML Type methods
 * @static
 */
class ArtaTemplateXml{
	
	
	static function loadTemplate(){
		$p=ArtaLoader::Package();
		preg_match("@^<\?xml@", $p->content, $m);
		$template = ArtaLoader::Template();
		// remove XML Start code from template if exists in package result content. 
		if(@strlen($m[0])>0){
			$template->content = '<artatmpl type="package">';
		}else{
			$template->content = '<?xml version="1.0" encoding="'.$template->getCharset().'"?>'."\n<artatmpl type=\"package\">";
		}
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
		header('Content-type: text/xml');
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
<?php 
/**
 * ArtaTemplate HTML Type methods
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
 * ArtaTemplateHtml Class
 * ArtaTemplate HTML Type methods
 * @static
 */
class ArtaTemplateHtml{
	
	
	static function loadTemplate(){
		$template = ArtaLoader::Template();
		ob_start();
		if(!ArtaLoader::Import('templates->'.$template->getName().'->'.$template->getTmpl(), 'client')){
			$u=ArtaLoader::User();
			$g=$u->getGuest();
			$gs=unserialize($g->settings);
			$u=$u->getCurrentUser();
			$us=unserialize($u->settings);
			$sname=CLIENT.'_template';
			if(ArtaFilterinput::safeAddress($template->getName())==@ArtaFilterinput::safeAddress($gs->$sname)){
				$db=ArtaLoader::DB();
				$db->setQuery('UPDATE #__userfields SET `default`='.$db->Quote(serialize('default')).' WHERE `extype`=\'library\' AND `var`='.$db->Quote($sname), array('default'));
				$db->query();
				redirect(ArtaURL::getURL());
			
			}elseif(ArtaFilterinput::safeAddress($template->getName())==@ArtaFilterinput::safeAddress($us->$sname)){
				$us->$sname=$gs->$sname;
				$db=ArtaLoader::DB();
				$db->setQuery('UPDATE #__users SET settings='.$db->Quote(serialize($us)).' WHERE id='.$u->id, array('settings'));
				$db->query();
				redirect(ArtaURL::getURL());
			}
			die('Template file not found. Try "index.php?template=an_available_template&tmpl=an_available_template_file". By default template is "default" and tmpl is "index".');
		}
				
		$template->content = ob_get_contents();
		ob_end_clean();
		return $template->content;
	}


	static function addHTMLHeader(){ // its designed to add scripts and styles always at first and then other head contents
		$template = ArtaLoader::Template();
		ArtaTagsHtml::addLibraryScript('html5shiv');
		$head = '';
		$head .= '<title>'.htmlspecialchars($template->getTitle()).'</title>';
		$head .= "\n\t";
		$head .= ArtaTagsHtml::meta();
		$head .= "\n\t";
		$head .= ArtaTagsHtml::script();
		$head .= ArtaTagsHtml::CSS();
		$plugin = ArtaLoader::Plugin();
		$plugin->trigger('onBeforeAddHTMLHeader', array(&$head));
		$head .= implode("\n\n",$GLOBALS['_HEAD'])."\n\t";
		
		$template->addtoTmpl($head, 'head-unlocked');
	}
	

	static function prepare(){
		$debug = ArtaLoader::Debug();
		$debug->report('Preparing template...', 'ArtaTemplate::prepare');
		$template = ArtaLoader::Template();
		self::addHTMLHeader();
		$template->Header( 'Content-Type', 'text/html; charset=' . $template->getCharset());
		$locs=$template->getLocations();
		
		preg_match('<artatmpl *type="head" */?>', $template->content, $i );
		if(!isset($i[0])){
			preg_match('<head(.*)?>',$template->content, $i);
			$template->content=str_replace('<'.$i[0],
			 '<'.$i[0]."\n<artatmpl type=\"head\">", $template->content);
		}

		preg_match('<artatmpl *type="afterbody" */?>', $template->content, $i );
		if(!isset($i[0])){
			preg_match('<body(.*)?>',$template->content, $i);
			$template->content=str_replace('<'.$i[0],
			 '<'.$i[0]."\n<artatmpl type=\"afterbody\">", $template->content);
		}
		
		
		preg_match('<artatmpl *type="beforebodyend" */?>', $template->content, $i );
		if(!isset($i[0])){
			preg_match('</body>',$template->content, $i);
			$template->content=str_replace('<'.$i[0],
			 "<artatmpl type=\"beforebodyend\">\n<".$i[0], $template->content);
		}
		
		foreach($locs as $loc => $con){
			preg_match('<artatmpl *type="'.$loc.'" */?>', $template->content, $i );
			if(isset($i[0])){
				$val='';
				foreach($con as $k => $v){
					$val .= $v;
				}
				$template->content=str_replace('<'.$i[0].'>', $val, $template->content);
			}

		}
		preg_match_all('#<artatmpl type=\"\w*\" */?>#', $template->content, $i );
		foreach($i[0] as $v){
			$template->content=str_replace($v, '',$template->content);
		}
		
		return $template->content;
	}
	
	static function render(){
		$debug = ArtaLoader::Debug();
		$debug->report('Rendering started.', 'ArtaTemplate::render');
		// Nothing extra to do. For example see PDF type render function
		$template = ArtaLoader::Template();
		return $template->content;
	}
}
?>
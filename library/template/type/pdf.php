<?php 
/**
 * ArtaTemplate PDF Type methods
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
 * ArtaTemplatePdf Class
 * ArtaTemplate PDF Type methods
 * @static
 */
class ArtaTemplatePdf{
	
	static function loadTemplate(){
		$pdf=ArtaLoader::PDF();
		$pdf->initialize();
		$template=ArtaLoader::Template();
		$template->content='<artatmpl type="package">';
		return $template->content;
	}

	static function getPDF(){
		return ArtaLoader::PDF();
	}


	static function prepare(){
		$debug = ArtaLoader::Debug();
		$debug->report('Preparing template...', 'ArtaTemplate::prepare');
		$template = ArtaLoader::Template();
		$template->Header( 'Content-Type', 'text/plain; charset=' . $template->getCharset());
		$locs=$template->getLocations();
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
		return $template->content;
	}

	static function render(){
		$template=ArtaLoader::Template();
		$debug = ArtaLoader::Debug();
		$debug->report('Rendering started.', 'ArtaTemplate::render');
		if($debug->mode=='echo'){
			$debug->enabled=false;
		}
		$pdf=ArtaTemplatePdf::getPDF();
		$pdf->AddPage();
		$pdf->SetHeaderData('',0, $pdf->getTitle(), "\n".$pdf->getSubject()."\n".$pdf->getAuthor());
		ArtaLoader::Import('misc->date');
		$pdf->AppendHeaders();
		$pdf->Header();
		$pdf->WriteHTML($template->content);
		$config=ArtaLoader::Config();
		$pdf->AddFooter($config->site_name.' ('.ArtaURL::getFriendlyURL().')');
		$pdf->AppendFooters();
        
		ob_start();
		$pdf->Output('doc.pdf');
		$c=ob_get_contents();
		ob_end_clean();
		return $c;
	}

}
?>
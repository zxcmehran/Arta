<?php 
if(!defined('ARTA_VALID')){die('No access');}
class PagesViewPage extends ArtaPackageView{

	function display(){
		if($this->getSetting('staticpages_generate_pdf_version', true)==false){
			ArtaError::show(403, trans('PDF GENERATING IS DISABLED'));
		}
		
		$m=$this->getModel();
		
		$pdf=ArtaLoader::PDF();
		
		$pdf->setTitle($m->page->title);
		
		if($m->page->is_dynamic==true){
			ArtaError::show(500, 'Dynamic pages cannot be exported into PDF.');
		}
		
		$c = $m->page->content;
		$c = str_replace(array("\r\n", "\r", "\n"), '', $c);
		$c = str_replace("</p><p>", '<br/>', $c);
		$c = str_replace(array('<br/>', '<br />'), '<br>', $c);
		$c = str_replace('<br><br>', '<br>', $c);

		echo $c;
	}
}
?>
<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewPost extends ArtaPackageView{
	
	function Display(){
		if($this->getSetting('blogposts_generate_pdf_version', true)==false){
			ArtaError::show(403, trans('PDF GENERATING IS DISABLED'));
		}
		
		
		$m=$this->getModel();
		
		$id=getVar('id',0,'','int');
		
		$post=$m->getPost($id);
		
		$pdf=ArtaLoader::PDF();
		$pdf->setAuthor($post->added_by);
		$pdf->setTitle($post->title);
		$pdf->addHeader(trans('ADDED_TIME').' '.ArtaDate::_($post->added_time)."\n");
		if((int)$post->mod_by>0){
			$pdf->addHeader(trans('MOD_BY').' '.htmlspecialchars($post->mod_by)."\n");
			$pdf->addHeader( trans('MOD_TIME').' '.ArtaDate::_($post->mod_time)."\n");
		}
		
		$c=$post->introcontent.$post->morecontent;
		$c = str_replace(array("\r\n", "\r", "\n"), '', $c);
		$c = str_replace("</p><p>", '<br/>', $c);
		$c = str_replace(array('<br/>', '<br />'), '<br>', $c);
		$c = str_replace('<br><br>', '<br>', $c);
		
		echo $c;
		
	}
	
}
?>
<?php 
/**
 * Arta PDF Generator powered by TCPDF
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}
ArtaLoader::Import('pdf->tcpdf->config->lang->eng');
ArtaLoader::Import('pdf->tcpdf->tcpdf');
/**
 * ArtaPDF Class
 * Powered by TCPDF. You can use it to easily create PDFs
 */
class ArtaPDF extends TCPDF{

	/**
	 * Page Headers
	 * @var	string
	 */
	var $headers;
	
	/**
	 * Page Footers
	 * @var	string
	 */
	var $footers;
	
	/**
	 * Initializes PDF
	 */
	function initialize(){
	   
		parent::__construct(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true);
		$debug=ArtaLoader::Debug();
		$debug->report('ArtaPDF started.', 'ArtaPDF::__construct');
		$this->SetCreator('TCPDF on Arta Content Management Framework');
		$this->SetMargins(15, 35, 15);
		$this->SetHeaderMargin(5);
		$this->SetFooterMargin(10);
		$this->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM);
		$this->setImageScale(1); 

		$l = Array();
		// PAGE META DESCRIPTORS --------------------------------------

		$l['a_meta_charset'] = strtoupper(trans('_LANG_CHARSET'));
		$l['a_meta_dir'] = strtolower(trans('_LANG_DIRECTION'));
		$l['a_meta_language'] = strtolower(trans('_LANG_ID_NAME'));

		// TRANSLATIONS --------------------------------------
		$l['w_page'] = trans('PAGE');
		$this->setLanguageArray($l); 
		$this->AliasNbPages();
		$this->SetFont(trans('_PDF_FONT_FAMILY'), trans('_PDF_FONT_STYLE'), trans('_PDF_FONT_SIZE'));
        
        
		$this->setHeaderFont(Array(trans('_PDF_FONT_FAMILY'), trans('_PDF_FONT_STYLE'), trans('_PDF_FONT_SIZE')));
		$this->setFooterFont(Array(trans('_PDF_FONT_FAMILY'), trans('_PDF_FONT_STYLE'), trans('_PDF_FONT_SIZE')));

	}
	
	/**
	 * Adds page Headers
	 * 
	 * @param	string	$str	String to Add
	 */
	function addHeader($str){
		$this->headers .="\n".$str;
	}

	/**
	 * Appends page Headers to PDF
	 */
	function AppendHeaders(){
		$this->header_string.=$this->headers;
	}

	/**
	 * Adds page Footers
	 * 
	 * @param	string	$str	String to Add
	 */
	function addFooter($str){
		$this->footers .="     ".$str;
	}

	/**
	 * Appends page Footers to PDF
	 */
	function AppendFooters(){
		$this->footer_string.=$this->footers;
	}
    
    function getTitle(){
        return $this->title;
    }
    
    function getSubject(){
        return $this->subject;
    }
    
    function getAuthor(){
        return $this->author;
    }

}
?>
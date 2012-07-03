<?php
if(!defined('ARTA_VALID')){die('No access');}
/**
 * Language Translation Interface.
 * All translation classess shuold implement this interface.
 */
interface LanguageTranslation{
	
	/**
	 * Identify column of original content row.
	 * default: id
	 */
	function getIDRowName();
	
	/**
	 * Title column of original content row.
	 * default: title
	 */
	function getTitleRowName();
	
	/**
	 * Title of this Translation script e.g. "Blog posts" or "Links" or "Banners" or...
	 */
	function getTitle();
	
	
	/**
	 * Return your original content from this method
	 */
	function getRows();
	
	/**
	 * Return your contents count from this method.
	 */
	function getRowsCount();
	
	/**
	 * Return controls that you want to show on top of content list. For example Filters or Sorting Controls.
	 */
	function getControls();
	
	/**
	 * Import languages you need for getControls() method.
	 */
	function addLanguage();
	
	/**
	 * Return identified row completely. 
	 */
	function getRow($id);
	
	/**
	 * Does this row exists?
	 */
	function getRowExistence($id);
	
	/**
	 * Check input sent from translation edit form.
	 * $v	input
	 * $row	default row
	 */
	function checkInput($v, $row);

}
?>
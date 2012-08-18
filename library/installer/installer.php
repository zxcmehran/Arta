<?php
/**
 * ArtaInstaller Engine.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaInstaller Class
 */
 
class ArtaInstaller{
	
	/**
	 * Path to install tmp.
	 * @var	string
	 */
	var $path='';
	
	/**
	 * Path to XML File.
	 * @var	string
	 */
	var $file='';
	
	/**
	 * SimpleXML Element object
	 * @var	object
	 */
	var $xml=null;
	
	/**
	 * Installer output
	 * @var	string
	 */
	var $output='';
	
	/**
	 * Is update package? if not it's extension. 
	 * @var	bool
	 */
	var $update=false;
	
	/**
	 * Is Installation? if not it's Uninstallation. 
	 * @var	bool
	 */
	var $install=false;
	
	/**
	 * Outputed content in the middle of installation
	 * @var	string
	 */
	var $content='';
	
	/**
	 * Count of tasks to be done (Exts to be installer)
	 * @var	int
	 */
	var $todo=0;
	
	/**
	 * Currently installing extension.
	 * @var	object
	 */
	var $installing=null;
	
	/**
	 * Recently installed extensions.
	 * @var	array
	 */
	var $installed=array();
	
	/**
	 * Installation of archive completed or not. It means all extensions inside archive are installed or not.
	 * @var	bool
	 */
	var $fully_installed=false;
	
	/**
	 * Path to installation session log file
	 * @var	string
	 */
	var $installed_log='';
	
	/**
	 * Current step of the extension installation
	 * @var	int
	 */
	var $step=0;
	
	/**
	 * Steps should be passed to complete extension installation.
	 * @var	int
	 */
	var $steps=0;
	
	/**
	 * Contains logs of installation.
	 * @var	array
	 */
	var $logs=array();
	
	/**
	 * Contains Installer Driver Instance.
	 * @var	object
	 */
	var $installer;
	
	
	/**
	 * Some Inits...
	 * @param	string	$file	Archive (for install) or XML file(for uninstall).
	 * @return	mixed	Error msg on failure and true on success.
	 */
	function init($file){
		$file=ArtaFile::replaceSlashes($file);
		// predefining...
		@ini_set('max_execution_time', '120'); // so lazy computers are around the world...
		
		// check for any archives to extract
		if($this->isXML($file)==false){
			$this->install=true;
			$this->path=ARTAPATH_BASEDIR.'/tmp/installer/'.ArtaFile::removeExt(ArtaFile::getFilename($file));
			
			// make tmp dir and extract if currently not extracted.
			if(!is_dir($this->path)){
				if(ArtaFile::mkdir_extra($this->path)==false){
					return 'ERROR_CANT_MAKE_TMPDIR';
				}
				
				$r=@ArtaArchive::Extract($file, $this->path);
				if($r==false){
					$this->removeDir();
					return 'ERROR_CANT_EXTRACT_ARCHIVE';
				}
			}
			$xmlfile=$this->path.'/extension.xml';
		}else{
			$xmlfile=$file;
		}
		
		// Check XML existence
		if(is_file($xmlfile)==false){
			$this->removeDir();
			return 'ERROR_NO_XML';
		}
		
		$this->file=$xmlfile;

		// Parse XML
		ArtaLoader::Import('#xml->simplexml');
		$xml=@ArtaSimpleXML::parseFile($xmlfile);

		if($xml==false ||  
		( !isset($xml->extension)&&!isset($xml->update) ) ||
		( isset($xml->extension)&&isset($xml->update) ) 	// both extension and update is denied.  
		 ){
			$this->removeDir();
			return 'ERROR_INVALID_XML';
		}
		
		if($this->isXML($file)==false){ //  if is not uninstall
			// get installation process status to find out which should be installed at this turn.
			$this->installed_log = $installed_log = ArtaFile::getDir($file).'/'.md5(ArtaFile::getFilename($file)).'.ais';
			if(is_file($installed_log)){
				$this->installed=@(array)unserialize(ArtaFile::read($installed_log));
				if(isset($this->installed['todo'])){
					unset($this->installed['todo']);
				}
			}
		}
		
		$this->xml=$xml;
		
		if(isset($xml->update)){
			$this->update=true;
		}
		return true;
	}
	
	function isXML($file){
		return (strtolower(ArtaFile::getExt($file))=='xml');
	}
	
	function removeDir(){
		if(trim($this->path) == ''){
			return;
		}
		ArtaFile::rmdir_extra($this->path);
	}
	
	function Install(){
		if($this->install==false){
			return false;
		}
		
		$this->cleanCache();
		if($this->update){
			ArtaLoader::Import('#installer->update');
			$upd=new ArtaInstallerUpdate($this);
			$r=$upd->Install();
			$this->installer = $upd;
		}else{
			ArtaLoader::Import('#installer->extension');
			$ext=new ArtaInstallerExtension($this);
			$r=$ext->Install();
			$this->installer = $ext;
		}
		ArtaFile::write($this->installed_log, serialize(array_merge($this->installed, array('todo'=>$this->todo)))); // just added todo to find out how many are installed on installer view of installer package.
		return $r;
	}
	
	function Uninstall(){
		if($this->install){
			return false;
		}
		$this->cleanCache();
		if($this->update){
			/*ArtaLoader::Import('#installer->uninstall->update');
			$upd=new ArtaUninstallerUpdate($this);
			$r=$upd->Uninstall();*/
			// Not developed yet
		}else{
			ArtaLoader::Import('#installer->uninstall->extension');
			$ext=new ArtaUninstallerExtension($this);
			$r=$ext->Uninstall();
			$this->installer = $ext;
		}
		
		return $r;
	}
	
	function cleanTmp(){
		$this->removeDir();
	}
	
	function cleanCache(){
		ArtaFile::rmdir_extra(ARTAPATH_BASEDIR.'/tmp/cache');
		ArtaFile::rmdir_extra(ARTAPATH_ADMIN.'/tmp/cache');
	}
	
}



?>
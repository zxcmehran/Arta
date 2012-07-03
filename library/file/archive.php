<?php
 /**
 * ArtaArchive Class for creating ZIP archives and decompressing many archive modes.
 * For supported archive types see {@see	ArtaArchive}.
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
 * ArtaArchive Class
 * By using this class you can Compress ZIP files and extract and list files of zip, tar,
 * tgz, tar.gz, tar.gzip, gz, gzip, tbz2, tar.bz2, tar.bzip2, bz2 and bzip2 files.
 * 
 * @static
 */
class ArtaArchive {
	
	/**
	 * Creates ZIP Files
	 *
	 * @static
	 * @param	string	$filename	 File to create
	 * @param	array	$filesmap	 key must be path to file in disk and value must be target in archive
	 * @param	string	$filecomment	 Archive comment
	 * @param	array	$commentsmap	 key must be path to file in disk and value must be comment. you can specify folders comments too.
	 * @return	bool
	 */
	static function CreateZIP($filename, $filesmap, $filecomment='', $commentsmap=array()){

		ArtaLoader::Import('#file->archive->zip');
		$z=new ArtaArchive_zip($filename);

		// add dirs
		$added_dirs = array();
		foreach($filesmap as $f=>$d){
			$filesmap[$f]=str_replace(array(':','\\'),'/',$d);
			// and for this turn
			$d=str_replace(array(':','\\'),'/',$d);
			$dir=ArtaFile::getDir($d);
			if(array_key_exists($dir, $commentsmap)){$c=$commentsmap[$dir];}else{$c='';}
			if(strlen($dir)>0 AND !in_array($dir, $added_dirs)){
				$added_dirs[]=$dir;
				$z->addDir($dir, $c);
			}
		}
		// add files
		foreach($filesmap as $f=>$d){
			if(array_key_exists($f, $commentsmap)){$c=$commentsmap[$f];}else{$c='';}
			$z->addFile($f, $d, $c);
		}

		$z->save($filecomment);
		return file_exists($filename);
	}

	/**
	 * Extracts Archives. Detects archive type from extension.
	 * Supported types: zip, tar, tgz, tar.gz, tar.gzip, gz, gzip, tbz2, tar.bz2, tar.bzip2, bz2, bzip2
	 * @static
	 * @param	string	$filename	 File to Extract
	 * @param	string	$dest	 Destination directory path
	 * @param	array	$files	 files inside archive to extract. keys are numbers and values are paths
	 * @return	bool
	 */
	static function Extract($filename, $dest, $files=null){
		$filename=ArtaFile::replaceSlashes($filename);
		$dest=ArtaFile::replaceSlashes($dest);
		
		// get specified files if exists
		if($files !=null){
			if(!is_array($files)){
				$files=array($files);
			}
		}
		if(ArtaFile::getExt(ArtaFile::removeExt($filename))=='tar'){$tar=true;}else{$tar=false;}
		$ext=ArtaFile::getExt($filename);
		// at first detect file type
		switch($ext){
			case 'zip':
				$result = self::ExtractZIP($filename, $dest, $files);
			break;
			case 'tar':
				$result = self::ExtractTAR($filename, $dest, $files);
			break;
			case 'tgz'  :
				$tar=true;
			case 'gz'   :
			case 'gzip' :
				$tmpfname=ARTAPATH_BASEDIR.'/tmp/'.ArtaString::makeRandStr().'.tmp';
				$gzresult = self::ExtractGZIP($filename, $tmpfname);
				if ($gzresult ===false){
					ArtaFile::unlink($tmpfname);
					return false;
				}
				if($tar){
					// Try to untar the file
					$result = self::ExtractTAR($tmpfname, $dest, $files);
				}
				else
				{
					$result = ArtaFile::copy($tmpfname,$dest.'/'.ArtaFile::removeExt(ArtaFile::getFilename($filename)));
				}
				ArtaFile::unlink($tmpfname);
			break;
			case 'tbz2' :
				$tar = true; 
			case 'bz2'  :
			case 'bzip2':
				$tmpfname=ARTAPATH_BASEDIR.'/tmp/'.ArtaString::makeRandStr().'.tmp';
				$bzresult = self::ExtractBZIP2($filename, $tmpfname);
				if ($bzresult ===false){
					ArtaFile::unlink($tmpfname);
					return false;
				}
				if($tar){
					// Try to untar the file
					$result = self::ExtractTAR($tmpfname, $dest, $files);
				}
				else
				{
					$result = ArtaFile::copy($tmpfname,$dest.'/'.ArtaFile::removeExt(ArtaFile::getFilename($filename)));
				}
				ArtaFile::unlink($tmpfname);
			break;
			default:
				return false;
			break;
		}
		return $result;
	}

	/**
	 * Extracts Archive file list. Detects archive type from extension.
	 * Supported types: zip, tar, tgz, tar.gz, tar.gzip, gz, gzip, tbz2, tar.bz2, tar.bzip2, bz2, bzip2
	 * @static
	 * @param	string	$filename	 Archive to list files
	 * @return	mixed	false on failure and array on success
	 */
	static function ListFiles($filename){
		$filename=ArtaFile::replaceSlashes($filename);
		if(ArtaFile::getExt(ArtaFile::removeExt($filename))=='tar'){$tar=true;}else{$tar=false;}
		$ext=ArtaFile::getExt($filename);
		// at first detect file type
		switch($ext){
			case 'zip':
				$result = self::ListFilesZIP($filename);
			break;
			case 'tar':
				$result = self::ListFilesTAR($filename);
			break;
			case 'tgz'  :
				$tar=true;
			case 'gz'   :
			case 'gzip' :
				if($tar){
					$tmpfname=ARTAPATH_BASEDIR.'/tmp/'.ArtaString::makeRandStr().'.tmp';
					$gzresult = self::ExtractGZIP($filename, $tmpfname);
					if ($gzresult ===false){
						ArtaFile::unlink($tmpfname);
						return false;
					}
					// Try to untar the file
					$result = self::ListFilesTAR($tmpfname);
					ArtaFile::unlink($tmpfname);
				}
				else
				{
					$result = ArtaFile::removeExt(ArtaFile::getFilename($filename));
				}
				
			break;
			case 'tbz2' :
				$tar = true; 
			case 'bz2'  :
			case 'bzip2':
			if($tar){
					$tmpfname=ARTAPATH_BASEDIR.'/tmp/'.ArtaString::makeRandStr().'.tmp';
					$bzresult = self::ExtractBZIP2($filename, $tmpfname);
					if ($bzresult ===false){
						ArtaFile::unlink($tmpfname);
						return false;
					}
					// Try to untar the file
					$result = self::ListFilesTAR($tmpfname);
					ArtaFile::unlink($tmpfname);
				}
				else
				{
					$result = ArtaFile::removeExt(ArtaFile::getFilename($filename));
				}
			break;
			default:
				return false;
			break;
		}
		if(!is_array($result)){$result=array($result);}
		return $result;
	}

	/**
	 * Lists files in ZIP files
	 *
	 * @static
	 * @param	string	$f	 Archive to list files
	 * @return	mixed	false on failure and array on success
	 */
	static function ListFilesZIP($f){
		ArtaLoader::Import('#file->archive->unzip');
		$z=new ArtaArchive_unzip($f);
		$s=array();
		foreach($z->getList() as $k=>$v){
			$s[]=$k;
		}
		return $s;		
	}

	/**
	 * Lists files in TAR files
	 *
	 * @static
	 * @param	string	$f	 Archive to list files
	 * @return	mixed	false on failure and array on success
	 */
	static function ListFilesTAR($f){
		$data=ArtaFile::read($f);
		$position = 0;
		$result = array();
		
		while ($position < strlen($data)){
			$info = @ unpack("a100filename/a8mode/a8uid/a8gid/a12size", substr($data, $position));
			if (!$info) {
				return false;
			}

			$position += 512;
			$contents = substr($data, $position, octdec($info['size']));
			$position += ceil(octdec($info['size']) / 512) * 512;

			if ($info['filename']) {
				$result[]=$info['filename'];
			}
		}
		return $result;
	}

	/**
	 * Extracts ZIP files
	 *
	 * @static
	 * @param	string	$f	 File to Extract
	 * @param	string	$d	 Destination directory path
	 * @param	array	$fz	 files inside archive to extract. keys are numbers and values are paths
	 * @return	bool
	 */
	static function ExtractZIP($f, $d, $fz=null){
		ArtaLoader::Import('#file->archive->unzip');
		$z=new ArtaArchive_unzip($f);
		//remove pre-slashes from filez
		if(is_array($fz)){
			foreach($fz as $fk=>$fv){
				while($fv{0}=='/' || $fv{0}=='\\' || $fv{0}==':'){
					$fv=substr($fv,1);
				}
				$fv=ArtaFile::replaceSlashes($fv);
				$fv=str_replace(array('\\', ':'), '/', $fv);
				$res=$z->unzip($fv, false, 0755);
				if(!ArtaFile::write($d.'/'.$fv,$res)){return false;}
			}
			return true;
		}else{
		//	$z->debug=true;
			return $z->unzipAll($d);
		}
	}

	/**
	 * Extracts TAR files
	 *
	 * @static
	 * @param	string	$f	 File to Extract
	 * @param	string	$d	 Destination directory path
	 * @param	array	$fz	 files inside archive to extract. keys are numbers and values are paths
	 * @return	bool
	 */
	static function ExtractTAR($f, $d, $fz=null){
		$data=ArtaFile::read($f);
		$position = 0;

		if(is_array($fz)){
			foreach($fz as $fk=>$fv){
				while($fv{0}=='/' || $fv{0}=='\\' || $fv{0}==':'){
					$fv=substr($fv,1);
				}
                $fv = ArtaFile::replaceSlashes($fv);
                $fz[$fk] = str_replace(array(':','/','\\'), '/',$fv);
            }
		}
		
		while ($position < strlen($data)){
			$info = @ unpack("a100filename/a8mode/a8uid/a8gid/a12size/a12mtime/a8checksum/Ctypeflag", substr($data, $position));
			if (!$info) {
				return false;
			}

			$position += 512;
			$contents = substr($data, $position, octdec($info['size']));
			$position += ceil(octdec($info['size']) / 512) * 512;

			if ($info['filename']) {
				$fdata = null;
				$fmode = null;
				$fdate = octdec($info['mtime']);
				$fname = $info['filename'];
				$fsize = octdec($info['size']);
				$ftype = $info['typeflag'];
                
    			if(!is_array($fz) || (is_array($fz) && in_array($fname, $fz))){
    				if (($ftype == 0) || ($ftype == 0x30) || ($ftype == 0x35)) { 
    					/* File or folder. */
    					$fdata = $contents;
    					$fmode = octdec(substr($info['mode'], 3, 4));
    				} else {
    					/* Some other type. */
    				}

					if(($ftype == 0) || ($ftype == 0x30)){
						if(ArtaFile::write($d.'/'.$fname, $fdata)){
							@touch($d.'/'.$fname, $fdate);
							ArtaFile::chmod($d.'/'.$fname, $fmode);
						}else{
							return false;
						}
					}elseif($ftype == 0x35){
						if(!is_dir($d.'/'.$fname) && !ArtaFile::mkdir($d.'/'.$fname)){
							return false;
						}
					}
				}
			}
		}
		return true;
	}

	/**
	 * Extracts BZIP2 Files
	 *
	 * @static
	 * @param	string	$f	 File to Extract
	 * @param	string	$d	 Destination directory path
	 * @return	bool
	 */
	static function ExtractBZIP2($f, $d){
		// Is bz2 extension loaded?  If not try to load it
		if (!extension_loaded('bz2') && !function_exists('bzdecompress') && function_exists('dl')) {
			if (IS_WIN) {
				@ dl('php_bz2.dll');
			} else {
				@ dl('bz2.so');
			}
		}
		if (!extension_loaded('bz2')) {
			return false;
		}
		if (!$data = ArtaFile::read($f)){
			return false;
		}
		$buffer = bzdecompress($data);
		if (empty ($buffer)) {
			return false;
		}

		if (ArtaFile::write($d, $buffer) === false) {
			return false;
		}
		return true;
	}


	/**
	 * Extracts GZIP Files
	 *
	 * @static
	 * @param	string	$f	 File to Extract
	 * @param	string	$d	 Destination directory path
	 * @return	bool
	 */
	static function ExtractGZIP($f, $d){	
		
		if (!extension_loaded('zlib')) {
			return false;
		}
		
		if (!$data = ArtaFile::read($f)){
			return false;
		}

		//////////////////////////get position//////////////////////////
		$gzflag = array (
			'FTEXT' => 0x01,
			'FHCRC' => 0x02,
			'FEXTRA' => 0x04,
			'FNAME' => 0x08,
			'FCOMMENT' => 0x10
		);
		$position = 0;
		$info = @ unpack('CCM/CFLG/VTime/CXFL/COS', substr($data, $position +2));
		if (!$info) {
			return false;
		}
		$position += 10;

		if ($info['FLG'] & $gzflag['FEXTRA']) {
			$XLEN = unpack('vLength', substr($data, $position +0, 2));
			$XLEN = $XLEN['Length'];
			$position += $XLEN +2;
		}

		if ($info['FLG'] & $gzflag['FNAME']) {
			$filenamePos = strpos($data, "\x0", $position);
			$filename = substr($data, $position, $filenamePos - $position);
			$position = $filenamePos +1;
		}

		if ($info['FLG'] & $gzflag['FCOMMENT']) {
			$commentPos = strpos($data, "\x0", $position);
			$comment = substr($data, $position, $commentPos - $position);
			$position = $commentPos +1;
		}

		if ($info['FLG'] & $gzflag['FHCRC']) {
			$hcrc = unpack('vCRC', substr($data, $position +0, 2));
			$hcrc = $hcrc['CRC'];
			$position += 2;
		}

		//////////////////////////////////////////
		$buffer = gzinflate(substr($data, $position, strlen($data) - $position));
		if (empty ($buffer)) {
			return false;
		}
		if (ArtaFile::write($d, $buffer) === false) {
			return false;
		}
		return true;
	}

}
?>
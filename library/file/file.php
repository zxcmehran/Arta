<?php
/**
 * Arta File Manager (ArtaFile)
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaFile Class
 * File manager class. It's better to use this class because of safe mode and permissions.
 * It can use FTP on default functions failure.
 * @static
 */

class ArtaFile{

	/**
	 * Loads FTP class then Connects it
	 *
	 * @static
	 * @return	mixed	FTP Resource on valid connection otherwise returns false
	 */
	static function getFTP(){
		if(isset($GLOBALS['FTP_DEAD'])){
			return false;
		}
		$config=ArtaLoader::Config();
		
		//if FTP enabled
		if($config->ftp_enabled==1){
			// import class
			ArtaLoader::Import('file->ftp->ftp');
			$ftp=ArtaLoader::FTP();

			// if not connected, try to connect
			if($ftp->_connected !== true){
				$debug=ArtaLoader::Debug();
				// connect then check connection validity
				if(!$ftp->SetServer($config->ftp_host, (int)$config->ftp_port) || 
				!$ftp->connect() || 
				!$ftp->login($config->ftp_user, $config->ftp_pass) || 
				!$ftp->SetType(FTP_AUTOASCII) || 
				!$ftp->Passive(TRUE) || 
				!$ftp->chdir($config->ftp_path) || 
				!$ftp->file_exists('config.php') || 
				!$ftp->file_exists('index.php') || 
				(int)$ftp->filesize('config.php') !== (int)filesize(ARTAPATH_BASEDIR.'/config.php') ){
					$ftp->quit();
					// notify admin
					ArtaError::addAdminAlert('File manager class', 'Connecting to FTP', 'FTP Connection was invalid. This maybe because of invalid connection information or invalid "Path to Arta root via FTP" value at system configuration.');
					$debug->report('We were going to use FTP but we couldn\'t use it (because of connection or detection problems).','ArtaFile::getFTP');
					$GLOBALS['FTP_DEAD']=true;
					$preferred=false;
				}else{
					$preferred=$ftp;
					$debug->report('We are using FTP!','ArtaFile::getFTP');
					register_shutdown_function(array($ftp, 'quit'));
				}

			}else{
				$preferred=$ftp;
			}

		}else{
			$preferred=false;
		}
		return $preferred;
	}
	
	/**
	 * Gets file Extension
	 *
	 * @static
	 * @param	string	$path	Filename or Filepath
	 * @return	string	extension
	 */
	static function getExt($path){
		$subs=explode('.', $path);
		if(count($subs)==1){
			return '';
		}
		return $subs[count($subs)-1];
	}
	
	/**
	 * realpath() equivalent with mock file supporting.
	 * It acts just like realpath(), but it's not limited to file existence.
	 *
	 * @static
	 * @param	string	$path	Filepath
	 * @return	string	Canonical Filepath
	 */
	static function realpath($path){
		$path = self::replaceSlashes($path);
		if(strlen($path)==0) return;
		
		$res = array();
		$parts = explode(DS, $path);
		foreach($parts as $part){
			if($part=='.') continue;
			if($part=='..'){
				array_pop($res);
				continue;
			}
			$res[]=$part;
		}
		
		return implode(DS, $res); 
	}
	
	/**
	 * Replaces Slashes with 'DS' Constant 
	 * It strips junk slashes too.
	 * for example converts
	 * 	"c:/htdocs/arta\\packages\\user//" 
	 * to 
	 * 	"c:\htdocs\arta\packages\user\"
	 * when DS is defined as '\'
	 * It's good to clean junky Addresses
	 *
	 * @static
	 * @param	string	$path	Filepath
	 * @param	string	$separator	Directory Separator instead of DS.
	 * @return	string	Filepath
	 */
	static function replaceSlashes($path, $separator=DS){
		$rep=array('/','\\');
		if(DS==':'){
			$rep[]=':';
		}
		$r=str_replace($rep, DS, $path);
		while(strpos($r, DS.DS)!==false){
			$r=str_replace(DS.DS, DS, $r);	
		}
		if($separator!=DS){
			$r=str_replace(DS, $separator, $r);
		}
		
		return $r; 
	}

	/**
	 * Removes file Extension
	 *
	 * @static
	 * @param	string	$path	Filename or Filepath
	 * @return	string
	 */
	static function removeExt($path){
        if(strpos($path, '.')===false){
            return $path;
        }
		$subs=explode('.', $path);
		array_pop($subs);
		return implode($subs, '.');
	}
	
	/**
	 * Replaces invalid characters with '_' for naming a directory or file.
	 * 
	 * @static
	 * @param	string	$name	Name to make safe
	 * @return	string
	 */
	static function safeName($name){
		return str_replace(array('/','\\',':','*','?','"','<','>','|'), '_', $name);
	}
	
	/**
	 * Extracts filename from file path
	 *
	 * @static
	 * @param	string	$path	Filepath
	 * @return	string
	 */
	static function getFilename($path){
        $path=ArtaFile::replaceSlashes($path);

		$data=explode(DS,$path);
        return $data[count($data)-1];
	}

	/**
	 * Extracts file directory path. It means that this function removes file name from addresses
	 *
	 * @static
	 * @param	string	$path	Filepath
	 * @return	string
	 */
	static function getDir($path){
		$path=ArtaFile::replaceSlashes($path);

		$data=explode(DS,$path);
		array_pop($data);
		return implode(DS, $data);
	}

	/**
	 * Gets path relating to Arta Root dir. e.g. if "/home/some/www/arta/admin/index.php passed", "admin/index.php" will be returned.
	 *
	 * @static
	 * @param	string	$path	Filepath
	 * @param	bool	$force_slash	Replace ":" and "\" with "/"
	 * @return	string
	 */
	static function getRelatedPath($path, $force_slash=false){
		$dir=ARTAPATH_BASEDIR;
		if(strlen($path)==0) return;
		if(strpos($path, $dir)===0){
                $path = substr($path, strlen($dir));
        }
		while($path{0}=='/' || $path{0}=='\\' || $path{0}==':'){
            $path = substr($path,1);
        }

		if($force_slash==true){
			$path = str_replace(array('\\', ':'), '/', $path);
		}
		return $path;
	}

	/**
	 * Read file contents
	 *
	 * @static
	 * @param	string	$from	Filepath
	 * @return	string	File contents
	 */
	static function read($from){
		$from=self::replaceSlashes($from);
		if(file_exists($from)){
			
			$data=file_get_contents($from);
			if($data!==false){
				return $data;
			}else{
				//remove ARTAPATH_BASEDIR and pre-slashes
				$from=self::getRelatedPath($from, true);
				$data= self::ftp_read($from);
				return $data;
			}		
		}else{
			return false;
		}
	}

	/**
	 * Put file contents
	 *
	 * @static
	 * @param	string	$to	Filepath
	 * @param	string	$data	Contents to put
	 * @return	bool
	 */
	static function write($to, $data){
		$to=self::replaceSlashes($to);
		$dir=self::getDir($to);
		if(!is_dir($dir)){
			if(! self::mkdir_extra($dir)){return false;}
		}
		$f=@file_put_contents($to, $data);
		if($f!==false){
			return true;
		}else{
			//remove ARTAPATH_BASEDIR and pre-slashes
			$to=self::getRelatedPath($to, true);
			$res= self::ftp_write($to, $data);
			self::chmod($to, 0644);
			return $res;
		}
	}

	/**
	 * Read file contents via FTP
	 *
	 * @static
	 * @param	string	$from	Filepath
	 * @return	string	File contents
	 */
	static function ftp_read($from){
		$from=self::replaceSlashes($from);
		$ftp=self::getFTP();
		if($ftp!==false){
			//remove ARTAPATH_BASEDIR and pre-slashes
			$from=self::getRelatedPath($from, true);
			//check tmp dir existence
			if(! is_dir(ARTAPATH_BASEDIR.'/tmp/filetmp')){self::mkdir_extra(ARTAPATH_BASEDIR.'/tmp/filetmp');}
			// try to get file
            $tmpfile = ARTAPATH_BASEDIR.'/tmp/filetmp/'.self::getFilename($from);
			$data=$ftp->get($from, $tmpfile);
			if($data!==false){
				$v= file_get_contents($tmpfile);
				self::unlink($tmpfile);
				return $v;
			}else{
				return false;
			}		
		}else{
			return false;
		}
	}

	/**
	 * Put file contents via FTP
	 *
	 * @static
	 * @param	string	$to	Filepath
	 * @param	string	$data	Contents to put
	 * @return	bool
	 */
	static function ftp_write($to, $data){
		$to=self::replaceSlashes($to);
		$ftp=self::getFTP();
		if($ftp!==false){
			$to=self::getRelatedPath($to, true);
			$r=ArtaString::makeRandStr();
			
			if(! is_dir(ARTAPATH_BASEDIR.'/tmp/filetmp')){mkdir_extra(ARTAPATH_BASEDIR.'/tmp/filetmp');}
            $tmpfile = ARTAPATH_BASEDIR.'/tmp/filetmp/'.$r.'.tmp';
            $dir=self::getDir(ARTAPATH_BASEDIR.'/'.$to);
            if(!is_dir($dir)){
                if(! self::mkdir_extra($dir)){return false;}
            }
			if(!file_put_contents($tmpfile, $data)){return false;}
            if(file_exists(ARTAPATH_BASEDIR.'/'.$to)==true && self::unlink(ARTAPATH_BASEDIR.'/'.$to)==false){
                self::unlink($tmpfile);
                return false;
            }
            
			if($ftp->put($tmpfile, $to)){
				self::unlink($tmpfile);
				return true;
			}else{
				return false;
			}		
		}else{
			return false;
		}
	}

	/**
	 * Set file CHMOD
	 *
	 * @static
	 * @param	string	$path	Filepath
	 * @param	string	$mod	mod to apply
	 * @return	bool
	 */
	static function chmod($path, $mod){
		$path=self::replaceSlashes($path);
		if(file_exists($path)){
			@$res=chmod($path, $mod);
			if($res){
				return $res;
			}else{
				//remove ARTAPATH_BASEDIR and pre-slashes
				$path=self::getRelatedPath($path, true);
				$ftp=self::getFTP();
				if($ftp!==false){
					return $ftp->chmod($path, $mod);
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}

	/**
	 * Remove Directory
	 *
	 * @static
	 * @param	string	$path	Folder path
	 * @return	bool
	 */
	static function rmdir($path){
		$path=self::replaceSlashes($path);
		if(is_dir($path)){
			@$res=rmdir($path);
			if($res){
				return $res;
			}else{
				//remove ARTAPATH_BASEDIR and pre-slashes
				$path=self::getRelatedPath($path, true);
				$ftp=self::getFTP();
				if($ftp!==false){
					return $ftp->rmdir($path);
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}
	
	/**
	 * Remove Directory and subdirectories and files inside
	 *
	 * @static
	 * @param	string	$path	Folder path
	 * @return	bool
	 */
	static function rmdir_extra($path){
		$path=self::replaceSlashes($path);
		if(strlen($path)==0) return;
		if(self::rmdir_extra_safety($path)){
			return false;
		}
		$i=self::listDir($path);
		if(is_array($i) && count($i)>0){
			foreach($i as $f){
				if(is_file($path.'/'.$f)){
					self::unlink($path.'/'.$f);
				}elseif(is_dir($path.'/'.$f)){
					self::rmdir_extra($path.'/'.$f);
				}
			}
		}
		return self::rmdir($path);
	}
	
	/**
	 * Checks that is this path one of system directories that defined as a mistake to be deleted or not.
	 * @static
	 * @param	string	$path	Folder path
	 * @return	bool	true on mistake
	 */
	static function rmdir_extra_safety($path){
		$l=array(
				'admin',
				'admin/backup',
				'admin/help',
				'admin/imagesets',
				'admin/includes',
				'admin/languages',
				'admin/modules',
				'admin/packages',
				'admin/plugins',
				'admin/templates',
				'admin/tmp',
				'content',
				'crons',
				'imagesets',
				'includes',
				'languages',
				'library',
				'library/external',
				'media',
				'modules',
				'packages',
				'plugins',
				'templates',
				'tmp',
				'webservices',
				'widgets');
		$path=self::getRelatedPath($path, true);
		if(substr($path, -1, 1)==DS){
			$path= substr($path, 0, strlen($path)-1);
		}
		return in_array($path, $l);
	}

	/**
	 * Make Directory
	 *
	 * @static
	 * @param	string	$path	Folder path
	 * @return	bool
	 */
	static function mkdir($path){
		$path=self::replaceSlashes($path);
		@$res=mkdir($path);
		if($res){
			self::chmod($path, 0755);
			return $res;
		}else{
			//remove ARTAPATH_BASEDIR and pre-slashes
			$path=self::getRelatedPath($path, true);
			$ftp=self::getFTP();
			if($ftp!==false){
				$res=$ftp->mkdir($path);
				if($res){
					self::chmod($path, 0755);
				}
				return $res;
			}else{
				return false;
			}
		}
	}
	
	/**
	 * Makes Directory even if parent directory not exists.
	 *
	 * @static
	 * @param	string	$path	Folder path
	 * @param	string	$limit	Limit dir to go back.
	 * @return	bool
	 */
	static function mkdir_extra($path, $limit=null){
		$path=self::replaceSlashes($path);
		while(substr($path,-1,1)==DS){$path=substr($path,0,strlen($path)-1);}
		if(strlen($path)==0) return;
		$phases=explode(DS, $path);
        
		if($limit==null){
			$limit=ARTAPATH_BASEDIR;
		}
		$limit=self::replaceSlashes($limit);
		while(substr($limit,-1,1)==DS){$limit=substr($limit,0,strlen($limit)-1);}
		
		$v=$path;
				
		while(!is_dir($v)){
			if($v==$limit){
				return false;
			}
			array_pop($phases);
			$v=implode(DS, $phases);
		}
		
		$todo=substr($path, strlen($v)+1);
		if($todo==false){
			return true;
		}
		$todo=explode(DS, $todo);
		$str=$v.DS.$todo[0];
		while($str!==$path){
			self::mkdir($str);
			array_shift($todo);
			$str=$str.DS.$todo[0];
		}
		return self::mkdir($str);
	}


	/**
	 * Rename file
	 *
	 * @static
	 * @param	string	$path	File path
	 * @param	string	$to	Destination path
	 * @return	bool
	 */
	static function rename($path, $to){
		$path=self::replaceSlashes($path);
		$to=self::replaceSlashes($to);
		if(file_exists($path)){
            if(! is_dir(self::getDir($to))){self::mkdir_extra(self::getDir($to));}
			@$res=rename($path, $to);
			if($res){
				return $res;
			}else{
				//remove ARTAPATH_BASEDIR and pre-slashes
				$path=self::getRelatedPath($path, true);
				$to=self::getRelatedPath($to, true);
				$ftp=self::getFTP();
				if($ftp!==false){
					return $ftp->rename($path, $to);
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}

	/**
	 * Copy file
	 *
	 * @static
	 * @param	string	$path	File path
	 * @param	string	$to	Destination path
	 * @return	bool
	 */
	static function copy($path, $to){
		$path=self::replaceSlashes($path);
		$to=self::replaceSlashes($to);
		if(file_exists($path)){
            if(! is_dir(self::getDir($to))){self::mkdir_extra(self::getDir($to));}
			@$res=copy($path, $to);
			if($res){
				self::chmod($to, 0644);
				return $res;
			}else{
				//unfortunately, FTP protocol do not support copy command.
				$path=self::getRelatedPath($path, true);
				$to=self::getRelatedPath($to, true);
				$ftp=self::getFTP();
				$tmp=ARTAPATH_BASEDIR.'/tmp/'.ArtaString::makeRandStr().'.tmp';
				if($ftp!==false){
					$s=$ftp->get($path, $tmp);
					if($s==true){
						$res=$ftp->put($tmp, $to);
						self::delete($tmp);
						if($res){
							self::chmod($to, 0644);
						}
						return $res;
					}else{
						return false;
					}
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}

	/**
	 * Gets filesize
	 *
	 * @static
	 * @param	string	$path	File path
	 * @return	mixed	false on failure and int on success
	 */
	static function filesize($path){
		$path=self::replaceSlashes($path);
		if(file_exists($path)){
			@$res=filesize($path);
			if($res!==false ){
				return $res;
			}else{
				//remove ARTAPATH_BASEDIR and pre-slashes
				$path=self::getRelatedPath($path, true);
				$ftp=self::getFTP();
				if($ftp!==false){
					return $ftp->filesize($path);
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}

	/**
	 * Delete file
	 *
	 * @static
	 * @param	string	$path	File path
	 * @param	string	$dir	Delete if target is Directory?
	 * @param	string	$rmdir_extra	Remove directory with self::rmdir_extra() ?
	 * @return	bool
	 */
	static function delete($path, $dir=false, $rmdir_extra=false){
		$path=self::replaceSlashes($path);
		if(file_exists($path) && !is_dir($path)){			
			@$res=unlink($path);
			if($res){
				return $res;
			}else{
				//remove ARTAPATH_BASEDIR and pre-slashes
				$path=self::getRelatedPath($path, true);
				$ftp=self::getFTP();
				if($ftp!==false){
					return $ftp->delete($path);
				}else{
					return false;
				}
			}
		}elseif(is_dir($path) && $dir==true){
			return $rmdir_extra ? self::rmdir_extra($path) : self::rmdir($path);
		}else{
			return false;
		}
	}

	/**
	 * Refer to delete()
	 * @static
	 * @param	string	$path	File path
	 * @param	string	$dir	Delete if target is Directory?
	 * @param	string	$rmdir_extra	Remove directory with self::rmdir_extra() ?
	 * @return	bool
	 */
	static function unlink($path, $dir=false, $rmdir_extra=false){
		return self::delete($path, $dir, $rmdir_extra);
	}

	/**
	 * Get Directory contents
	 *
	 * @static
	 * @param	string	$path	dir path
	 * @return	mixed	false on failure and array on success
	 */
	static function listDir($path){
		$path=self::replaceSlashes($path);
		if(is_dir($path)){
			@$d = dir($path);
			if(!$d===false){
				$known=array();
				while (false !== ($file = $d->read())) {
					if($file !== '.' && $file !== '..'){
					$known[] = $file;
					}
				}
				$d->close();
				return $known;
			}else{
				$ftp=self::getFTP();
				if($ftp!==false){
					$path=self::getRelatedPath($path, true);
					$data=$ftp->nlist($path);
					foreach($data as $k => $v){
						$data[$k]=str_replace($path, '', $v);
					}
					return $data;
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}

	/**
	 * Get file modified timestamp
	 *
	 * @static
	 * @param	string	$path	File path
	 * @return	mixed	false on failure and int on success
	 */
	static function getModified($path){
		$path=self::replaceSlashes($path);
		if(file_exists($path)){
			@$res=filemtime($path);
			if($res){
				return $res;
			}else{
				//remove ARTAPATH_BASEDIR and pre-slashes
				$path=self::getRelatedPath($path, true);
				$ftp=self::getFTP();
				if($ftp!==false){
					return $ftp->mdtm($path);
				}else{
					return false;
				}
			}
		}else{
			return false;
		}
	}

}

?>
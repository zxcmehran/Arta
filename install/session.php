<?php
class ArtaInstallerSessionHandler {

	static function open($save_path, $session_name)
	{
		if(!is_dir(ROOTDIR.'/tmp') AND !mkdir(ROOTDIR.'/tmp')) return false;
		return true;
	}
	
	static function close()
	{
		return true;
	}

	static function read($id)
	{
		@include(ROOTDIR.'/tmp/'.$id.'.php');
		if(!isset($data)){$data='';}	
		return (string)$data;
	}
	
	static function write($id, $session_data) {
		@chmod(ROOTDIR.'/tmp', 0755);
		$f = file_put_contents(ROOTDIR.'/tmp/'.$id.'.php', 
		"<?php \$data=base64_decode(\"".base64_encode($session_data)."\");?>");
		
		@chmod(ROOTDIR.'/tmp/'.$id.'.php', 0644);
		return $f;
		
	}

	static function destroy($id)
	{
		if($id == session_id()){
			session_unset();
		}
		$s = @unlink(ROOTDIR.'/tmp/'.$id.'.php');
		return $s;
	}

	static function gc($max)
	{
		$d = dir(ROOTDIR.'/tmp');
		$known=array();
		while (false !== ($file = $d->read())) {
			if($file !== '.' && $file !== '..'){
			$known[] = $file;
			}
		}
		$d->close();
		
		foreach($known as $f){
			$p=ROOTDIR.'/tmp/'.$f;
			if(is_file($p) && @filemtime($p)<(time()-$max)){
				unlink($p);
			}
		}
		return true;
	}
}

ini_set('session.save_handler', 'user');
session_set_save_handler(
	array('ArtaInstallerSessionHandler', 'open'),
	array('ArtaInstallerSessionHandler', 'close'),
	array('ArtaInstallerSessionHandler', 'read'),
	array('ArtaInstallerSessionHandler', 'write'),
	array('ArtaInstallerSessionHandler', 'destroy'),
	array('ArtaInstallerSessionHandler', 'gc')
	);

register_shutdown_function("session_write_close");

?>
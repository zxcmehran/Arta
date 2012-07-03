<?php
if(!defined('ARTA_VALID')){die('No access');}
class ToolsModelCache extends ArtaPackageModel{
	
	function __construct(){
		$cache_items=ArtaFile::listDir(ARTAPATH_BASEDIR.'/tmp/cache');
		if(!is_array($cache_items)){
			$cache_items=array();
		}
		$data=array();
		foreach($cache_items as $c){
			$x=array('size'=>ArtaFile::filesize(ARTAPATH_BASEDIR.'/tmp/cache/'.$c), 'name'=>$c, 'client'=>'site');
			if(ArtaFile::delete(ARTAPATH_BASEDIR.'/tmp/cache/'.$c)==true){
				$x['del']=true;
			}else{
				$x['del']=false;
			}
			$data[]=$x;
		}
		$cache_items=ArtaFile::listDir(ARTAPATH_ADMIN.'/tmp/cache');
		if(!is_array($cache_items)){
			$cache_items=array();
		}

		foreach($cache_items as $c){
			$x=array('size'=>ArtaFile::filesize(ARTAPATH_ADMIN.'/tmp/cache/'.$c), 'name'=>$c, 'client'=>'admin');
			if(ArtaFile::delete(ARTAPATH_ADMIN.'/tmp/cache/'.$c)==true){
				$x['del']=true;
			}else{
				$x['del']=false;
			}
			$data[]=$x;
		}
		$this->data=$data;
	}
	
	function getResult(){
		return $this->data;
	}

}
?>
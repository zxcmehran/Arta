<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/4/3 19:57 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

class LinksModelLink_editor extends ArtaPackageModel{
	
	function getD(){
		$dir=ArtaFile::listDir(ARTAPATH_BASEDIR.'/packages');
		foreach($dir as $k=>$v){
			if(is_dir(ARTAPATH_BASEDIR.'/packages/'.$v)==false|| in_array($v, array('xmlrpc','captcha'))){
				unset($dir[$k]);
			}
		}
		$db=ArtaLoader::DB();
		$dir=array_map(array($db, 'Quote'),$dir);
		$db->setQuery('SELECT title,name FROM #__packages WHERE name IN('.implode(',', $dir).')');
		$r=$db->loadObjectList();
		if($r==null){
			$r=array();
		}
		
		$x=array();
		$l=ArtaLoader::Language();
		foreach($r as $k=>$v){
			$types=array();
			@include(ARTAPATH_BASEDIR.'/packages/'.$v->name.'/link_editor.php');
			
			if(
			is_file(ARTAPATH_BASEDIR.'/packages/'.$v->name.'/link_editor.php') &&
			function_exists(ucfirst($v->name).'LinkTypes') && 
			function_exists(ucfirst($v->name).'LinkControls')){
				$l->addtoNeed($v->name, 'package');
				$func=ArtaString::removeIllegalChars($v->name, array_merge(range('a','z'),range('0','9'), array('_'))).'LinkTypes';
				foreach($func() as $typek=>$type){
					$types[$typek]=$type;
				}
			}
			$x[ArtaString::stick(array($v->name, $v->title), '|', true)]=$types;
		}
		
		return $x;
	}
	
	function getData(){
		$v=getVar('code', '', '', 'string');
		$x=@ArtaString::split(base64_decode($v),'|',false, true);
		if(count($x)!==4){
			ArtaError::show();
		}
		$l=ArtaLoader::Language();
		if(is_dir(ARTAPATH_BASEDIR.'/packages/'.$x[0]) && is_file(ARTAPATH_BASEDIR.'/packages/'.$x[0].'/link_editor.php')){
			@include(ARTAPATH_BASEDIR.'/packages/'.$x[0].'/link_editor.php');
			$func=ucfirst($x[0]).'LinkControls';
			if(function_exists($func)){
				$l->addtoNeed($x[0], 'package');
				$func2=ucfirst($x[0]).'LinkTypes';
				$dat=$func2();
				$this->title=$x[3].' > '.$dat[$x[1]]['title'];
				
				$default=array();
				$func=ArtaString::removeIllegalChars($func, array_merge(range('a','z'),range('0','9'), array('_')));
				eval('$res=array(\'link\'=>$x[2],\'control\'=>'.$func.'($x[1], $default));');
				$assign='';
				foreach($default as $defk=>$defv){
					$assign.='assign(\''.$defk.'\', \''.$defv.'\');';
				}
				ArtaTagsHtml::addtoTmpl('<script>'.$assign.'</script>', 'beforebodyend');
				return $res;
			}else{
				ArtaError::show();
			}
		}else{
			ArtaError::show();
		}
	}
	
}

?>
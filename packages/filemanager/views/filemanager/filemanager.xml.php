<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/2/14 17:41 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class FilemanagerViewFilemanager extends ArtaPackageView{
	// '/sadasd'
	function displayFilemanager(){
		$this->setLayout('response');
		$this->render();
	}
	
	function tree(){
		$path=base64_decode(getVar('url', base64_encode('/')));
		$path=str_replace('..','',$path);
		$list=ArtaFile::listDir(ARTAPATH_BASEDIR.'/content/'.$path);
		
		$r='';
		if(is_array($list)){
			asort($list);
			$r.='<response>';
			foreach($list as $k=>$v){
				if(is_dir(ARTAPATH_BASEDIR.'/content/'.$path.$v)){
					$r .='<li><a href="#handle" onclick="setURL(\''.JSValue($path.$v.'/',true).'\');">'.htmlspecialchars($v).'</a><ul style="padding-left: 8px; margin-left: 8px; direction:ltr; text-align: left; list-style: url('.makeURL(Imageset('folder_small.png')).');" class="subtree'.(getVar('c','1', 'get', 'int')+1).'" id="fm_tree_'.htmlspecialchars($path.$v).'/'.'"></ul></li>';
				}
			}
			$r.='</response>';
		}else{
			$r ='<response></response>';
		}
		$this->assign('resp', $r);
		$this->displayFilemanager();
	}
	
	function content(){
		$mod=getVar('editor',false, '', 'bool');
		$path=base64_decode(getVar('url', base64_encode('/')));
		$path=str_replace('..','',$path);
		$list=ArtaFile::listDir(ARTAPATH_BASEDIR.'/content/'.$path);
		
		$r='';
		if(is_array($list) AND count($list)>0){
			$ord=array();
			foreach($list as $v){
				if($v=='.htaccess'){
					continue;
				}
				$ord[(int)is_dir(ARTAPATH_BASEDIR.'/content/'.$path.$v)][]=$v;
			}
		@	asort($ord[0]);
		@	asort($ord[1]);
		@	$ord[0]=(array)$ord[0];
		@	$ord[1]=(array)$ord[1];
			$list=@(array)array_merge($ord[1],$ord[0]);
	

			$r .='<table>';
			foreach($list as $k=>$v){
				$img=$path.$v;
				$x=ArtaFile::getExt($img);
				$x=strtolower($x);
				$url='index.php?pack=filemanager&type=jpg&img='.base64_encode($img);
				if($x!=='jpeg'&&$x!=='jpg'&&$x!=='gif'&&$x!=='png'){
					$img='';
					$x='raw';
					$url=Imageset('file.png');
					$link='<a href="content'.$path.$v.'" target="_blank">'.$v.'</a>';
				}else{
					$link='<a href="'.htmlspecialchars(makeURL('content'.$path.$v)).'" onclick="window.open(\''.JSValue(makeURL('content'.$path.$v), true).'\', \'MyWIN\', \'scrollbars,resizable\');return false;">'.htmlspecialchars($v).'</a>';
				}
				if($mod==1){
					$link='<a href="#handle" onclick="w=window.opener==null?window.parent:window.opener;w.SetUrl( \''.JSValue(makeURL('content'.$path.$v),true).'\' );window.close();">'.htmlspecialchars($v).'</a>';
				}
				
				$size=round(filesize(ARTAPATH_BASEDIR.'/content/'.$path.$v)/1024);
				$size=$size==0 ? 1 : $size;
				$size.=' KB';
				
				if(is_dir(ARTAPATH_BASEDIR.'/content/'.$path.$v)){
					$link='<a href="#handle" onclick="setURL(\''.JSValue($path.$v, true).'/\');">'.htmlspecialchars($v).'</a>';
					$img='/';
					$x='png';
					$size='';
					$url=Imageset('folder.png');
				}
				if($size){
					$size='<br /><font color="gray">'.$size.'</font>';
				}
				$mDate='<br /><font color="gray">'.ArtaDate::_(@filemtime(ARTAPATH_BASEDIR.'/content/'.$path.$v)+ArtaDate::getOffset()).'</font>';
				$url=makeURL($url);
				$r .='<tr><td><img style="cursor:pointer;" src="'.Imageset('false.png').'" onclick="DeleteFile(\''.JSValue($path.$v, true).'\',\''.JSValue($path, true).'\')" title="'.trans('DELETE').'"/><br/><img style="cursor:pointer;" width="16" height="16" src="'.Imageset('edit_small.png').'" onclick="RenameFile(\''.JSValue($path.$v, true).'\',\''.JSValue($path, true).'\', \''.JSValue($v, true).'\')" title="'.trans('RENAME').'" /></td><td rowspan="2" style="width:80px;border:1px solid gray;text-align:center"><img src="'.htmlspecialchars($url).'" /></td><td rowspan="2">'.$link.$size.$mDate.'</td></tr><tr><td></td></tr>';
			}
			$r .='</table>';
		}else{
			$r.= '<center><b>'.trans('NO RESULTS').'</b></center>';
		}
		
		$this->assign('resp', $r);
		$this->displayFilemanager();
	}
	
	function delete(){
		if(ArtaSession::checkToken()==false){
			ArtaError::show(400);
		}
		if(ArtaUsergroup::getPerm('can_upload_files', 'package', 'filemanager')==false){
			ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
		}
		$path=base64_decode(getVar('url', base64_encode(''), '', 'string'));
		$path=str_replace('..','',$path);
		if(is_null($path) || $path == '/.htaccess' || $path == '.htaccess'){
			ArtaError::show(500);
		}else{
			if(!ArtaFile::delete(ARTAPATH_BASEDIR.'/content/'.$path, true, true)){
				ArtaError::show(500);
			}
		}
		$r ='<response></response>';
		$this->assign('resp', $r);
		$this->displayFilemanager();
	}
	
	function rename(){
		if(ArtaSession::checkToken()==false){
			ArtaError::show(400);
		}
		if(ArtaUsergroup::getPerm('can_upload_files', 'package', 'filemanager')==false){// editing files
			ArtaError::show(403, trans('YOU ARE NOT AUTHORIZED'));
		}
		$path=base64_decode(getVar('url', base64_encode(''), '', 'string'));
		$nname=base64_decode(getVar('newname', base64_encode(''), '', 'string'));
		$nname=ArtaFilterinput::clean($nname, 'filename');
		$pth = ArtaFile::getDir($path);
		if(is_null($path) || $path == '/.htaccess' || $path == '.htaccess'){
			ArtaError::show(500);
		}
		if(!ArtaFile::rename(ARTAPATH_BASEDIR.'/content/'.$path, ARTAPATH_BASEDIR.'/content/'.$pth.'/'.$nname)){
			ArtaError::show(500);
		}
		$r ='<response></response>';
		$this->assign('resp', $r);
		$this->displayFilemanager();
	}
}

?>
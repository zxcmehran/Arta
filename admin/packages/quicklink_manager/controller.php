<?php
if(!defined('ARTA_VALID')){die('No access');}
class Quicklink_managerController extends ArtaPackageController{
	
	function display(){
		$view=$this->getView(getVar('view', 'links', '', 'string'));
		$view->display();
	}
	
	function reorder(){
		if(ArtaUsergroup::getPerm('can_edit_quicklinks_order', 'package', 'quicklink_manager')==false){
			ArtaError::show(403, trans('YOU CANNOT EDIT QUICKLINK ITEMS ORDER'));
		}
		
		if(ArtaSession::checkToken()==false){
			ArtaError::show(403, trans('INVALID TOKEN'));
		}
		
		$vars=ArtaRequest::getVars();
		if(!isset($vars['pos'])){
			ArtaError::show(500);
		}
		if((int)@$vars['pos']<0){
			ArtaError::show(500);
		}
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT * FROM #__quicklink WHERE id='.$db->Quote(getVar('id', 0, '', 'int')));
		$mod=$db->loadObject();
		if($mod==null){
			ArtaError::show();
		}
		$db->setQuery('SELECT * FROM #__quicklink ORDER BY `order`');
		$mods=$db->loadObjectList();
		foreach($mods as $k=>$v){
			if((int)$v->order!==$k){
				$incorrect=true;
				break;
			}
		}
		if(isset($incorrect)){
			foreach($mods as $k=>$v){
				$db->setQuery('UPDATE #__quicklink SET `order`='.$db->Quote($k).' WHERE id='.$db->Quote($v->id), array('order'));
				$db->query();
			}
		}
		if($vars['pos']>$mod->order){
			$x=$mod->order+1;
			$y=$vars['pos'];
			$ph='`order`=`order`-1';
		}else{
			$y=$mod->order-1;
			$x=$vars['pos'];
			$ph='`order`=`order`+1';
		}
		$db->setQuery('UPDATE #__quicklink SET '.$ph.' WHERE `order` BETWEEN '.$db->Quote($x).' AND '.$db->Quote($y), array('order'));
		$res = $db->query();
		$db->setQuery('UPDATE #__quicklink SET `order`='.$db->Quote($vars['pos']).' WHERE id='.$db->Quote($vars['id']), array('order'));
		if($res && $db->query()){
			redirect('index.php?pack=quicklink_manager', trans('MOVED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'), 'index.php?pack=quicklink_manager');
		}
	}

	function save(){
		if(ArtaUsergroup::getPerm('can_addedit_quicklinks', 'package', 'quicklink_manager')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT QUICKLINK ITEMS'));
		}
		
		$vars =ArtaRequest::getVars('post');
		
		$vars=ArtaUtility::array_extend($vars, array('title'=>'','link'=>'','alt'=>'','img'=>'file.png','key'=>'','key_ctrl'=>'','key_shift'=>'','key_alt'=>'', 'relative'=>0));
		
		$vars=ArtaFilterinput::clean($vars, array('title'=>'string','link'=>'string','alt'=>'string','img'=>'string','key'=>'string','key_ctrl'=>'bool','key_alt'=>'bool','key_shift'=>'bool', 'relative'=>'bool'));
		
		
		$vars=ArtaFilterinput::trim($vars);
		$vars=ArtaFilterinput::array_limit($vars, array('title'=>255, 'link'=>255,'alt'=>255,'img'=>254));
		
		if($vars['relative']==false){
			$vars['img']='#'.$vars['img'];
		}
		
		if(@(int)$vars['id']>0){
			$q='index.php?pack=quicklink_manager&view=new&id='.(int)$vars['id'];
		}else{
			$q='index.php?pack=quicklink_manager&view=new';
		}
		if((string)$vars['title']=='' || (string)$vars['link']==''||(string)$vars['img']==''){
			redirect($q, trans('FORM ISNT COMPLETE'), 'error');
		}
		if(strlen($vars['key'])>0){
			$vars['key']=$vars['key']{0};
		}
		$key='';
		
		if((string)$vars['key']!==''){
			if($vars['key_ctrl']){
				$key .='ctrl+';
			}
			if($vars['key_shift']){
				$key .='shift+';
			}
			if($vars['key_alt']){
				$key .='alt+';
			}
			$key .=strtolower($vars['key']);	
		}
		
		$db=ArtaLoader::DB();
		if(@(int)$vars['id']>0){
			$db->setQuery('SELECT * FROM #__quicklink WHERE id='.$db->Quote((int)$vars['id']));
			$row=$db->loadObject();
			if($row==null){
				ArtaError::show(404);
			}
			
			$db->setQuery('UPDATE #__quicklink SET `title`='.$db->Quote($vars['title']).', `link`='.$db->Quote($vars['link']).', `alt`='.$db->Quote($vars['alt']).', `img`='.$db->Quote($vars['img']).', `acckey`='.$db->Quote($key).' WHERE id='.$row->id);
			
		}else{
			
			$db->setQuery('SELECT MAX(`order`) FROM #__quicklink');
			$m=(int)$db->loadResult()+1;
			$db->setQuery('INSERT INTO #__quicklink VALUES (NULL, '.$db->Quote($vars['title']).','.$db->Quote($vars['link']).', '.$db->Quote($vars['alt']).', '.$db->Quote($vars['img']).', '.$db->Quote($key).', '.$db->Quote($m).')');
			
		}
		
		$r=$db->query();
		if($r){
			redirect('index.php?pack=quicklink_manager', trans('LINK ADDED/UPDATED SUCC'));
		}else{
			ArtaError::show(500, 'Error in DB');
		}
		
		
	}

	function delete(){
		if(ArtaUsergroup::getPerm('can_delete_quicklinks', 'package', 'quicklink_manager')==false){
			ArtaError::show(403, trans('YOU CANNOT DELETE QUICKLINK ITEMS'));
		}
		$id=getVar('id',false,'post','int');
		if($id==false){redirect('index.php?pack=quicklink_manager');}
		$db=ArtaLoader::DB();
		$db->setQuery('DELETE FROM #__quicklink WHERE id='.$db->Quote($id));
		$r=$db->query();
		if($r){
			redirect('index.php?pack=quicklink_manager', trans('DELETED SUCC'));
		}else{
			ArtaError::show(500, trans('ERROR IN DB'));
		}
	}

}
?>
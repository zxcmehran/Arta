<?php
if(!defined('ARTA_VALID')){die('No access');}
class LinksViewLink_editor extends ArtaPackageView{
	
	function display(){
		$m=$this->getModel();
		
		if(getVar('code', false, '', 'string')==false){
			$data=$m->getD();
			$this->assign('var', $data);
			$this->setTitle(trans('LINK EDITOR'));
		}else{
			$data=$m->getData();
			$this->assign('var', $data);
			if(isset($m->title)){
				$this->setTitle(trans('LINK EDITOR').': '.$m->title);
			}else{
				$this->setTitle(trans('LINK EDITOR'));
			}
		}
		$this->render();
	}

}
?>
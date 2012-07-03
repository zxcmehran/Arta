<?php
if(!defined('ARTA_VALID')){die('No access');}
class DomainsViewEdit extends ArtaPackageView{
	
	function display(){
		ArtaAdminButtons::addSave('', 'if($("packerr").style.display!="none"){return false;}if(!confirm("'.trans('ARE U SURE').'"+"\n\n"+$("preview").innerHTML.unescapeHTML())){return false;}');
		ArtaAdminButtons::addCancel();
		
		$m=$this->getModel();
		$d=$m->getData();
		$this->assign('data',$d);
		if((int)$d->id==0){
			$this->setTitle(trans('ADD DOMAIN'));
		}else{
			$this->setTitle(trans('EDIT DOMAIN').': '.htmlspecialchars($d->address));
		}
		ArtaAdminTips::addTip(trans('ADDEDIT TIP'));
		$this->assign('packages',$m->getSitePackages());
		$this->assign('upackages',$m->getUnusablePackages());
		$this->render();
	}

}
?>
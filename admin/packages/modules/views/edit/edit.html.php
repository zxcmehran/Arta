<?php
if(!defined('ARTA_VALID')){die('No access');}
class ModulesViewEdit extends ArtaPackageView{
	
	function display(){
		ArtaRequest::addVar('tmpltest', 'simple');
		ArtaAdminButtons::addSave();
		ArtaAdminButtons::addCancel();
		ArtaAdminTips::addTip(trans('ADD/EDIT MODULE TIP'));
		$this->setTitle(trans('ADD/EDIT MODULE'));
		$m=$this->getModel();
		$data = $m->getData();
		if($data->id>0 AND !isset($data->linkviewer) AND (string)$data->module!=''){
			ArtaAdminButtons::addSetting($data->module, 'module');
		}
		$this->assign('data',$data);
		$this->assign('packs',$m->getPacks());
		$this->assign('mods',$m->getMods());
		$this->assign('groups',$m->getGroups());
		ArtaTagsHtml::addHeader('<script>
		new PeriodicalExecuter(function(pe) {
			new Ajax.Request(client_url+\'index.php?pack=blog&view=new&type=xml&keep_alive=1\', {method: \'get\'});
		}, 300);
		</script>');
		$this->render();
	}

}
?>
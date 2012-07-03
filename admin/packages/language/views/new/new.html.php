<?php
if(!defined('ARTA_VALID')){die('No access');}
class LanguageViewNew extends ArtaPackageView{
	
	function display(){
		if(ArtaUsergroup::getPerm('can_addedit_translations','package','language')==false){
			ArtaError::show(403, trans('YOU CANNOT ADDEDIT TRANSLATIONS'));
		}
		
		$id=getVar('id', null, null, 'int');
		$group=getVar('group', null, null, 'filename');
		$lang=getVar('lang', null, null, 'int');
		$path=ArtaLoader::Pathway();
		
		$model=$this->getModel();
		
		$title=trans('EDIT TRANSLATION');
		$this->setTitle($title);
		
		$this->assign('data', $this->makeTable($model->getRow($id, $group, $lang)));
		$this->assign('lang', $model->lang);
		ArtaAdminButtons::addSave();
		ArtaAdminButtons::addCancel();
		ArtaTagsHtml::addHeader('<script>
		new PeriodicalExecuter(function(pe) {
			new Ajax.Request(client_url+\'index.php?pack=blog&view=new&type=xml&keep_alive=1\', {method: \'get\'});
		}, 300);
		</script>');
		$this->render();
	}
	
	function makeTable($rows){
		$r='<table class="admintable">';
		$i=0;
		foreach($rows as $row){
			$r.='<tr class="row'.$i.'"><td>';
			$r.='<b>'.htmlspecialchars($row['title']).'</b>';
			$r.='<p><table style="min-width:98%;"><tr><td style="width:125px;"><b>'.trans('ORIGINAL').':</b></td><td> <div style="border:1px solid #bbbbbb; padding: 5px;">'.$row['defaultvalue'].'</div></td></tr>';
			$r.='<tr><td style="width:125px;"><b>'.trans('TRANSLATED').':</b></td><td> <div class="translated_data">'.ArtaTagsHtml::PreformItem($row['name'],$row['value'],$row['type'],'$default=unserialize(base64_decode("'.base64_encode(serialize($row['default'])).'"));$defaultvalue=unserialize(base64_decode("'.base64_encode(serialize($row['defaultvalue'])).'"));'.$row['typedata']).'</div></td></tr>';

			$r.='</table></p></td></tr>';
			$i= $i==0 ? 1:0;
			
		}
		$r.='</table>';
		return $r;
	}
	
	

}
?>
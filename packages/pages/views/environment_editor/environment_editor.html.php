<?php 
if(!defined('ARTA_VALID')){die('No access');}
class PagesViewEnvironment_editor extends ArtaPackageView{

	function display(){
		$this->m=$this->getModel();
		$wid=$this->m->getWidget();
		$this->assign('data', $wid);
		$this->setTitle(trans('WIDGET EDITOR').': '.(isset($wid->new) ? trans('NEW WIDGET'): $wid->title));
		ArtaTagsHtml::addHeader('<script>
		new PeriodicalExecuter(function(pe) {
			new Ajax.Request(client_url+\'index.php?pack=blog&view=new&type=xml&keep_alive=1\', {method: \'get\'});
		}, 300);
		</script>');
		$this->render();
	}
	
	function getSettings(){
		$sets=$this->m->getSettings();
		$r='<table width="100%">';
		$lang=ArtaLoader::Language();
		foreach($sets as $k=>$v){
			if($lang->exists('WIDSET_'.$v->var.'_DESC')){
				$desc=trans('WIDSET_'.$v->var.'_DESC');
			}else{
				$desc='';
			}
			$r.='<tr><td class="label">'.ArtaTagsHtml::Tooltip(trans('WIDSET_'.$v->var.'_LABEL'), $desc).'</td><td class="value">'.ArtaTagsHtml::PreFormItem('settings['.$v->var.']', $v->value, $v->vartype, $v->vartypedata).'</td></tr>';
		}
		$r.='</table>';
		return $r;
		
	}
	
	function getWidgets(){
		$wids=$this->m->getWidgets();
		$r='';
		foreach($wids as $k=>$v){
			$r.='<option';
			if($v->id==$this->m->widget->widget){
				$r.=' selected="selected"';
			}
			$r.=' value="'.$v->id.'">'.htmlspecialchars($v->title).'</option>';
		}
		return $r;
	}
	
}
?>
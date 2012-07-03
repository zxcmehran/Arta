<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewNewcat extends ArtaPackageView{
	
	function display(){
		// @TODO add sef_alias for category too.
		if(ArtaUsergroup::getPerm('can_addedit_categories', 'package', 'blog')){
			$ids=getVar('ids', false, '', 'array');
			
			if($ids==false){$title=trans('NEW CATEGORY');}else{$title=trans('EDIT CATEGORY');}
			
			$this->setTitle($title);
			
			$model=$this->getModel();
			$x=$model->getCat($ids);
			$this->assign('cat', $x);
			$cats=$model->getCategories();
			$this->assign('cats', $cats);
			$pc=$model->getCats($x);
			$this->assign('pc', $this->makeUGList($pc,$x, $model->fromParents));
			
			ArtaAdminButtons::addSave(array('task'=>'saveCat'));
			ArtaAdminButtons::addCancel();
	
			$this->render();
		}else{
			redirect('index.php?pack=blog', trans('YOU CANNOT ADDEDIT BLOG CATEGORIES'), 'error');
		}
	}
	
	function makeUGList($pc, $x, $def){
		$UG = ArtaUsergroup::getItems();
		$pc = explode(',',$pc);
		$px = explode(',',$x->accmask);
		$pd = explode(',',$def);

		$r='<a name="accmasks">&nbsp;</a>';
		if(count($pd)!=0 && strlen($def)){
			$r.= '<div style="color:orange;text-align:center">'.trans('DEFAULT ACCMASK IS FROM PARENT').'</div>';
		}
		$r.='<table>';
		foreach($UG as $g){
			$r.='<tr><td>'.$g->title.'</td><td>'.ArtaTagsHtml::radio('ugs['.$g->id.']',
			 array(trans('NO'), trans('YES'), trans('AMNC')), !in_array($g->id,$px)&&!in_array('-'.$g->id,$px) ? 2 : (int)in_array($g->id,$pc)).' <span style="color:gray;">'.trans('DEFAULT').': '.(in_array($g->id, $pd) ? trans('YES') : trans('NO')).'</span></td></tr>';
		}
		$r.='</table>';
		return $r;
		
	}

}
?>
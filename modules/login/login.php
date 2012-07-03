<?php 
if(!defined('ARTA_VALID')){die('No access');}
$h = $this->getHelper();

$layout=$this->getSetting('view_layout', '0');
if($layout==2){
	$layout='3';
}elseif($layout==true){
	$layout='2';
}else{
	$layout='';
}

switch($h->getState()){
	case true:
		$this->assign('username', $this->getSetting('show_username'));
		$this->assign('greeting', $this->getSetting('greeting_msg'));
		$this->setLayout('logout'.$layout);
		$this->render();
	break;
	case false:
		$this->assign('prefix', $this->getSetting('module_prefix_txt'));
		$this->assign('suffix', $this->getSetting('module_suffix_txt'));
		$this->assign('redirect', $this->getSetting('redirect_to_location'));
		$this->setLayout('login'.$layout);
		$this->render();
	break;
}

?>
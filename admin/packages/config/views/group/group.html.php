<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ConfigViewGroup extends ArtaPackageView{
	
	function display(){
		$this->setTitle(trans('CONFIGURATION GROUPS'));
		$vars=array(array('config', trans('c_config'), 'view=config'),
			array('apply', trans('c_defaults'), 'view=defaults'),
			array('package', trans('c_package'), 'view=extension&extype=package'),
			array('module', trans('c_module'), 'view=extension&extype=module'),
			array('plugin', trans('c_plugin'), 'view=extension&extype=plugin'),
			array('template', trans('c_template'), 'view=extension&extype=template'),
			array('cron', trans('c_cron'), 'view=extension&extype=cron'));
		$this->assign('groups', $vars);
		$this->render();
	}

}
?>
<?php
if(!defined('ARTA_VALID')){die('No access');}
/**
 * Config Language Translation Handler.
 */
class LanguageTranslationConfig implements LanguageTranslation{
	
	function getIDRowName(){
		return 'id';
	}
	
	function getTitleRowName(){
		return 'title';
	}

	function getTitle(){
		return trans('SYSCONFIG');
	}
	

	function getRows(){
		$x=array('site_name','homepage_title','description', 'keywords', 'time_format', 'cal_type', 'offline_msg');
		$config=new ArtaConfig;
		$this->addLanguage();
		
		$data=array();
		$i=0;
		foreach($x as $k=>$v){
			$data[$i]=new stdClass;
			$data[$i]->id=$k+1;
			$data[$i]->title=trans($v);
			$data[$i]->value=$config->$v;
			$i++;
		}

		return $data;
	}
	
	function getRowsCount(){
		return 7;
	}
	

	function getControls(){

	}

	function addLanguage(){
		$l=ArtaLoader::Language();
		$l->addtoNeed('config','package');
	}
	
	function getRow($id){
		$this->addLanguage();
		$x=array(1=>'site_name',2=>'homepage_title',3=>'description', 4=>'keywords', 5=>'time_format', 6=>'cal_type', 7=>'offline_msg');
		$config=new ArtaConfig;
		
		if(isset($x[$id])){
			$data=new stdClass;
			$data->id=$id;
			$data->title=trans($x[$id]);
			$data->value=$config->{$x[$id]};
			return $data;
		}else{
			return null;
		}

	}

	function getRowExistence($id){
		return (in_array($id, range(1,7)));
	}
	
	function checkInput($v, $row){
		if($row->id==5){
			$v['title']=ArtaUTF8::substr($v['value'], 0, 20).'...';
		}else{
			$v['title']=$v['value'];
		}
		
		return $v;
	}

}
?>
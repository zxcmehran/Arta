<?php
if(!defined('ARTA_VALID')){die('No access');}

class SearchModelSearch extends ArtaPackageModel{
	
	function getPlugins(){
		$p=ArtaLoader::Plugin();
		$r=$p->trigger('onGetSearchLocations', array());
		$res=array();
		foreach($r as $k=>$v){
			if(is_array($v)){
				$res=array_merge($res, $v);
			}else{
				$res[]=$v;
			}
		}
		return $res;
	}
	
	function getRes(){
		$ph=getVar('phrase', null, '', 'string');
		$ph=trim($ph);
		if($ph==null){
			return null;
		}
		ArtaRequest::addVar('phrase', $ph);
		if($ph!==null && strlen($ph)<2){
			return false;
		}
		
		$p=ArtaLoader::Plugin();
		$order=getVar('order', 'popular', '', 'string');
		if(!in_array($order, array('newest','oldest','popular','alpha'))){
			$order='popular';
		}
		$dbg=ArtaLoader::Debug();
		$this->past=$dbg->getMicrotime();
		$ret=$p->trigger('onSearch', array($ph, getVar('match_type', 0, '','int'), $order));
		$this->past=$dbg->getMicrotime() - $this->past;
		$res=array();
		foreach($ret as $k=>$v){
			if(is_array($v)){
				$res=array_merge($res, $v);
			}else{
				$res[]=$v;
			}
		}
		$at=getVar('at', false, '', 'array');
		if($at!==false){
			foreach($res as $k=>$v){
				if(!@isset($at[$v->name])){
					unset($res[$k]);
				}
			}
		}
		
		/* its written to avoid being blog results at first and then pages and ... for everytime.
		it makes it as blog,pages,some,blog,pages,some instead of blog,blog,pages,pages,some,some
		note that ordering is random as you see in 
		
			$keyz=array_keys($xxxx);
			shuffle($keyz);
			
	 	*/
	 	
	 	$_res=$res;
	 	// $xxxx=array('blog'=>array(...), 
		// 		'pages'=>array(...));
	 	$xxxx=ArtaUtility::keyByChild($res, 'name', true);
	 	foreach($xxxx as &$v){
	 		if(!is_array($v)){
	 			$v=array($v);
	 		}
	 	}
	 	$res=array();
	 	$keyz=array_keys($xxxx);
	 	shuffle($keyz);
	 	// $keyz= array('pages','blog',...);
	 	while(count($xxxx)!==0){
	 		foreach($keyz as $key){
	 			// if(count($xxxx['pages'])>0){
	 			if(@count($xxxx[$key])>0){
	 				$res[]=array_shift($xxxx[$key]);
	 			}elseif(isset($xxxx[$key])){// if count is zero and its set, so unset it!
	 				unset($xxxx[$key]);
	 			}
	 		}
	 	}

		foreach($res as $k=>$v){
			$res[$k]->text=$this->makeText($v->text,$ph);
			
		}
		$c=ArtaLoader::Config();
		$this->count=count($res);
		$res=array_slice($res, getVar('limitstart', 0, '', 'int'), getVar('limit', $c->list_limit, '', 'int'));
		return $res;
	
	}
	
	function makeText($text, $ph){

		$int=50;
		$fragz=3;
		
		$ph=explode(' ', $ph);
		if(ArtaUTF8::strlen($text)<$int){
			$r=$text;
		}else{
			$pr=array();
			foreach($ph as $p){
				$start=ArtaUTF8::strpos($text, $p)-$int;
				if($start<1){
					$start=0;
					$yy='';
				}else{
					$yy='...';
				}
				$end=$start+$int*2;
				if($end > ArtaUTF8::strlen($text)){
					$end=ArtaUTF8::strlen($text);
					$xx='';
				}else{
					$xx='...';
				}
				$pr[$start.'-'.$end]=$yy.ArtaUTF8::substr($text, $start, $end).$xx;
			}
			$len=array();
			// select $fragz items from top of $pr
			foreach($pr as $k=>$v){
				$len[$k]=ArtaUTF8::strlen($v);
			}
			arsort($len);
			$x=array();
			for($i=0;$i<3;$i++){
				//$x[]=array_shift($len);
				foreach($len as $lk=>$lv){
					$x[$lk]=$lv;
					unset($len[$lk]);
				}
				$i++;
			}
			$a=array();
			foreach($x as $k=>$v){
				$a[]=$pr[$k];
			}
			$r=implode($a, '...');
		}
		foreach($ph as $v){
			$pos=ArtaUTF8::strpos($r, $v);
			$r=str_ireplace($v, '<span class="highlight">'.$v.'</span>', $r);
		}
		return $r;
	}
	
}
?>
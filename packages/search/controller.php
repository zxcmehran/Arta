<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/14 18:55 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class SearchController extends ArtaPackageController{
	
	function Display(){
		$v=$this->getView('search', 'html');
		$v->DisplayForm();
	}
	
	function search(){
		$times=$this->getSetting('search_times', 10);
		$time=$this->getSetting('search_timeout', 5);
		
		ArtaUtility::denyBruteForce('search_engine', 'searchMsg1', 'searchMsg2');
		ArtaUtility::addBruteForce('search_engine', 3, $times, $time*60);
		
		$q=$_SERVER['QUERY_STRING'];
		$q=ArtaURL::breakupQuery($q);
		unset($q['task']);
		$q['hash']=md5(ArtaString::hash((string)$q['phrase']).ArtaSession::genToken().(int)(date('is',time())/30));
		
		$q=ArtaURL::makeupQuery($q);
		redirect('index.php?'.$q);
	}
	
	function getXML(){
		$c=ArtaLoader::Config();
		
		$tem=ArtaLoader::Template();
		$this->setDoctype('xml');
		$l=ArtaFile::listDir(ARTAPATH_BASEDIR.'/templates/'.$tem->getName());
		
		$img='';
		foreach($l as $v){
			if(substr($v, 0, 8)=='favicon.'){
				$img=$v;
				break;
			}
		}
		
		if($img!=''){
			$ext=ArtaFile::getExt($img);
			switch($ext){
				case 'gif':
					$type='image/gif';
					break;
				case 'png':
					$type='image/png';
					break;
				case 'ico':
					$type='image/x-icon';
					break;
			}
			if(isset($type)){
				$img='  <Image height="16" width="16">data:'.$type.';base64,'.
				@htmlspecialchars(base64_encode(file_get_contents(ARTAPATH_BASEDIR.'/templates/'.$tem->getName().'/'.$img))).'</Image>';
			}else{
				$img='';
			}
		}
		
		$tmpl='<?xml version="1.0" encoding="UTF-8"?>
<OpenSearchDescription xmlns="http://a9.com/-/spec/opensearch/1.1/">
  <ShortName>'.htmlspecialchars($c->site_name).' - '.trans('SEARCH').'</ShortName>
  <Url type="text/html" method="get" template="'.htmlspecialchars(ArtaURL::getSiteURL().'index.php?pack=search&task=search&phrase={searchTerms}').'">
  </Url>
'.$img.'
  <OutputEncoding>UTF-8</OutputEncoding>
  <InputEncoding>UTF-8</InputEncoding>
</OpenSearchDescription>';

		echo $tmpl;
	}
	
}

function searchMsg1(){
	ArtaError::show(400, trans('YOU MUST WAIT 5 SECS TO PERFORM ANOTHER SEARCH'), ArtaURL::getURL());
}

function searchMsg2($time, $timeout){
	redirect('index.php?pack=search', sprintf(trans('SEARCH QUOTA FULL'),(int)(($time+$timeout-time())/60)+1));
}

?>
<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/3/14 18:53 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
class SearchViewSearch extends ArtaPackageView{
	
	function DisplayForm(){
		$this->setTitle(trans('SEARCH RESULTS'));
		$phrase=getvar('phrase','','','string');
		$hash=getvar('hash','','','string');
		if($phrase && md5(ArtaString::hash($phrase).ArtaSession::genToken().(int)(date('is',time())/30)) !== $hash){
			$q=$_SERVER['QUERY_STRING'];
			$q=ArtaURL::breakupQuery($q);
			unset($q['hash']);
			$q['task']='search';
			$q=ArtaURL::makeupQuery($q);
			ArtaError::show(400, null, 'index.php?'.$q);
		}
		$m=$this->getModel();
		$this->assign('plugins', $m->getPlugins());
		$this->assign('result', $m->getRes());
		@$past=$m->past;
		@$p = (int)strpos((string)$past, '.');
		@$past = (float)substr($past, 0, $p+4);
		@$this->assign('past', $past);
		@$this->assign('count', $m->count);
		$this->render();
	}
	
}

?>
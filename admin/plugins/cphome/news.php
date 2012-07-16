<?php
if(!defined('ARTA_VALID')){die('No access');}

function plgNewsShow(){
	return '<table class="admintable">
<thead><tr><th colspan="2">'.trans('LAST UPDATES AND NEWS').'</th></tr></thead>
<tbody>
<tr>
	<td><iframe src="http://cc.artaproject.com/arta/news.php?version='.ArtaVersion::getVersion().'&language='.trans('_LANG_ID').'" style="width:99%;border:0px; height:300px; background:white;"></iframe></td>
</tr>
</tbody>
</table>';
}

function plgGetUpdates(){
	if(is_file(ARTAPATH_ADMIN.'/tmp/updatecheck.tmp')){
		$time=ArtaFile::read(ARTAPATH_ADMIN.'/tmp/updatecheck.tmp');
	}else{
		$time=0;
	}
	if(time()-$time > 172800){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, 'http://cc.artaproject.com/arta/latest.php?version='.ArtaVersion::getVersion());
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);
		curl_setopt($ch, CURLOPT_USERAGENT,ARTA_USERAGENT);
		curl_setopt($ch, CURLOPT_TIMEOUT,15);
		// grab URL
		$c= @curl_exec($ch);
		
		if($c!==false AND curl_errno($ch)==0){
			ArtaFile::write(ARTAPATH_ADMIN.'/tmp/updatecheckres.xml', $c);
		}
				
		ArtaFile::write(ARTAPATH_ADMIN.'/tmp/updatecheck.tmp', time()-(($c==false OR curl_errno($ch)!=0)?86400:0));
		
		if(($c==false OR curl_errno($ch)!=0) AND is_file(ARTAPATH_ADMIN.'/tmp/updatecheckres.xml')){
			$c=ArtaFile::read(ARTAPATH_ADMIN.'/tmp/updatecheckres.xml');
		}
	}elseif(is_file(ARTAPATH_ADMIN.'/tmp/updatecheckres.xml')){
		$c=ArtaFile::read(ARTAPATH_ADMIN.'/tmp/updatecheckres.xml');
	}else{
		$c=false;
	}
	
	ArtaLoader::Import('#xml->simplexml');
	$xml=@ArtaSimpleXML::parseString($c);
	if($xml!=false AND version_compare($xml->version, ArtaVersion::VERSION) > 0){
		echo '<div class="cphome_msg"/><img src="'.Imageset('info.png').'" alt="i"/> '.trans('A NEWER VERSION AVAILABLE TO UPDATE').'<br/>';
		echo '<b>Arta '.$xml->version.($xml->isStable=='1'?'':' (UNSTABLE)').'</b> ('.trans('RELDATE').': '.ArtaDate::_($xml->releaseDate, 'Y/m/d').') ';
		echo '<a href="'.htmlspecialchars($xml->downloadURL).'" target="_blank">'.trans('DOWNLOAD').'</a>';
	}else{
		ArtaFile::delete(ARTAPATH_ADMIN.'/tmp/updatecheckres.xml');
	}
}

?>
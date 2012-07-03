<?php
if(!defined('ARTA_VALID')){die('No access');}
	// Links will be called from outside of browser, so we must take care of URLs
	$params=new stdClass;
	$b=$this->get('blog');
	$c=Artaloader::Config();
	$params->title=$b->title.' - '.$c->site_name;
	$params->description=$b->desc;
	
	if($b->id==0){
		$bid='';
	}else{
		$bid='&blogid='.$b->id;
	}
	
	$params->link= makeURL('index.php?pack=blog&view=last'.$bid);
	if(substr($params->link,0,1)=='/'){
		$params->link = ArtaURL::getURL(array('path'=>'','path_info'=>'', 'query'=>'')).$params->link;
	}
	$params->webmaster=$c->mail_admin;
	$i=0;
	if(is_array($this->get('items'))==false || count($this->get('items'))==0){
		ArtaError::show(404,trans('NO POSTS FOUND'));
	}else{
		$plug=ArtaLoader::Plugin();
		$tmpl=array();
		foreach($this->get('items') as $k=>$v){
                        $tmpl[$i] = new stdClass;
			$tmpl[$i]->title=$v->title;
			$tmpl[$i]->link=makeURL('index.php?pack=blog&view=post&id='.$v->id);
			if(substr($tmpl[$i]->link,0,1)=='/'){
				$tmpl[$i]->link = ArtaURL::getURL(array('path'=>'','path_info'=>'', 'query'=>'')).$tmpl[$i]->link;
			}
			$plug->trigger('onShowBody', array(&$v->introcontent, 'blogpost-intro'));
			$tmpl[$i]->description=rel2abs($v->introcontent);
			$tmpl[$i]->author=$v->author;
			$tmpl[$i]->date=date('r', strtotime($v->added_time));
			$tmpl[$i]->category=$v->blogid->title;
			$i++;
		}
		$params->items=$tmpl;
	}
	echo @ArtaTagsXML::RSS2($params);
	
	function rel2abs($text){
		URLProcess($text);
		return $text;
	}
	
	function URLProcess(&$c){
		$buffer = &$c ;
      	$p=ArtaURL::getURL(array('path'=>'','path_info'=>'', 'query'=>'')).substr($_SERVER['SCRIPT_NAME'], 0, strlen($_SERVER['SCRIPT_NAME'])-strlen('index.php'));

		$base   = $p;
       	$regex  = '#(href|src|action)="([^"]*)#m';
      	$buffer = preg_replace_callback( $regex, 'URLMake', $buffer );
       	$protocols = '[a-zA-Z0-9]+:';
		$regex     = '#(onclick="window.open\(\')(?!/|'.$protocols.'|\#)([^/]+[^\']*?\')#m';
		$buffer    = preg_replace($regex, '$1'.$base.'$2', $buffer);
		
		// Background image url()
		$regex 		= '#url\([\'\"]?(?!/|'.$protocols.'|\#)([^\)\'\"]+)[\'\"]?\)#m';
		$buffer 	= preg_replace($regex, 'url(\''. $base .'$1$2\')', $buffer);
		
		// OBJECT <param name="xx", value="yy">
		$regex 		= '#<param name="(movie|src|url)" value="(?!/|'.$protocols.'|\#|\')([^"]*)"#m';
		$buffer 	= preg_replace($regex, '<param name="$1" value="'. $base .'$2"', $buffer);

		// OBJECT <param value="xx", name="yy">
		$regex 		= '#<param value="(?!/|'.$protocols.'|\#|\')([^"]*)" name="(movie|src|url)"#m';
		$buffer 	= preg_replace($regex, '<param value="'. $base .'$1" name="$2"', $buffer);

		return true;
	}

   	 function URLMake( &$matches ){
		$original       = $matches[0];
		$type			= $matches[1];
       	$url            = $matches[2];

		$url = str_replace('&amp;','&',$url);
		if(strlen($url) && $url{0}!=='#'){
       		$url = ArtaURL::getURL(array('path'=>'','path_info'=>'', 'query'=>'')).ArtaURL::make($url);
       	}
       	if($type=='url'){
       		return 'url('.$url.')';
       	}
      	return $type.'="'.$url;
      }
	
?>

<?php
/**
 * ArtaOEmbed. Support for OEmbed service.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if (!defined('ARTA_VALID')) {
	die('No access');
}

/**
 * Arta OEmbed
 * 
 * @uses	http://embed.ly/
 * @link	http://oembed.com/	For more info about OEmbed system.
 */
class ArtaOEmbed
{
	/**
	 * Service Providers
	 * If you want to add more, use plugins before start rendering package.
	 * @var	array
	 */
	var $services = array('#http://(www\.)?youtube.com/watch.*#i' => array('http://www.youtube.com/oembed', true),
		'http://youtu.be/*' => array('http://www.youtube.com/oembed', false),
		'http://blip.tv/file/*' => array('http://blip.tv/oembed/', false), 
		'#http://(www\.)?vimeo\.com/.*#i' => array('http://www.vimeo.com/api/oembed.{format}', true),
		'#http://(www\.)?dailymotion\.com/.*#i' => array('http://www.dailymotion.com/api/oembed', true), 
		'#http://(www\.)?flickr\.com/.*#i' => array('http://www.flickr.com/services/oembed/', true), 
		'#http://(.+)?smugmug\.com/.*#i' => array('http://api.smugmug.com/services/oembed/', true), 
		'#http://(www\.)?hulu\.com/watch/.*#i' => array('http://www.hulu.com/api/oembed.{format}', true), 
		'#http://(www\.)?viddler\.com/.*#i' => array('http://lab.viddler.com/services/oembed/', true), 
		'http://qik.com/*' => array('http://qik.com/api/oembed.{format}', false), 
		'http://revision3.com/*' =>	array('http://revision3.com/api/oembed/', false),
		'http://i*.photobucket.com/albums/*' => array('http://photobucket.com/oembed', false),
		'http://gi*.photobucket.com/groups/*' => array('http://photobucket.com/oembed', false),
		'#http://(www\.)?scribd\.com/.*#i' => array('http://www.scribd.com/services/oembed', true),
		'http://wordpress.tv/*' => array('http://wordpress.tv/oembed/', false),
		'#http://(answers|surveys)\.polldaddy.com/.*#i' => array('http://polldaddy.com/oembed/', true),
		'#http://(www\.)?funnyordie\.com/videos/.*#i' => array('http://www.funnyordie.com/oembed', true));
	
	/**
	 * Converted Services ready to use
	 * @access	private
	 * @var	array
	 */
	private $_services=array();
	
	/**
	 * Preferred Format of response.
	 * @access	private
	 * @var	string
	 */
	private $format;
	
	/**
	 * Cache of items
	 * @access	private
	 * @var	array
	 */
	private $cache=array();
	
	/**
	 * Should update cache file?
	 * @access	private
	 * @var	bool
	 */
	private $cache_updated=false;
	
	/**
	 * Makes everything ready.
	 */
	function __construct(){
		$this->remakeServices();
		$this->loadCache();
	}
	
	/**
	 * Caches some data if needed.
	 */
	function __destruct(){
		if($this->cache_updated==true){
			ArtaCache::putData('oembed','urlcache', $this->cache);
		}
	}
	
	/**
	 * Loads cache into $this->cache.
	 * @access	private
	 */
	private function loadCache(){
		if(ArtaCache::isUsable('oembed','urlcache')){
			$this->cache=(array)ArtaCache::getData('oembed','urlcache');
		}
	}	
	
	/**
	 * Converts wildcard items to regexp ones.
	 * @access	private
	 */
	private function remakeServices(){
		foreach ( $this->services as $matchmask => $data ) {
			list( $providerurl, $regex ) = $data;
			if ( !$regex ){
				$matchmask = '#' . str_replace( '___wildcard___', '(.+)', preg_quote( str_replace( '*', '___wildcard___', $matchmask ), '#' ) ) . '#i';
			}
			$this->_services[$matchmask]=$providerurl;
		}
	}
	
	/**
	 * Finds out the preferred response format.
	 * @return	string	preferred format
	 */
	function getFormat(){
		if(function_exists('json_decode')){
			return 'json';
		}else{
			return 'xml';
		}
	}
	
	/**
	 * Returns the URL embed code.
	 * To operate this class you need to just this method. it will take care of everything after constructing.
	 * @param	string	$url	The URL of content (like a movie in youtube.)
	 * @param	array	$params	Parameters of embed. It's consist of maxheight and maxwidth.
	 * @return	mixed	false on failure and html of embed on success.
	 */
	function getResult($url, $params=array()){ 
		if(isset($this->cache[$url.serialize($params)])){
			return $this->cache[$url.serialize($params)];
		}
		
		$this->format= $this->getFormat();
		
		foreach($this->_services as $match=>$prov){
			if(preg_match( $match, $url )){
				$provider= str_replace( '{format}', $this->format, $prov );
				break;
			}
		}
		if(@$provider==null){
			$provider = 'http://api.embed.ly/1/oembed'; // use embedly if provider not present in class
		}
		
		
		$data = $this->fetchData($provider, $url, $params,false);
		
		// if embedly failed try to discover
		if(!$data){
			$provider = $this->discoverProvider($url);
			if(!$provider) return false;
			
			$data = $this->fetchData($provider, $url, $params,true);
			if(!$data) return false;		
		}
		
		
		$res = $this->getHTML($data, $params);
		$this->cache[$url.serialize($params)]=$res;
		$this->cache_updated=true;
		return $res;
	}
	
	/**
	 * Discovers Service Provider form content URL.
	 * 
	 * @param	string	$url	The URL of content (like a movie in youtube.)
	 * @return	mixed	false on failure else array of providers.
	 */
	function discoverProvider($url){
		$providers=array();
		$linktypes = array(
				'application/json+oembed' => 'json',
				'text/xml+oembed' => 'xml',
				'application/xml+oembed' => 'xml');// Incorrect, but used by Vimeo
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);			
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_USERAGENT,ARTA_USERAGENT);
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		@set_time_limit(40);
		// grab URL
		$c = @curl_exec($ch);
		
		if($c==false){
			return false;
		}
		$c = substr($c, 0, stripos($c, '</head>'));
		
		$found = false;
		foreach($linktypes as $lt => $format){
			if(stripos($c, $lt)){
				$found = true;
				break;
			}
		}
		
		if($found && preg_match_all( '/<link([^<>]+)>/i', $c, $links )){
			foreach ( $links[1] as $link ) {
				$atts=array();
				preg_match('#href="([^"<>]+)"#i', $link,$atts['href']);
				preg_match('#type="([^"<>]+)"#i', $link,$atts['type']);

				if ( !empty($atts['type']) && !empty($linktypes[$atts['type']]) && !empty($atts['href']) ) {
					$providers[$linktypes[$atts['type']]] = $atts['href'];
				}
			}
		}
		
		return $providers;
	}
	
	/**
	 * Fetches Service response and returns parsed value.
	 * 
	 * @param	mixed	$prov	Provider(s)
	 * @param	string	$url	Content URL
	 * @param	array	$params	Embed Parameters
	 * @param	bool	$discovered	Are providers discovered or was known by class?
	 * @return	mixed	false on failure else object of provider response.
	 */
	function fetchData($prov,$url,$params,$discovered){
		$params2send=array();
		if(@$params['maxwidth']){
			$params2send['maxwidth']=$params['maxwidth'];
		}
		if(@$params['maxheight']){
			$params2send['maxheight']=$params['maxheight'];
		}
		if(!$discovered){
			$provider=$prov;
			$params2send['url']=urlencode($url);
			$params2send['format']=$this->format;
		}else{
			if(isset($prov[$this->format])){
				$provider=$prov[$this->format];
			}elseif($this->format!='xml' AND @$prov['xml']!=null){
				$this->format='xml';
				$provider = $prov['xml'];
			}else{
				return false;
			}
		}
		
		if(strpos($provider, '?')){
			$provider=explode('?',$provider);
			$pq=(array)ArtaString::splitVars(implode('?',array_slice($provider,1)));
			$provider=$provider[0];
		}else{
			$pq=array();
		}
		foreach($params2send as $k=>$v){
			if(!isset($pq[$k])){
				$pq[$k]=$v;
			}
		}
		
		$format=$this->format;
		$cont=$this->fetchDataContents($provider.'?'.ArtaString::stickVars($pq));

		if($cont===false && $format=='json' && !$discovered){
			$pq['format']='xml';
		}elseif($cont===false && $format=='json' && isset($prov['xml'])){
			$provider = $prov['xml'];
			if(strpos($provider, '?')){
				$provider=explode('?',$provider);
				$pq=(array)ArtaString::splitVars(implode('?',array_slice($provider,1)));
				$provider=$provider[0];
			}else{
				$pq=array();
			}
			foreach($params2send as $k=>$v){
				if(!isset($pq[$k])){
					$pq[$k]=$v;
				}
			}
		}elseif($cont===false){
			return false;
		}
		if($cont===false){
			$format='xml';
			$cont=$this->fetchDataContents($provider.'?'.ArtaString::stickVars($pq));
			if($cont==false){
				return false;
			}
		}

		eval('$r=$this->_parse_'.$format.'($cont);');
		return $r;
		
	}
	
	
	/**
	 * Downloads response from URL.
	 * @param	string	$url	URL to grab
	 * @return	mixed
	 */
	function fetchDataContents($url){
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_HEADER, false);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER,false);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST,0);	
		curl_setopt($ch, CURLOPT_USERAGENT,ARTA_USERAGENT);	
		curl_setopt($ch, CURLOPT_TIMEOUT, 10);
		// grab URL
		$c = @curl_exec($ch);
		if(curl_errno($ch)==501){ //not implemented.
			return false;
		}
		return $c;
	}
	
	/**
	 * Parses a json response body.
	 *
	 * @access private
	 * @param	string	$response_body	The Response body
	 */
	private function _parse_json( $response_body ) {
		return ( ( $data = json_decode( trim( $response_body ) ) ) && is_object( $data ) ) ? $data : false;
	}

	/**
	 * Parses an XML response body.
	 *
	 * @access private
	 * @param	string	$response_body	The Response body
	 */
	private function _parse_xml( $response_body ) {
		if ( function_exists('simplexml_load_string') ) {
			$data = simplexml_load_string( $response_body );
			if ( is_object( $data ) )
				return $data;
		}
		return false;
	}
	
	
	/**
	 * Extracts HTML code from response contents.
	 * @param	object	$data	Parsed response
	 * @param	array	$params	Embed parameters
	 * @return	mixed	HTML on success, false on failure.
	 */
	function getHTML($data, $params){
		if(!is_object($data) || empty($data->type))	return false;
		switch($data->type){
			case 'photo':
				if(empty($data->url) || empty($data->width) || empty($data->height)) return false;
				$title = (!empty($data->title))?$data->title:'';
				$return = '<img src="'.htmlspecialchars($data->url).'" title="'.htmlspecialchars($title).'" alt="'.htmlspecialchars($title).'" width="'.htmlspecialchars($data->width).'" height="'.htmlspecialchars($data->height).'" />';
			break;
			case 'video':
			case 'rich':
				$x='';
				
				if(isset($params['maxwidth']) AND (!isset($data->width) || $data->width>$params['maxwidth'])){
					$x .='width: '.(int)$params['maxwidth'].'px;';
					
				}
				if(isset($params['maxheight']) AND (!isset($data->height) || $data->height>$params['maxheight'])){
					$x .='height: '.(int)$params['maxheight'].'px;';
				}
				if($x !=''){
					$x= '<div style="overflow:auto; '.$x.'">';
					$return = (!empty($data->html))?$x.$data->html.'</div>':false;
				}else{
					$return = (!empty($data->html))?$data->html:false;
				}
			break;
			case 'link':
				$return = (!empty($data->title))?'<a href="'.htmlspecialchars($url).'">'.htmlspecialchars($data->title).'</a>' : false;
				break;
			default;
				$return = false;
		}
		
		return $return;
	}
	
	
	
}

?>
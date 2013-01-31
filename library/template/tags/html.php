<?php 
/**
 * HTML Tags generation class is included here.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @subpackage	ArtaTemplate
 * @version		$Revision: 2 2012/12/06 18:50 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
if(!defined('ARTA_VALID')){die('No access');}
/**
 * ArtaTagsHtml class for output special HTML tags.
 * @todo	comment all methods in this file (may not).
 * @static
 */
class ArtaTagsHtml{
	
	static function meta(){
		$template = ArtaLoader::Template();
		$config=ArtaLoader::Config();
		$m = 
			'<meta http-equiv="Content-Type" content="text/html; charset='.trans('_LANG_CHARSET').'" />'."\n\t".
			'<meta name="robots" content="index, follow" />'."\n\t".
			'<meta name="generator" content="'.htmlspecialchars($template->getGenerator()).'" />'."\n\t".
			'<meta name="keywords" content="'.htmlspecialchars($template->keywords).'" />'."\n\t".
			'<meta name="description" content="'.htmlspecialchars($template->description).'" />'."\n\t".
			"<script>\nvar site_url = '".JSValue(ArtaURL::getSiteURL())."';\nvar client_url='".ArtaURL::getClientURL()."';\nvar img_url='".JSValue(ArtaURL::getClientURL())."imagesets/".JSValue($template->getImgSetName())."/';".
			"\n</script>";
		return $m;
	}
	
	static function addtoTmpl($str, $location){
		$t=ArtaLoader::Template();
		$t->addtoTmpl($str, $location);
	}

	static function script(){
		global $_SCRIPTS;
		$res = "\n\t";
		foreach($_SCRIPTS as $v){
			$res .=$v."\n\t";
		}
		return $res;
	}

	static function addScript($path){
		global $_SCRIPTS;
		$val='<script src="'.htmlspecialchars($path).'"></script>';
		if(!in_array($val, $_SCRIPTS)){
			$_SCRIPTS[]=$val;
		}
	}

	static function CSS(){
		global $_CSS;
		$res = "\n\t";
		foreach($_CSS as $v){
			$res .=$v."\n\t";
		}
		return $res;
	}

	static function addCSS($path){
		global $_CSS;
		$val='<link href="'.htmlspecialchars($path).'" rel="stylesheet" type="text/css"></link>';
		if(!in_array($val, $_CSS)){
			$_CSS[]=$val;
		}
	}
	
	static function addHeader($val){
		global $_HEAD;
		if(in_array($val,$_HEAD)==false){
			$_HEAD[]=$val;
		}
		return true;
	}
	
	static function addRSS($link, $title='RSS 2.0', $type="application/rss+xml"){
		self::addHeader('<link href="'.htmlspecialchars($link).'" rel="alternate" type="'.htmlspecialchars($type).'" title="'.htmlspecialchars($title).'" />');
	}
	
	static function addLibraryScript($name){
		switch(strtolower($name)){
			case 'scriptaculous':
				self::addLibraryScript('prototype');
				$code=('media/scripts/scriptaculous/scriptaculous.js');
				$pos='head';
			break;
			case 'prototype':
				$code=('media/scripts/prototype.js');
				$pos='head';
			break;
			case 'arta':
				$code=('media/scripts/arta/arta.js');
				$pos='head';
			break;
			case 'artacalendar':
			case 'calendar':
				self::addLibraryScript('prototype');
				$code=('media/scripts/arta/artacalendar.js');
				$pos='head';
			break;
			case 'livepipe':
			case 'livepipe_event':
				self::addLibraryScript('prototype');
				self::addLibraryScript('scriptaculous');
				$code=('media/scripts/livepipe/livepipe.js');
				$pos='head';
			break;
			case 'livepipe_contextmenu':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/contextmenu.js');
				$pos='head';
			break;
			case 'livepipe_cookie':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/cookie.js');
				$pos='head';
			break;
			case 'livepipe_event_behavior':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/event_behavior.js');
				$pos='head';
			break;
			case 'livepipe_hotkey':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/hotkey.js');
				$pos='head';
			break;
			case 'livepipe_progressbar':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/progressbar.js');
				$pos='head';
			break;
			case 'livepipe_rating':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/rating.js');
				$pos='head';
			break;
			case 'livepipe_resizable':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/resizable.js');
				$pos='head';
			break;
			case 'livepipe_scrollbar':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/scrollbar.js');
				$pos='head';
			break;
			case 'livepipe_selection':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/selection.js');
				$pos='head';
			break;
			case 'livepipe_selectmultiple':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/selectmultiple.js');
				$pos='head';
			break;
			case 'livepipe_tabs':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/tabs.js');
				$pos='head';
			break;
			case 'livepipe_textarea':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/textarea.js');
				$pos='head';
			break;
			case 'livepipe_window':
				self::addLibraryScript('livepipe');
				$code=('media/scripts/livepipe/window.js');
				$pos='head';
			break;
			case 'menu':
				$code=('media/scripts/menu.js');
				$pos='head';
			break;
			case 'fisheye':
				$code=('media/scripts/fisheye.js');
				$pos='head';
			break;
			case 'tinymce':
			case 'tiny_mce':
				$code=('media/scripts/tiny_mce/tiny_mce.js');
				$pos='head';
			break;
			case 'codepress':
				$code=('media/scripts/codepress/codepress.js');
				$pos='head';
			break;
			case 'jquery':
				$code=('media/scripts/jquery.js');
				$pos='head';
			break;
			case 'html5':
			case 'html5shiv':
			case 'html5shim':
				
//				self::addHeader('<!--[if lt IE 9]>
//		<script src="media/scripts/html5shiv.js"></script>
//	<![endif]-->');
//				return;
				$code=('media/scripts/html5shiv.js');
				$pos='head';
			break;
			case 'jqueryui':
			case 'jquery-ui':
			case 'jquery_ui':
			case 'jquery.ui':
				self::addLibraryScript('jquery');
				self::addCSS('media/styles/jquery_ui/jquery_ui.css');
				$code=('media/scripts/jquery_ui.js');
				$pos='head';
			break;
			case 'jquerymobile':
			case 'jquery-mobile':
			case 'jquery_mobile':
			case 'jquery.mobile':
				self::addLibraryScript('jquery');
				self::addCSS('media/styles/jquery_mobile/jquery_mobile.css');
				self::addCSS('media/styles/jquery_mobile/jquery_mobile_structure.css');
				$code=('media/scripts/jquery_mobile.js');
				$pos='head';
			break;
			default:
				$code=('media/scripts/'.$name.'.js');
				$pos='head';
			break;
		}
		$t=ArtaLoader::Template();
		if($pos=='head'){
			self::addScript($code);
		}elseif($pos=='afterbody'){
			if($t->added('afterbody',$code)==false){
				$t->addtoTmpl('<script type="text/javascript" src="'.htmlspecialchars($code).'"></script>', 'afterbody');
			}
		}elseif($pos=='beforebodyend'){
			if($t->added('beforebodyend',$code)==false){
				$t->addtoTmpl('<script type="text/javascript" src="'.htmlspecialchars($code).'"></script>', 'beforebodyend');
			}
		}
		if(strtolower($name)=='jquery'){
			$t->addtoTmpl('<script type="text/javascript">var $j = jQuery.noConflict();</script>', 'head');
		}
	}
	
	static function addCodeEditor($name, $value, $params=array()){
		self::addLibraryScript('codepress');
		$params=ArtaUtility::array_extend($params, array('language'=>'php', 'id'=>'codeeditor_'.ArtaString::makeRandStr(), 'height'=>'300px'));
		$r='<textarea name="'.htmlspecialchars($name).'" id="'.htmlspecialchars($params['id']).'" class="codepress '.htmlspecialchars($params['language']).'" style="width:95%;height:'.htmlspecialchars($params['height']).';">'.htmlspecialchars($value).'</textarea>';
		return $r;
	}
	
	static function addEditor($name, $value, $params=array()){
		$editor='tinymce';
		if(!is_callable(array('ArtaTagsHtml', 'addEditor_'.$editor))){
			ArtaError::show(500, 'Editor "'.$editor.'" Not found');
		}
		eval('$r=self::addEditor_'.$editor.'($name, $value, $params);');
		return $r;
	}
	
	static function addEditor_tinymce($name, $value, $params=array()){
		$id='editor_'.$name;
		if(@$params['toolbarset']=='mini' && !isset($params['height'])){
			$params['height']='100';
		}
		$params=ArtaUtility::array_extend($params, array('skin'=>'o2k7', 'height'=>'400', 'toolbarset'=>'default','savebtn'=>false));
		
		
		
		self::addLibraryScript('tinymce');
		switch($params['toolbarset']){
			case 'mini':
				$t='
				theme : "simple"';
				if($params['savebtn']){
					$z='save|,';
				}else{
					$z='';
				}
				$t='
				theme : "advanced",
				theme_advanced_buttons1 : "'.$z.'undo,redo,|,cut,copy,paste,pastetext,pasteword,|,codetag,styleprops,|,link,unlink,image,emotions,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,ltr,rtl,|,help",
		theme_advanced_buttons2 : "",
		theme_advanced_buttons3 : "",
		theme_advanced_buttons4 : "",
		theme_advanced_toolbar_location : "bottom",
		theme_advanced_toolbar_align : "center",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true';
			break;
			default:
				if($params['savebtn']){
					$z=',save';
				}else{
					$z='';
				}
				$t='
				theme : "advanced",
				theme_advanced_buttons1 : "newdocument'.$z.',preview,print,|,search,replace,|,undo,redo,|,cut,copy,paste,pastetext,pasteword,|,fullscreen,code,help,|,cite,abbr,acronym,del,ins,attribs",
		theme_advanced_buttons2 : "bold,italic,underline,strikethrough,|,justifyleft,justifycenter,justifyright,justifyfull,|,ltr,rtl,|,sub,sup,|,bullist,numlist,|,outdent,indent,blockquote,|,visualchars,nonbreaking,|,styleprops",
		theme_advanced_buttons3 : "tablecontrols,|,hr,removeformat,visualaid,|,insertlayer,moveforward,movebackward,absolute,|,insertdate,inserttime",
		theme_advanced_buttons4 : "link,unlink,anchor,image,cleanup,|,codetag,charmap,emotions,media,advhr,|,forecolor,backcolor,formatselect,fontselect,fontsizeselect",
		theme_advanced_toolbar_location : "top",
		theme_advanced_toolbar_align : "left",
		theme_advanced_statusbar_location : "bottom",
		theme_advanced_resizing : true';
			break;
		}
		$sc='<script>	
	tinyMCE.init({
		// General options
		mode: "exact",
		visual: 0,
		elements: "'.JSValue($id).'",
		skin: "o2k7",
		document_base_url: "'.JSValue(ArtaURL::getSiteURL()).'",
		plugins : "codetag,safari,style,layer,table,save,advhr,advimage,advlink,emotions,insertdatetime,preview,media,searchreplace,print,contextmenu,paste,directionality,fullscreen,noneditable,visualchars,nonbreaking,xhtmlxtras,inlinepopups,tabfocus",
		
		// Theme options
		'.$t.'

		,content_css : site_url+"media/scripts/tiny_mce/style.css"

	});</script><style>table.mceLayout{direction:ltr;}</style>';
		ArtaTagsHtml::addHeader($sc);
		$txt='<textarea id="'.htmlspecialchars($id).'" name="'.htmlspecialchars($name).'" style="width: 95%; height:'.$params['height'].'px">'.htmlspecialchars($value).'</textarea><div onclick="if(this.innerHTML==\'&times;\'){tinyMCE.execCommand(\'mceRemoveControl\', false, \''.htmlspecialchars($id).'\');this.innerHTML=\'o\';}else{tinyMCE.execCommand(\'mceRemoveControl\', false, \''.htmlspecialchars($id).'\');this.innerHTML=\'&times;\';}" style="font-weight:bold; color:red; font-size:16px;cursor: pointer;">&times;</div>';
		return $txt;
	}
	
	static function getTransed($p,$t,$a=false){
		$j_month_0=array('farvardin', 'ordibehest', 'khordad', 'tir', 'mordad', 'shahrivar', 'mehr', 'aban','azar', 'dey', 'bahman', 'esfand');
		$j_month_1=array('far', 'ord', 'kho', 'tir', 'mor', 'sha', 'meh', 'aba', 'aza', 'dey', 'bah','esf');
		 
		$g_month_0=array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august','september', 'october', 'november', 'december');
		$g_month_1=array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov','dec');
	 	$weekday_0=array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
	 	$weekday_1=array('sun','mon','tue','wed','thu','fri','sat');
	 	eval('$v=$'.$t.'_'.(int)$a.';');
	 	return trans($t.'_'.(int)$a.'_'.strtoupper($v[$p-1]));
	}
	
	static function addArtaCalendarTranslation(){
		$weekdays="'".implode("', '", array(
			JSValue(self::getTransed(1,'weekday')),
			JSValue(self::getTransed(2,'weekday')),
			JSValue(self::getTransed(3,'weekday')),
			JSValue(self::getTransed(4,'weekday')),
			JSValue(self::getTransed(5,'weekday')),
			JSValue(self::getTransed(6,'weekday')),
			JSValue(self::getTransed(7,'weekday'))
		))."'";
		$short_weekdays="'".implode("', '", array(
			JSValue(self::getTransed(1,'weekday', true)),
			JSValue(self::getTransed(2,'weekday', true)),
			JSValue(self::getTransed(3,'weekday', true)),
			JSValue(self::getTransed(4,'weekday', true)),
			JSValue(self::getTransed(5,'weekday', true)),
			JSValue(self::getTransed(6,'weekday', true)),
			JSValue(self::getTransed(7,'weekday', true))
		))."'";
		$gregorian_months="'".implode("', '", array(
			JSValue(self::getTransed(1,'g_month')),
			JSValue(self::getTransed(2,'g_month')),
			JSValue(self::getTransed(3,'g_month')),
			JSValue(self::getTransed(4,'g_month')),
			JSValue(self::getTransed(5,'g_month')),
			JSValue(self::getTransed(6,'g_month')),
			JSValue(self::getTransed(7,'g_month')),
			JSValue(self::getTransed(8,'g_month')),
			JSValue(self::getTransed(9,'g_month')),
			JSValue(self::getTransed(10,'g_month')),
			JSValue(self::getTransed(11,'g_month')),
			JSValue(self::getTransed(12,'g_month'))
		))."'";
		$jalali_months="'".implode("', '", array(
			JSValue(self::getTransed(1,'j_month')),
			JSValue(self::getTransed(2,'j_month')),
			JSValue(self::getTransed(3,'j_month')),
			JSValue(self::getTransed(4,'j_month')),
			JSValue(self::getTransed(5,'j_month')),
			JSValue(self::getTransed(6,'j_month')),
			JSValue(self::getTransed(7,'j_month')),
			JSValue(self::getTransed(8,'j_month')),
			JSValue(self::getTransed(9,'j_month')),
			JSValue(self::getTransed(10,'j_month')),
			JSValue(self::getTransed(11,'j_month')),
			JSValue(self::getTransed(12,'j_month'))
		))."'";
		$lang="'".implode("', '", array(
			JSValue(trans('CALENDAR_TODAY')),
			JSValue(trans('CALENDAR_PREV_YEAR')),
			JSValue(trans('CALENDAR_PREV_MONTH')),
			JSValue(trans('CALENDAR_NEXT_MONTH')),
			JSValue(trans('CALENDAR_NEXT_YEAR')),
			JSValue(trans('CALENDAR_CLOSE')),
			JSValue(trans('CALENDAR_REFRESH')),
			JSValue(trans('CALENDAR_IN_JALALI')),
			JSValue(trans('CALENDAR_IN_GREGORIAN')),
			JSValue(trans('CALENDAR_SELECTED'))
		))."'";
		$v="<script>
	ArtaCalendarStore.weekdays=new Array({$weekdays});
	ArtaCalendarStore.short_weekdays=new Array({$short_weekdays});
	ArtaCalendarStore.jalali_months=new Array({$jalali_months});
	ArtaCalendarStore.gregorian_months=new Array({$gregorian_months});
	ArtaCalendarStore.lang=new Array({$lang});	
</script>
		";
		return self::addHeader($v);
	}
	
	static function Tooltip($str=null, $tip, $width=200){
		if($str==null){
			$str = '<img src="'.Imageset('info.png').'" alt="i"/>';
		}
		if(strlen($tip)!==0){
			ArtaTagsHtml::addCSS('media/styles/tooltips.css');
			self::addLibraryScript('livepipe_window');
			$id=ArtaString::makeRandStr(8);
			$t=ArtaLoader::Template();
			$tip=JSValue($tip);
			$s="<script>new Control.ToolTip($('tooltip_{$id}'),\"{$tip}\",{className:'tooltip',width:'{$width}'});</script>";
			if(!$t->added('beforebodyend',$s)){
				$t->addtoTmpl($s,'beforebodyend');
			}
			return "<span class=\"tooltip_text\" id=\"tooltip_{$id}\">".$str."</span>";
		}else{
			return $str;
		}
	}

	static function WarningTooltip($tip){
		if(strlen($tip)!==0){
			ArtaTagsHtml::addCSS('media/styles/tooltips.css');
			self::addLibraryScript('livepipe_window');
			$id=ArtaString::makeRandStr(8);
			$t=ArtaLoader::Template();
			$tip=JSValue($tip);
			$s="<script>new Control.ToolTip($('tooltip_{$id}'),\"{$tip}\",{className:'tooltip_warning',width:'200'});</script>";
			if(!$t->added('beforebodyend',$s)){
				$t->addtoTmpl($s,'beforebodyend');
			}
			return "<span class=\"tooltip_text\" id=\"tooltip_{$id}\"><img src=\"".Imageset('warning.png')."\"></span>";
		}else{
			return '';
		}
	}
		
	static function Window($linktxt, $url, $title=''){
		$str=$linktxt;
		ArtaTagsHtml::addCSS('media/styles/windows.css');
		self::addLibraryScript('livepipe_window');
		$t=ArtaLoader::Template();
		$w="<script>
		var window_factory = function(container,options){
			var window_header = new Element('div',{	className: 'window_header' });
			var window_title = new Element('div',{ className: 'window_title' });
			var window_close = new Element('div',{ className: 'window_close' });
			var window_contents = new Element('div',{ className: 'window_contents' });
			var w = new Control.Window(container,Object.extend({
				className: 'window',
				closeOnClick: window_close,
				draggable: window_header,
				fade:true,
				iframe:true,
				position: 'center',
				insertRemoteContentAt: window_contents,
				afterOpen: function(){
					window_title.update(container.readAttribute('title'));
					w.position();
				}
			},options || {}));
			window_close.innerHTML='&times;';
			w.container.insert(window_header);
			window_header.insert(window_title);
			window_header.insert(window_close);
			w.container.insert(window_contents);
			
			return w;
		};
		</script>";
		if(!$t->added('head',$w)){
			$t->addtoTmpl($w,'head');
		}
		$id=ArtaString::makeRandStr(7);
		$s="<script>window_factory($('window_{$id}'));</script>";
		if(!$t->added('beforebodyend',$s)){
			$t->addtoTmpl($s,'beforebodyend');
		}
		$url=htmlspecialchars($url);
		$title=htmlspecialchars($title);

		return "<a href=\"{$url}\" class=\"window_text\" id=\"window_{$id}\" title=\"{$title}\">".$str."</a>";
	}
	
	static function ModalWindow($linktxt, $url, $title='', $is_img=false){
		self::addLibraryScript('livepipe_window');
		ArtaTagsHtml::addCSS('media/styles/windows.css');
		$t=ArtaLoader::Template();
		if($is_img){
			$name='modal_factory';
			$xx='imageHandle: true,
			insertRemoteContentAt: content,';
			$yy='';
			$zz='window_title.update(container.readAttribute(\'title\'));  
	     window_header.insert(window_title);  
	     modal.container.insert(window_header);
	     modal.container.insert(content);';
			$class=' imgwindow';
			$ww='';			
		}else{
			$name='modal_factory2';
			$xx='iframe: true,';
			$yy='';
			$zz='';
			$class='';
			$ww='window_title.update(container.readAttribute(\'title\'));  
	     window_header.insert(window_title);  
		 modal.getRemoteContentInsertionTarget().insert(window_header);';			
		}
		$id=ArtaString::makeRandStr(7);
		$s="<script>
	var $name=function(container){
	 var window_header = new Element('div',{ className: 'window_header' });  
     var content = new Element('div', {className:'window_contents'});  
     var window_title = new Element('div',{ className: 'window_title' });
	 var loadingProgress=new Element('img');
	 loadingProgress.setAttribute('src', '".makeURL(Imageset('loading_bar.gif'))."');
	 loadingProgress.setAttribute('id', 'loadingHandler'); 
	 loadingProgress.setAttribute('style', 'position:absolute;top:2px;left:2px;z-index:10001;'); 
	 Event.observe(window,'scroll',replaceProgress,false);
	 var dim=document.viewport.getDimensions();
	 
	 function replaceProgress(event){
	 	l= (dim.width / 2).ceil() - (loadingProgress.getWidth()/2);
	 	t= (dim.height / 2).ceil() - (loadingProgress.getHeight()/2);
	 	offset=document.viewport.getScrollOffsets();
	 	loadingProgress.setStyle({top: (offset.top + t)+'px',  left: (offset.left + l)+'px'});
	 }
	 document.body.insert(loadingProgress);
	 loadingProgress.hide();
	 
	 postype=Prototype.Browser.Opera==true?'relative':'center';
	 var modal = new Control.Modal(container,{  
	     overlayOpacity: 0.75,  
	     className: 'window modal$class',  
	     fade: true, $xx
		 closeOnClick: 'overlay',
		 position:postype,
		 beforeOpen: function(){	new Effect.Appear(loadingProgress); $yy },
		 onInsertFrame: function(){ $ww },	  
		 afterOpen: function(){	loadingProgress.hide(); $zz modal.position();},
		 beforeClose: function(){ loadingProgress.hide(); }
	 });
 };
 </script>";
		if(!$t->added('beforebodyend',$s)){
			$t->addtoTmpl($s,'beforebodyend');
		}
		$s="<script>$name($('modal_window_{$id}'));</script>";
		if(!$t->added('beforebodyend',$s)){
			$t->addtoTmpl($s,'beforebodyend');
		}
		$url=htmlspecialchars($url);
		$title=htmlspecialchars($title);

		return "<a href=\"{$url}\" class=\"window_text modal\" id=\"modal_window_{$id}\" title=\"{$title}\">".$linktxt."</a>";
	}

	static function getTimezones($name='offset', $selected=0){
		$res='<select name="'.htmlspecialchars($name).'" size="1" style="font-size:10px;width:100%;">'."\n";
		$timezones=array('-12'=>"(UTC -12:00) International Date Line West",
			'-11'=>"(UTC -11:00) Midway Island, Samoa",
			'-10'=>"(UTC -10:00) Hawaii",
			'-9.5'=>"(UTC -09:30) Taiohae, Marquesas Islands",
			'-9'=>"(UTC -09:00) Alaska",
			'-8'=>"(UTC -08:00) Pacific Time (US &amp; Canada)",
			'-7'=>"(UTC -07:00) Mountain Time (US &amp; Canada)",
			'-6'=>"(UTC -06:00) Central Time (US &amp; Canada), Mexico City",
			'-5'=>"(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima",
			'-4'=>"(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz",
			'-3.5'=>"(UTC -03:30) St. John's, Newfoundland and Labrador",
			'-3'=>"(UTC -03:00) Brazil, Buenos Aires, Georgetown",
			'-2'=>"(UTC -02:00) Mid-Atlantic",
			'-1'=>"(UTC -01:00) Azores, Cape Verde Islands",
			'0'=>"(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca",
			'1'=>"(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris",
			'2'=>"(UTC +02:00) Istanbul, Jerusalem, Kaliningrad, South Africa",
			'3'=>"(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg",
			'3.5'=>"(UTC +03:30) Tehran",
			'4'=>"(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi",
			'4.5'=>"(UTC +04:30) Kabul",
			'5'=>"(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent",
			'5.5'=>"(UTC +05:30) Bombay, Calcutta, Madras, New Delhi",
			'5.75'=>"(UTC +05:45) Kathmandu",
			'6'=>"(UTC +06:00) Almaty, Dhaka, Colombo",
			'6.3'=>"(UTC +06:30) Yagoon",
			'7'=>"(UTC +07:00) Bangkok, Hanoi, Jakarta",
			'8'=>"(UTC +08:00) Beijing, Perth, Singapore, Hong Kong",
			'8.75'=>"(UTC +08:00) Western Australia",
			'9'=>"(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk",
			'9.5'=>"(UTC +09:30) Adelaide, Darwin, Yakutsk",
			'10'=>"(UTC +10:00) Eastern Australia, Guam, Vladivostok",
			'10.5'=>"(UTC +10:30) Lord Howe Island (Australia)",
			'11'=>"(UTC +11:00) Magadan, Solomon Islands, New Caledonia",
			'11.3'=>"(UTC +11:30) Norfolk Island",
			'12'=>"(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka",
			'12.75'=>"(UTC +12:45) Chatham Island",
			'13'=>"(UTC +13:00) Tonga",
			'14'=>"(UTC +14:00) Kiribati");
		foreach($timezones as $k=>$v){
			if((string)$selected == $k){
				$checked=' selected="selected"';
			}else{
				$checked='';
			}
			// $v is already htmlspecialchars() ed.
			$res .="<option value=\"{$k}\"{$checked}>".$v."</option>\n";			
		}
		$res .="</select>";
		return $res;
	}

	static function getOfflineMsg(){
		$config=ArtaLoader::Config();
		$template=ArtaLoader::Template();
		$res="<!DOCTYPE html>\n<html><head><title>".htmlspecialchars($config->site_name)."</title><meta http-equiv=\"Content-Type\" content=\"text/html; charset=".trans('_LANG_CHARSET').'" />'.
			'<meta name="robots" content="index, follow" />'.
			'<meta name="generator" content="'.$template->getGenerator().'" />'.
			'<meta name="keywords" content="'.htmlspecialchars($config->keywords).'" />'.
			'<meta name="description" content="'.htmlspecialchars($config->description)."\" /></head><body>";
		$res .="<center><img src=\"".Imageset('arta.png')."\"/><br/><h3>".$config->offline_msg."</h3><br/><form>Bypass Code: <input type=\"password\" name=\"bypass_code\"/> <input type=\"submit\" value=\"Bypass Offline mode\"/></form></center></body></html>";
		return $res;
	}

	static function MessageEffect(){
		return "<script>setTimeout(\"$$('div.message.tip, div.message.warning, div.message.error').each(function(e){new Effect.Highlight(e, {keepBackgroundImage:true, duration: 3});});\", 2000);</script>";
	}
	
	static function MessageCSS(){
		return "<link href=\"media/styles/msg.css\" rel=\"stylesheet\" type=\"text/css\"></link>";
	}

	static function SortControls($columns=array(), $def_c, $def_d='asc'){
		$def_c=getVar('order_by', $def_c, '', 'string');
		$def_d=getVar('order_dir',$def_d, '', 'string');
		$res="<div class=\"sortcontrols\"><form name=\"sortform\" method=\"get\" action=\"".ArtaURL::getURL()."\">";
		$urls=ArtaURL::breakupQuery($_SERVER['QUERY_STRING']);
		if(isset($_REQUEST['order_by'])){
			unset($urls['order_by']);
		}
		if(isset($_REQUEST['order_dir'])){
			unset($urls['order_dir']);
		}
		foreach($urls as $k=>$v){
			$v=htmlspecialchars($v);
			$k=htmlspecialchars($k);
			$res .="<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\"/>";
		}
		$res .=trans('SORT BY')." : <select name=\"order_by\">\n";
		foreach($columns as $k=>$v){
			if($def_c == $k){
				$s=" selected=\"selected\"";
			}else{$s='';}
			$k=htmlspecialchars($k);
			$v=htmlspecialchars($v);
			$res .="\t<option value=\"{$k}\"{$s}>{$v}</option>\n";
		}		
		$res .="</select>\n";
		if($def_d == 'asc'){$asc=" selected=\"selected\"";}else{$asc='';}
		if($def_d == 'desc'){$desc=" selected=\"selected\"";}else{$desc='';}
		$res .="<select name=\"order_dir\">\n<option value=\"asc\"{$asc}>".trans('ASCENDING')."</option>\n<option value=\"desc\"{$desc}>".trans('DESCENDING')."</option>\n</select>\n";
		$res .=' <input type="submit" value="'.trans('GO').'"/>';
		$res .="\n</form></div>";
		return $res;
	}

	static function SortLink($title, $name, $defd='asc'){
		$by=getVar('order_by', false, '', 'string');
		$dir=getVar('order_dir', $defd, '', 'string');
		$dir=strtolower($dir);
		if($by==$name){
			if($dir=='asc'){
				$img='<img src="'.Imageset('asc.png').'"/> ';
			}else{
				$img='<img src="'.Imageset('desc.png').'"/> ';
			}
		}else{
			$img='';
		}
		if($dir=='asc'){
			$dir='desc';
		}else{
			$dir='asc';
		}
		$vars=ArtaURL::breakupQuery(ArtaURL::getQuery());
		unset($vars['order_by']);
		unset($vars['order_dir']);
		return '<a href="'.htmlspecialchars('index.php?'.ArtaString::stickVars($vars).'&order_by='.$name.'&order_dir='.$dir).'">'.$img.htmlspecialchars($title).'</a>';
	}

	static function SortResult($defb, $defd='ASC', $before=null, $after=null){
		if((string)$before !== ''){
			$before .=',';
		}
		if((string)$after !== ''){
			$after =','.$after;
		}
		$by=getVar('order_by', $defb, '', 'string');

		$db=ArtaLoader::DB();
		$by = $db->getCEscaped($by);		
		$dir=getVar('order_dir', $defd, '', 'string');
		if($dir !== 'asc' && $dir !== 'desc'){
			$dir='asc';
		}
		return ' ORDER BY '.$before.'`'.$by.'` '.strtoupper($dir).' '.$after;
	}

	static function FilterControls($name, $columns, $title){
		$urls=ArtaURL::breakupQuery($_SERVER['QUERY_STRING']);
		$newurls=$urls;
		foreach($newurls as $k=>$v){
			if($k=='where['.$name.']'){
				unset($newurls[$k]);
			}
		}
		
		$newurls='index.php?'.ArtaURL::makeupQuery($newurls);
		
		$res="<div class=\"filtercontrols\"><form name=\"{$name}filterform\" method=\"get\" action=\"".ArtaURL::getURL()."\" >";
		$y=false;
		if(isset($urls['where['.$name.']'])){
			$y=true;
			unset($urls['where['.$name.']']);
		}
		
		foreach($urls as $k=>$v){
			$v=htmlspecialchars($v);
			$k=htmlspecialchars($k);
			$res .="<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\"/>\n";
		}
		$name=htmlspecialchars($name);
		$newurls=htmlspecialchars($newurls);
	
		$def= $y==false ? " selected=\"selected\"" : '';
		$res .="<select name=\"where[{$name}]\" id=\"where[{$name}]\" onchange=\"if(this.options[0].selected == true){document.location.href='{$newurls}';}else{document.{$name}filterform.submit();}\">\n";
		$res .="<option{$def}>".sprintf(trans('FILTER BY_'), htmlspecialchars($title))."</option>";
		$where=getVar('where', array(), '', 'array');

		foreach($columns as $k=>$v){
			
			if(isset($where[$name]) && $where[$name] == $k ){
				$s=" selected=\"selected\"";
			}else{$s='';}
			$k=htmlspecialchars($k);
			$v=htmlspecialchars($v);
			$res .="\t<option value=\"{$k}\"{$s}>{$v}</option>\n";
		}
		$res .="</select>\n";
		$res .="</form></div>";
		return $res;
	}
	
	static function FilterFindControls($name, $title){
		$urls=ArtaURL::breakupQuery($_SERVER['QUERY_STRING']);
		$newurls=$urls;
		foreach($newurls as $k=>$v){
			if($k=='where['.$name.']'){
				unset($newurls[$k]);
			}
		}
		
		$newurls='index.php?'.ArtaURL::makeupQuery($newurls);
		
		$res="<div class=\"filtercontrols\"><form name=\"{$name}searchfilterform\" method=\"get\" action=\"".ArtaURL::getURL()."\" >";
		$current=null;
		if(isset($urls['where['.$name.']'])){
			$current=$urls['where['.$name.']'];
			unset($urls['where['.$name.']']);
		}
		$current=htmlspecialchars($current);
		foreach($urls as $k=>$v){
			$v=htmlspecialchars($v);
			$k=htmlspecialchars($k);
			$res .="<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\"/>\n";
		}
		$name=htmlspecialchars($name);
		$newurls=htmlspecialchars($newurls);
		
		if($current==false){
			$current=sprintf(trans('FILTER BY_'), htmlspecialchars($title));
		}
		
		$clickto=trans('CLEAR TO RESET FILTER');
		$res .="<input name=\"where[{$name}]\" id=\"where[{$name}]\" class=\"acceptRet\"  title=\"$clickto\" style=\"font-size:10px;\" value=\"$current\" onfocus=\"if(this.value=='".JSValue(sprintf(trans('FILTER BY_'), htmlspecialchars($title)), true)."'){this.value='';}\" onblur=\"if(this.value==''){this.value='".JSValue(sprintf(trans('FILTER BY_'), htmlspecialchars($title)), true)."';}\"/>\n";
		$go=trans('GO');
		$res .="<input style=\"font-size:10px;\" type=\"button\" onclick=\"if($('where[{$name}]').value!=''){document.{$name}searchfilterform.submit();}else{document.location.href='$newurls';}\" value=\"$go\"/>";

		$res .="\n";
		$res .="</form></div>";
		return $res;
	}

	static function FilterResult($where_suffix=null, $operators=array(), $tbl_name=array()){
		$db=ArtaLoader::DB();
		$data=getVar('where', array(), '', 'array');
		$res='';
		if(count($data) !== 0){
			foreach($data as $k=>$v){
				if(!isset($operators[$k])){
					$operators[$k]='=';
				}
				if(is_int(strpos(strtoupper($operators[$k]), 'LIKE'))){
					
					$val='%'.$db->getEscaped($v, true).'%';
				}else{
					$val=$db->getEscaped($v);
				}
				if(is_array($tbl_name) && isset($tbl_name[$k])){
					$tbl='`'.$db->getCEscaped($tbl_name[$k])."`.";
				}elseif(!is_array($tbl_name) && (string)$tbl_name!=''){
					$tbl='`'.$db->getCEscaped($tbl_name)."`.";
				}else{
					$tbl='';
				}
				
				
				$res .=$tbl.'`'.$db->getCEscaped($k)."` ".$operators[$k].' '.
						$db->Quote($val,false)." AND ";
			}
			$res=substr($res, 0, strlen($res)-5);
			if((string)$where_suffix != ''){
				$where_suffix=" AND ( ".$where_suffix." )";
			}
			$res='WHERE ('.$res.")".$where_suffix;
		}else{
			if((string)$where_suffix != ''){
				$res = 'WHERE '.$where_suffix;
			}else{
				$res ='';
			}
		}
		return ' '.$res;
	}

	static function addBooleanControlsScript(){
		$template=ArtaLoader::Template();
		$is=$template->getImgSetName();
		$code="<script>";
		$code .='function setBoolean(imgid, t, f){
		succ=arguments[3];
		fail=arguments[4];
		if(typeof succ != "function"){
			succ= Prototype.emptyFunction;
		}
		if(typeof fail != "function"){
			fail= Prototype.emptyFunction;
		}
		if($(imgid).alt == "'.trans('NO').'" || 
			$(imgid).alt == "'.JSValue(trans('NO')).' - '.JSValue(trans('ERROR')).'"){
			url=t;
		}else{
			url=f;
		}
		new Ajax.Request(client_url+url, {
				method: "get",
				parameters: {token: "'.ArtaSession::genToken().'"},
				onCreate: function(transport) {
					$(imgid).src=client_url+"imagesets/'.$is.'/loading_small.gif";
					$(imgid).style.cursor="wait";
				},
				onSuccess: function(transport) {
					$(imgid).style.display="none";
					new Effect.Appear($(imgid));
					if($(imgid).alt == "'.JSValue(trans('NO')).'" ||
						$(imgid).alt == "'.JSValue(trans('NO')).' - '.JSValue(trans('ERROR')).'"){
						$(imgid).style.cursor="pointer";
						$(imgid).src=client_url+"imagesets/'.$is.'/true.png";
						$(imgid).alt="'.JSValue(trans('YES')).'";
						$(imgid).title="'.JSValue(trans('YES')).'";
					}else{
						$(imgid).style.cursor="pointer";
						$(imgid).src=client_url+"imagesets/'.$is.'/false.png";
						$(imgid).alt="'.JSValue(trans('NO')).'";
						$(imgid).title="'.JSValue(trans('NO')).'";
					}
					succ = succ.curry($(imgid),$(imgid).alt=="'.JSValue(trans('YES')).'",transport);
					succ();				
				},
				onFailure: function(transport){
					if($(imgid).alt == "'.JSValue(trans('NO')).'" ||
						$(imgid).alt == "'.JSValue(trans('NO')).' - '.JSValue(trans('ERROR')).'"){
						$(imgid).style.cursor="pointer";
						$(imgid).src=client_url+"imagesets/'.$is.'/false.png";
						$(imgid).alt="'.JSValue(trans('NO')).' - '.JSValue(trans('ERROR')).'";
						$(imgid).title="'.JSValue(trans('NO')).' - '.JSValue(trans('ERROR')).'";
					}else{
						$(imgid).style.cursor="pointer";
						$(imgid).src=client_url+"imagesets/'.$is.'/true.png";
						$(imgid).alt="'.JSValue(trans('YES')).' - '.JSValue(trans('ERROR')).'";
						$(imgid).title="'.JSValue(trans('YES')).' - '.JSValue(trans('ERROR')).'";
					}
					errmsg=\''.JSValue(trans('ERROR')).'\';
					if(transport.responseText.match(/<span class="errornum">(.*)<\/span>/i)){
						errmsg+=": "+(transport.responseText.match(/<span class="errornum">(.*)<\/span>/mi)[1]);
					}
					if(transport.responseText.match(/<span class="errortype">(.*)<\/span>/i)){
						errmsg+=" - "+(transport.responseText.match(/<span class="errortype">(.*)<\/span>/mi)[1]);
					}
					if(transport.responseText.match(/<div class="errormsg">(.*)<\/div>/i)){
						errmsg+="\n"+(transport.responseText.match(/<div class="errormsg">(.*)<\/div>/mi)[1]);
					}
					alert(errmsg);
					fail= fail.curry($(imgid),$(imgid).alt=="'.JSValue(trans('YES')).'",transport);
					fail();
				}
				
			});
			
		}';
		$code .="</script>";
		if(!$template->added('afterbody', $code)){
			$template->addtoTmpl($code, 'afterbody');
		}}

	static function BooleanControls($current, $maketrue, $makefalse, $succJSFunc=null, $failJSFunc=null){
		$true=JSValue($maketrue, true);
		$false=JSValue($makefalse, true);
		ArtaTagsHtml::addBooleanControlsScript();
		$current= (bool)$current;
		$id=ArtaString::makeRandStr(9);
		if($current == true){
			return "<img src=\"".Imageset('true.png')."\" style=\"cursor: pointer;\" alt=\"".trans('YES')."\" title=\"".trans('YES')."\" onclick=\"setBoolean('{$id}', '{$true}', '{$false}'".($succJSFunc?', '.htmlspecialchars($succJSFunc):'').($failJSFunc?', '.htmlspecialchars($failJSFunc):'').")\" id=\"{$id}\"/>";
		}else{
			return "<img src=\"".Imageset('false.png')."\" style=\"cursor: pointer;\" alt=\"".trans('NO')."\" title=\"".trans('NO')."\" onclick=\"setBoolean('{$id}', '{$true}', '{$false}'".($succJSFunc?', '.htmlspecialchars($succJSFunc):'').($failJSFunc?', '.htmlspecialchars($failJSFunc):'').")\" id=\"{$id}\"/>";
		}
	}

	static function LimitControls($count, $unique=null){
		if($unique!=null){
			$unique=$unique.'_';
		}else{
			$unique='';
		}
		if(isset($_SESSION[$unique.'list_limit']) && getVar($unique.'limit',false, '', 'int')===false && is_numeric($_SESSION[$unique.'list_limit'])){
			ArtaRequest::addVar($unique.'limit',$_SESSION[$unique.'list_limit']);
		}
		if(!@(int)$_SESSION[$unique.'list_limit'] !== getVar($unique.'limit',false, '', 'int') && getVar($unique.'limit',false, '', 'int')!==false){
			$_SESSION[$unique.'list_limit']=getVar($unique.'limit',false, '', 'int');
		}
		$count=(int)$count;
		$config=ArtaLoader::Config();
		$limitstart=getVar($unique.'limitstart', 0, '', 'int');
		$limit=getVar($unique.'limit', (int)$config->list_limit, '', 'int');
		if($limit !==0 && $count !==0){
			if($count/$limit !==(int)sprintf('%u', $count / $limit)){$i=1;}else{$i=0;}
			$page_count=(int)sprintf('%u', $count / $limit) +$i;
			$current_page=(int)sprintf('%u', $limitstart / $limit) + 1;
		}else{
			$page_count=1;
			$current_page=1;
		}
		$res='<div class="limitcontrols"><a style="display: block; position: absolute; margin-top: -400px;" name="lim">&nbsp;</a><form method="get" name="'.$unique.'limitform" action="'.ArtaURL::getURL().'">';
		$urls=ArtaURL::breakupQuery($_SERVER['QUERY_STRING']);
		if(isset($urls[$unique.'limit'])){
			unset($urls[$unique.'limit']);
		}
		if(isset($urls[$unique.'limitstart'])){
			unset($urls[$unique.'limitstart']);
		}
		foreach($urls as $k=>$v){
			$v=htmlspecialchars($v);
			$k=htmlspecialchars($k);
			$res .="<input type=\"hidden\" name=\"{$k}\" value=\"{$v}\"/>\n";
		}
		$res .="<input type=\"hidden\" name=\"{$unique}limitstart\" value=\"{$limitstart}\"/>\n";
		$res .="<table class=\"limittable\"><tr>";
		$res .="<td class=\"limittd\">".trans('Limit Count').": <select name=\"{$unique}limit\" onchange=\"document.{$unique}limitform.{$unique}limitstart.value=0;document.{$unique}limitform.submit();\">\n";
		$lists=array(10=>10, 20=>20, 25=>25, 30=>30, 40=>40, 50=>50, 100=>100);
		if(!isset($lists[$limit]) && $limit!==0){$lists[$limit]=$limit;ksort($lists);}
		$lists[trans('ALL')]=0;
		foreach($lists as $k=>$v){
			if($v == $limit){$s=" selected=\"selected\"";}else{$s='';}
			$v=htmlspecialchars($v);
			$k=htmlspecialchars($k);
			$res .="<option value=\"{$v}\"{$s}>".$k."</option>\n";
		}
		
		$res .="</select></td>";
		if($current_page == 1){
			$res .="<td class=\"firsttd off\">".trans('FIRST_PAGE')."</td>";
		}else{
			$res .="<td class=\"firsttd\"><a href=\"#lim\" onclick=\"document.{$unique}limitform.{$unique}limitstart.value=0;document.{$unique}limitform.submit();\">".trans('FIRST_PAGE')."</a></td>";
		}
		if($current_page == 1){
			$res .="<td class=\"prevtd off\">".trans('PREV_PAGE')."</td>";
		}else{
			$res .="<td class=\"prevtd\"><a href=\"#lim\" onclick=\"document.{$unique}limitform.{$unique}limitstart.value= parseInt(document.{$unique}limitform.{$unique}limitstart.value) - {$limit}; document.{$unique}limitform.submit();\">".trans('PREV_PAGE')."</a></td>";
		}
		
		$i=1;
		$res .="<td class=\"pagestd\">";
		while($i <= $page_count){
			if($i == $current_page){$s='<b>';$s2="</b>";}else{$s='';$s2='';}
			$res .="{$s}<a href=\"#lim\" onclick=\"document.{$unique}limitform.{$unique}limitstart.value= ".($i -1 ) * $limit."; document.{$unique}limitform.submit();\">".$i."</a>{$s2}";
			$i++;
		}
		
		$res .="</td>";

		if($current_page == $page_count){
			$res .="<td class=\"nexttd off\">".trans('NEXT_PAGE')."</td>";
		}else{
			$res .="<td class=\"nexttd\"><a href=\"#lim\" onclick=\"document.{$unique}limitform.{$unique}limitstart.value= parseInt(document.{$unique}limitform.{$unique}limitstart.value) + {$limit}; document.{$unique}limitform.submit();\">".trans('NEXT_PAGE')."</a></td>";
		}

		if($current_page == $page_count){
			$res .="<td class=\"firsttd off\">".trans('LAST_PAGE')."</td>";
		}else{
			$res .="<td class=\"firsttd\"><a href=\"#lim\" onclick=\"document.{$unique}limitform.{$unique}limitstart.value=".$limit * ($page_count - 1).";document.{$unique}limitform.submit();\">".trans('LAST_PAGE')."</a></td>";
		}
		$res .="</tr></table></form></div>";
		return $res;

	}

	static function LimitResult($arr=null, $unique=null){
		if($unique!=null){
			$unique=$unique.'_';
		}else{
			$unique='';
		}
		if(isset($_SESSION[$unique.'list_limit']) && getVar($unique.'limit',false, '', 'int')===false && is_numeric($_SESSION[$unique.'list_limit'])){
			ArtaRequest::addVar($unique.'limit',$_SESSION['list_limit']);
		}
		if(!@(int)$_SESSION[$unique.'list_limit'] !== getVar($unique.'limit',false, '', 'int') && getVar($unique.'limit',false, '', 'int')!==false){
			$_SESSION[$unique.'list_limit']=getVar($unique.'limit',false, '', 'int');
		}
		$config=ArtaLoader::Config();
		$limit=getVar($unique.'limit', (int)$config->list_limit, '', 'int');
		$limitstart=getVar($unique.'limitstart', 0, '', 'int');
		if(is_numeric($limit) == false){$limit=$config->list_limit;}
		if(is_numeric($limitstart) == false){$limitstart=0;}
		if($arr==null && !is_array($arr)){
			if((int)$limit!==0){
				return ' LIMIT '.$limitstart.','.$limit;
			}else{
				return '';
			}
		}else{
			if((int)$limit!==0){
				return array_slice($arr, $limitstart, $limit);
			}else{
				return $arr;
			}
		}
	}

	static function CAPTCHA($name, $id=null, $params=array()){
		$s='';
		$denied=array('type', 'name', 'value', 'class');
		foreach($params as $p=>$pv){
			if(in_array($p, $denied) == false){
				$s .=' '.$p.'="'.htmlspecialchars($pv).'"';
			}
		}
		$pkg=ArtaLoader::Package();
		$address=("index.php?pack=captcha&type=".$pkg->getSetting('captcha_format', 'png', 'captcha', 'site').($id==null?'':'&id='.urlencode($id)));
		$uniqid=ArtaString::makeRandStr();
		$c=ArtaLoader::Config();
		if($c->sef==0|| $id!=null){
			$char='&';
		}else{
			$char='?';
		}
		if($_SERVER['QUERY_STRING_ORIGINAL'] !==''){
			$char='&';
		}
		$name=htmlspecialchars($name);
		$address=htmlspecialchars($address);
		$r="\n<br/><img src=\"{$address}\" id=\"{$uniqid}\" align=\"left\" onload=\"$('cant_read_image').style.visibility='hidden';\"/>&nbsp;&nbsp;".trans('TYPE THE CODE')."<br/>&nbsp;&nbsp;<input type=\"text\" name=\"{$name}\" value=\"\"{$s}/><br/>&nbsp;&nbsp;\n<img src=\"".Imageset('loading_small.gif')."\" id=\"cant_read_image\" style=\"\"/><a style=\"color:red;cursor:pointer;\" onclick=\"$('{$uniqid}').src=$('{$uniqid}').src+'{$char}'+Math.random(); $('cant_read_image').style.visibility='visible';\">".trans('I CANT READ THIS')."</a>";
		return $r;
	}

	static function radio($name, $data, $selected, $params=array()){
		$name=htmlspecialchars($name);
		$r="\n";
		$denied=array('type', 'name', 'value', 'checked');
		foreach($data as $k=>$v){
			if($k == $selected){$s=' checked="checked"';}else{$s='';}
			if(isset($params[$k]) && is_array($params[$k])){
				foreach($params[$k] as $p=>$pv){
					if(in_array($p, $denied) == false){
						if($p=='class'){
							$s .=' class="'.htmlspecialchars($pv).'"';
						}
						$s .=' '.$p.'="'.htmlspecialchars($pv).'"';
					}
				}
			}
			if(isset($params[$k]['id'])==false){
				$rand='radio_'.ArtaString::makeRandStr();
				$id=" id=\"{$rand}\"";
			}else{
				$rand=$params[$k]['id'];
				$id='';
			}
			$k=htmlspecialchars($k);
			$v=htmlspecialchars($v);
			$r .="<span><input type=\"radio\" name=\"{$name}\" value=\"{$k}\"{$s} {$id}/><label for=\"{$rand}\">{$v}</label></span> \n";
		}
		return $r;
	}

	static function checkbox($name, $value, $title, $selected, $params=array()){
		$name=htmlspecialchars($name);
		$value=htmlspecialchars($value);
		$title=htmlspecialchars($title);
		if($selected){$s=' checked="checked"';}else{$s='';}
		$denied=array('type', 'name', 'value', 'class', 'checked');
		foreach($params as $p=>$pv){
			if(in_array($p, $denied) == false){
				$s .=' '.$p.'="'.htmlspecialchars($pv).'"';
			}
		}
		if(isset($params['id'])==false){
			$rand='checkbox_'.ArtaString::makeRandStr();
			$id=" id=\"{$rand}\"";
		}else{
			$rand=$params['id'];
			$id='';
		}
		$r ="<span><input type=\"checkbox\" name=\"{$name}\" value=\"{$value}\"{$s}{$id}/><label for=\"{$rand}\">{$title}</label></span> \n";
		return $r;
	}

	static function select($name, $data, $selected, $type=0, $select_params=array(), $params=array()){
		while(substr($name,strlen($name)-2)=='[]'){
			$name=substr($name,0,strlen($name)-2);
		}
		$name=htmlspecialchars($name);
		if((int)$type==2){
			$name.='[]';
		}
		if($type==2){
			$s=" size=\"5\" multiple=\"multiple\" title=\"".trans('YOU CAN USE CTRL')."\"";
		}elseif($type==1){
			$s=" size=\"5\"";
		}else{
			$s='';
		}
		$denied=array('type', 'name', 'value', 'class', 'checked');
		foreach($select_params as $p=>$pv){
			if(in_array($p, $denied) == false){
				$s .=' '.$p.'="'.htmlspecialchars($pv).'"';
			}
		}
		
		$r="\n<select name=\"{$name}\"{$s} >\n";
		$denied=array('value', 'selected');
		foreach($data as $k=>$v){
			if(is_array($v)){
				if(count($v)){
					$k=htmlspecialchars($k);
					$r .="<optgroup label=\"{$k}\">\n";
					foreach($v as $k2=>$v2){
						if(is_array($selected) == false){
							if($k2 == $selected){
								$s=' selected="selected"';
							}else{
								$s='';
							}
						}else{
							if(in_array($k2, $selected)){
								$s=' selected="selected"';
							}else{
								$s='';
							}
						}
	
	
						if(isset($params[$k2]) && is_array($params[$k2])){
							foreach($params[$k2] as $p=>$pv){
								if(in_array($p, $denied) == false){
									$s .=' '.$p.'="'.htmlspecialchars($pv).'"';
								}
							}	
						}
						$k2=htmlspecialchars($k2);
						$v2=htmlspecialchars($v2);
						$r .="\t<option value=\"{$k2}\"{$s}>{$v2}</option>\n";
					}
					$r .="</optgroup>";
				}
			}else{
				if(is_array($selected) == false){
					
					if($k == $selected){
						$s=' selected="selected"';
					}else{
						$s='';
					}
				}else{
					if(in_array($k, $selected)){
						$s=' selected="selected"';
					}else{
						$s='';
					}
				}


				if(isset($params[$k]) && is_array($params[$k])){
					foreach($params[$k] as $p=>$pv){
						if(in_array($p, $denied) == false){
							$s .=' '.$p.'="'.htmlspecialchars($pv).'"';
						}
					}	
				}
				$k=htmlspecialchars($k);
				$v=htmlspecialchars($v);
				$r .="\t<option value=\"{$k}\"{$s}>{$v}</option>\n";
			}
		}
		$r .="</select>\n";
		return $r;
	}

	static function text($name, $value, $type='text', $params=array()){
		$name=htmlspecialchars($name);
		$value=htmlspecialchars($value);
		$type=htmlspecialchars($type);
		$s='';
		$denied=array('type', 'name', 'value', 'class');
		foreach($params as $p=>$pv){
			if(in_array($p, $denied) == false){
				$s .=' '.$p.'="'.htmlspecialchars($pv).'"';
			}
		}
		$r="\n<input type=\"{$type}\" name=\"{$name}\" value=\"{$value}\"{$s}/>";
		return $r;
	}

	static function textbox($name, $value, $params=array()){
		$name=htmlspecialchars($name);
		$value=htmlspecialchars($value);
		$s='';
		$denied=array('name', 'class');
		foreach($params as $p=>$pv){
			if(in_array($p, $denied) == false){
				$s .=' '.$p.'="'.htmlspecialchars($pv).'"';
			}
		}
		$r="\n<textarea name=\"{$name}\"{$s}>{$value}</textarea>";
		return $r;
	}


	static function button($name, $value, $type='button', $params=array()){
		return self::text($name, $value, $type, $params);
	}

	static function PreFormItem($name, $value, $vartype, $vartypedata=''){
		$vartype = strtolower($vartype);
		$options=array('name'=>$name, 'vartype'=>$vartype, 'value'=>$value);
		$res = '';
		
		switch($vartype){
			case 'custom':
				ob_start();
				eval($vartypedata);
				$res=ob_get_contents();
				ob_end_clean();
			break;
			case 'date': 
				/** Uses "calendar" in $insertTime=false mode. **/
				return self::PreFormItem($name, $value, 'calendar', '$options["insertTime"]=false;');
			break;
			case 'calendar':
				eval($vartypedata);
				// params : $options['insertTime'] = Insert datetime(true) or date only(false)
				if(!isset($options['insertTime'])){$options['insertTime']=true;}
				$u=ArtaString::makeRandStr();
				$res .=ArtaTagsHtml::text($name, $value, 'text', array('id'=>$u));
				$res .=ArtaTagsHtml::Calendar($u, null, $options['insertTime']);
			break;
			case 'users':
				eval($vartypedata);
				// params : $options['return'] = one of #__users table columns
				//	$options['show'] = one of #__users table columns
				// $options['select_type'] = 0 | 1 | 2 (refer to ArtaTagsHtml::select )
				if(!isset($options['return'])){$options['return']='id';}
				if(!isset($options['show'])){$options['show']='username';}
				if(!isset($options['select_type'])){$options['select_type']=0;}
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__users ORDER BY usergroup,id');
				$users=$db->loadObjectList();
				$result=array();
				$i=0;
				foreach($users as $v){
					$i=$v->usergroup;
					$db->setQuery('SELECT name FROM #__usergroups WHERE id='.$db->Quote($i));
					$ug=$db->loadObject();
					$result[$ug->name][$v->$options['return']]=$v->$options['show'];
				}
				if($options['select_type']==2){
					$name=$name.'[]';
				}
				$res=ArtaTagsHtml::select($name, $result, $value, $options['select_type']);
			break;
			case 'usergroups':
				eval($vartypedata);
				// params : $options['return'] = one of #__usergroups table columns
				//	$options['show'] = one of #__usergroups table columns
				// $options['select_type'] = 0 | 1 | 2 (refer to ArtaTagsHtml::select )
				// $options['guest'] = 0 | 1 show guest or not
				if(!isset($options['return'])){$options['return']='id';}
				if(!isset($options['show'])){$options['show']='title';}
				if(!isset($options['select_type'])){$options['select_type']=0;}
				if(!isset($options['guest'])){$options['guest']=0;}
				$db=ArtaLoader::DB();
				/*$where = $options['guest']!==1 ? " WHERE name != 'guest'" : '';
				$db->setQuery('SELECT * FROM #__usergroups'.$where.' ORDER BY id');
				$users=$db->loadObjectList();*/
				$users=ArtaUtility::keyByChild(ArtaUsergroup::getItems(), 'id');
				if($options['guest']!==1){
					unset($users[0]);
				}
				$result=array();
				foreach($users AS $v){
					$result[$v->$options['return']]=$v->$options['show'];
				}
				if($options['select_type']==2){
					$name=$name.'[]';
				}
				$res =ArtaTagsHtml::select($name, $result, $value, $options['select_type']);
			break;
			case 'packages':
				eval($vartypedata);
				// params : $options['return'] = one of #__users table columns
				//	$options['show'] = one of #__users table columns
				// $options['select_type'] = 0 | 1 | 2 (refer to ArtaTagsHtml::select )
				if(!isset($options['return'])){$options['return']='id';}
				if(!isset($options['show'])){$options['show']='title';}
				if(!isset($options['select_type'])){$options['select_type']=0;}
				
				$db=ArtaLoader::DB();
				$db->setQuery('SELECT * FROM #__packages ORDER BY id');
				$users=$db->loadObjectList();
				$result=array();
				foreach($users AS $v){
					$result[$v->$options['return']]=$v->$options['show'];
				}
				if($options['select_type']==2){
					$name=$name.'[]';
				}
				$res =ArtaTagsHtml::select($name, $result, $value, $options['select_type']);
			break;
			case 'modules':
				eval($vartypedata);
				// params : $options['return'] = one of #__users table columns
				//	$options['show'] = one of #__users table columns
				// $options['select_type'] = 0 | 1 | 2 (refer to ArtaTagsHtml::select )
				// $options['client']=Target client
				if(!isset($options['client'])){$options['client']='all';}
				if(!isset($options['return'])){$options['return']='id';}
				if(!isset($options['show'])){$options['show']='title';}
				if(!isset($options['select_type'])){$options['select_type']=0;}
				$db=ArtaLoader::DB();
				if($options['client']=='all'){
				$db->setQuery('SELECT * FROM #__modules ORDER BY id');
				}else{
					$db->setQuery("SELECT * FROM #__modules WHERE client=".$db->Quote($options['client'])." ORDER BY id");
				}
				$users=$db->loadObjectList();
				$result=array();
				foreach($users AS $v){
					$result[$v->client][$v->$options['return']]=$v->$options['show'];
				}
				if($options['select_type']==2){
					$name=$name.'[]';
				}
				$res =ArtaTagsHtml::select($name, $result, $value, $options['select_type']);
			break;
			case 'plugins':
				eval($vartypedata);
				// params : $options['return'] = one of #__users table columns
				//	$options['show'] = one of #__users table columns
				// $options['select_type'] = 0 | 1 | 2 (refer to ArtaTagsHtml::select )
				// $options['client']=Target client
				if(!isset($options['client'])){$options['client']='all';}
				if(!isset($options['return'])){$options['return']='id';}
				if(!isset($options['show'])){$options['show']='title';}
				if(!isset($options['select_type'])){$options['select_type']=0;}
				$db=ArtaLoader::DB();
				if($options['client']=='all'){
				$db->setQuery('SELECT * FROM #__plugins ORDER BY id');
				}else{
					$db->setQuery("SELECT * FROM #__plugins WHERE client=".$db->Quote($options['client'])." ORDER BY id");
				}
				$users=$db->loadObjectList();
				$result=array();
				foreach($users AS $v){
					$result[$v->client][$v->$options['return']]=$v->$options['show'];
				}
				if($options['select_type']==2){
					$name=$name.'[]';
				}
				$res =ArtaTagsHtml::select($name, $result, $value, $options['select_type']);
			break;
			case 'languages':
				eval($vartypedata);
				// params : $options['return'] = one of #__users table columns
				//	$options['show'] = one of #__users table columns
				// $options['select_type'] = 0 | 1 | 2 (refer to ArtaTagsHtml::select )
				// client = all | site | admin
				if(!isset($options['return'])){$options['return']='name';}
				if(!isset($options['show'])){$options['show']='title';}
				if(!isset($options['select_type'])){$options['select_type']=0;}
				if(!isset($options['client'])){$options['client']='all';}
				$db=ArtaLoader::DB();
				if($options['client']=='all'){$sql='SELECT * FROM #__languages ORDER BY id';}else{
					$sql="SELECT * FROM #__languages WHERE client=".$db->Quote($options['client'])." ORDER BY id";
				}

				$db->setQuery($sql);
				$users=$db->loadObjectList();
				$result=array();
				foreach($users AS $v){
					$result[$v->client][$v->$options['return']]=$v->$options['show'];
				}
				if($options['select_type']==2){
					$name=$name.'[]';
				}
				$res =ArtaTagsHtml::select($name, $result, $value, $options['select_type']);
			break;
			case 'templates':
				eval($vartypedata);
				// params : $options['return'] = one of #__users table columns
				//	$options['show'] = one of #__users table columns
				// $options['select_type'] = 0 | 1 | 2 (refer to ArtaTagsHtml::select )
				// $options['preview'] = true | false (Preview template?)
				// $options['client']=Target client
				if(!isset($options['client'])){$options['client']='all';}
				if(!isset($options['return'])){$options['return']='name';}
				if(!isset($options['show'])){$options['show']='title';}
				if(!isset($options['preview'])){$options['preview']=true;}
				if(!isset($options['select_type'])){$options['select_type']=0;}
				$db=ArtaLoader::DB();
				if($options['client']=='all'){$sql='SELECT * FROM #__templates ORDER BY id';}else{
					$sql="SELECT * FROM #__templates WHERE client=".$db->Quote($options['client'])." ORDER BY id";
				}
				$db->setQuery($sql);
				$users=$db->loadObjectList();
				$result=array();
				foreach($users AS $v){
					$result[$v->client][$v->$options['return']]=$v->$options['show'];
				}
				if(!array_key_exists('site', $result)){
					$result['site']=array();
				}
				if(!array_key_exists('admin', $result)){
					$result['admin']=array();
				}				
				if($options['select_type']==2){
					$name=$name.'[]';
				}
				$x=array();
				$preview='';
				$sp=array();
				if($options['preview']==true){
					$siteurl=ArtaURL::getSiteURL();
					$adminurl=ArtaURL::getAdminURL();
					$path=$siteurl;
					$id=ArtaString::makeRandStr(8);
					foreach($result['site'] as $key=>$tmpl){
						$tip=JSValue('<img style="position:absolute;z-index:20;" src="'.$siteurl.'templates/'.$key.'/thumb.png"/>');
						$x[$key]['onmouseover']='$("tmpl_preview_'.$id.'").innerHTML= "'.$tip.'";';
					}
					foreach($result['admin'] as $key=>$tmpl){
						$tip=JSValue('<img style="position:absolute;z-index:20;" src="'.$adminurl.'templates/'.$key.'/thumb.png"/>');
						$x[$key]['onmouseover']='$("tmpl_preview_'.$id.'").innerHTML= "'.$tip.'";';
						if($key==$value){
							$path=$adminurl;
						}
					}
					$preview='<span style="vertical-align:top;" id="tmpl_preview_'.$id.'"></span>';
					$sp=array('onmouseout'=>'$("tmpl_preview_'.$id.'").innerHTML= "";');
				}
				$res =ArtaTagsHtml::select($name, $result, $value, $options['select_type'], $sp, $x).$preview;
			break;
			case 'bool':
				eval($vartypedata);
				//params:
				// $options['yes'] = Yes word
				// $options['no'] = No word
				// $options['true_value'] = true (yes) Value
				// $options['false_value'] = false (no) Value
				//	$options['params'] = option params array
				if(!isset($options['params'])){$options['params']=array();}
				if(!isset($options['yes'])){$options['yes']=trans('YES');}
				if(!isset($options['no'])){$options['no']=trans('NO');}
				if(!isset($options['true_value'])){$options['true_value']=1;}
				if(!isset($options['false_value'])){$options['false_value']=0;}
				$res = ArtaTagsHtml::radio($name, array($options['false_value']=>$options['no'], $options['true_value']=>$options['yes']), $value, $options['params']);
			break;
			case 'radio':
				eval($vartypedata);
				//params:
				// $options['options'] = options array
				//	$options['params'] = option params array
				if(!isset($options['options'])){$options['options']=array();}
				if(!isset($options['params'])){$options['params']=array();}
				$res = ArtaTagsHtml::radio($name, $options['options'], $value, $options['params']);
			break;
			case 'select':
				eval($vartypedata);
				//params:
				// $options['options'] = selections array
				//	$options['params'] = select params array
				//	$options['o_params'] = Options params array
				// $options['select_type'] = select type
				if(!isset($options['select_type'])){$options['select_type']=0;}
				if(!isset($options['options'])){$options['options']=array();}
				if(!isset($options['params'])){$options['params']=array();}
				if(!isset($options['o_params'])){$options['o_params']=array();}
				$res = ArtaTagsHtml::select($name, $options['options'], $value, $options['select_type'], $options['params'],$options['o_params']);
			break;
			case 'text':
				eval($vartypedata);
				//params:
				// $options['type'] = text type : text|password
				//	$options['params'] = text params array
				if(!isset($options['params'])){$options['params']=array();}
				if(!isset($options['type'])){$options['type']='text';}
				$res = ArtaTagsHtml::text($name, $value, $options['type'], $options['params']);
			break;
			case 'textbox':
				eval($vartypedata);
				//params:
				//	$options['params'] = textbox params array
				if(!isset($options['params'])){$options['params']=array();}
				$res = ArtaTagsHtml::textbox($name, $value, $options['params']);
			break;
			case 'imagesets':
				eval($vartypedata);
				// params : $options['return'] = one of #__users table columns
				//	$options['show'] = one of #__users table columns
				// $options['select_type'] = 0 | 1 | 2 (refer to ArtaTagsHtml::select )
				// $options['preview'] = true | false (Preview imageset?)
				// $options['client']=Target client
				if(!isset($options['client'])){$options['client']='all';}
				if(!isset($options['return'])){$options['return']='name';}
				if(!isset($options['show'])){$options['show']='title';}
				if(!isset($options['select_type'])){$options['select_type']=0;}
				if(!isset($options['preview'])){$options['preview']=true;}
				$db=ArtaLoader::DB();
				if($options['client']=='all'){$sql='SELECT * FROM #__imagesets ORDER BY id';}else{
					$sql="SELECT * FROM #__imagesets WHERE client=".$db->Quote($options['client'])." ORDER BY id";
				}
				$db->setQuery($sql);
				$users=$db->loadObjectList();
				$result=array();
				foreach($users AS $v){
					$result[$v->client][$v->$options['return']]=$v->$options['show'];
				}
				
				if(!array_key_exists('site', $result)){
					$result['site']=array();
				}
				if(!array_key_exists('admin', $result)){
					$result['admin']=array();
				}				
				if($options['select_type']==2){
					$name=$name.'[]';
				}
				$x=array();
				$preview='';
				$sp=array();
				if($options['preview']==true){
					$siteurl=ArtaURL::getSiteURL();
					$adminurl=ArtaURL::getAdminURL();
					$path=$siteurl;
					$id=ArtaString::makeRandStr(8);
					foreach($result['site'] as $key=>$tmpl){
						$tip=JSValue('<img style="position:absolute;z-index:20;" src="'.$siteurl.'imagesets/'.$key.'/imageset_thumb.png"/>');
						$x[$key]['onmouseover']='$("imgset_preview_'.$id.'").innerHTML= "'.$tip.'";';
					}
					foreach($result['admin'] as $key=>$tmpl){
						$tip=JSValue('<img style="position:absolute;z-index:20;" src="'.$adminurl.'imagesets/'.$key.'/imageset_thumb.png"/>');
						$x[$key]['onmouseover']='$("imgset_preview_'.$id.'").innerHTML= "'.$tip.'";';
						if($key==$value){
							$path=$adminurl;
						}
					}
					$preview='<span style="vertical-align:top;" id="imgset_preview_'.$id.'"></span>';
					$sp=array('onmouseout'=>'$("imgset_preview_'.$id.'").innerHTML= "";');
				}
				
				if($options['select_type']==2){
					$name=$name.'[]';
				}
				$res =ArtaTagsHtml::select($name, $result, $value, $options['select_type'], $sp, $x).$preview;
			break;
		}
		return $res;
	}

	static function Calendar($target, $type=null, $insertTime=true){
		if($type==null){
			$c=ArtaLoader::Config();
			$type=$c->cal_type;
		}
		ArtaTagsHtml::addLibraryScript('artacalendar');
		ArtaTagsHtml::addCSS('media/styles/artacalendar.css');
		self::addArtaCalendarTranslation();
		$id='handler'.ArtaString::makeRandStr();
		$target=JSValue($target);
		$res2=" <img src=\"".Imageset('calendar.png')."\" alt=\"".trans('CALENDAR')."\" id=\"{$id}\"/>";
		$res="<script>\n\tnew ArtaCalendar('{$target}', {handler:'{$id}', type: '{$type}'".($insertTime==false?', format:\'%Y-%m-%d\'':'')."});</script>";
		$template=ArtaLoader::Template();
		if(!$template->added('beforebodyend', $res)){
			$template->addtoTmpl($res, 'beforebodyend');
		}
		return $res2;
	}
	
	static function ReorderControlsUP($link, $show, $alt=null, $enabled=true){
		if($alt==null){
			$alt=trans('MOVE UP');
		}
		$c='';
		if($show==true){
			if($enabled==true){
				$c.='<a href="'.htmlspecialchars($link).'" title="'.htmlspecialchars($alt).'">';
				$c.='<img src="'.Imageset('collapse.png').'" alt="'.htmlspecialchars($alt).'"/></a>';
			}
		}
		return $c;
	}
	
	static function ReorderControlsDOWN($link, $show, $alt=null, $enabled=true){
		if($alt==null){
			$alt=trans('MOVE DOWN');
		}
		$c='';
		if($show==true){
			if($enabled==true){
				$c.='<a href="'.htmlspecialchars($link).'" title="'.htmlspecialchars($alt).'">';
				$c.='<img src="'.Imageset('uncollapse.png').'" alt="'.htmlspecialchars($alt).'"/></a>';
			}
		}
		return $c;
	}
	
	static function msgBox($message, $type="tip"){
		ArtaTagsHtml::addtoTmpl(ArtaTagsHtml::MessageCSS(), 'head');
		ArtaTagsHtml::addtoTmpl(ArtaTagsHtml::MessageEffect(), 'beforebodyend');
		return '<div class="message '.strtolower($type).
							'">'.$message.'</div>';
	}
	
	static function openTranslation($title, $id, $type){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT id,title FROM #__languages WHERE client=\'site\'');
		$raw=$db->loadObjectList();
		$langs=array(trans('SELECT LANGUAGE'));
		foreach($raw as $v){
			$langs[$v->id]=$v->title;
		}
		$_id=ArtaString::makeRandStr();
		$r= '<form id="trans_'.$_id.'" action="index.php">';
		$r.=sprintf(trans('SHOW _ TRANS IN'), htmlspecialchars($title)).': '.self::select('lang', $langs, 0,'',array('onchange'=>'if(this.value!=\'0\'){$(\'trans_'.$_id.'\').submit();}'));
		$r.='<input type="hidden" name="pack" value="language">'
		.'<input type="hidden" name="view" value="new">'
		.'<input type="hidden" name="group" value="'.htmlspecialchars($type).'">'
		.'<input type="hidden" name="id" value="'.htmlspecialchars($id).'">'
		.'</form>';
		return $r;
	}
	
	static function AvatarTableDesc($u, $compact=false){
		if($u==null){
			return null;
		}
		$r='';
		$s=unserialize($u->settings);
		$m=unserialize($u->misc);
		
		$ug=ArtaUserGroup::getUsergroup($u->usergroup>0?$u->usergroup:0);
		
		if($ug){
			$r.='<tr><td nowrap="nowrap" class="avatarTable_ug">'.htmlspecialchars(trim($ug->title)).'</td></tr>';
		}
		
		if($compact==false){
			if(@trim($s->usertitle)!=''){
				$r.='<tr><td nowrap="nowrap" class="avatarTable_utitle">'.htmlspecialchars(trim($s->usertitle)).'</td></tr>';
			}
			$x='';	
					
			if(@trim($m->yahoo_id)!=''){
				$x.= '<img src="'.ArtaURL::getSiteURL().'packages/user/assets/images/im_yahoo.png" title="'.htmlspecialchars(trim($m->yahoo_id)).'" alt="Y!"/>&nbsp;';
			}
			
			if(@trim($m->gtalk_id)!=''){
				$x.= '<img src="'.ArtaURL::getSiteURL().'packages/user/assets/images/im_gtalk.png" title="'.htmlspecialchars(trim($m->gtalk_id)).'" alt="GTalk"/>&nbsp;';
			}
			
			if(@trim($m->weburl)!=''){
				$x.= '<a href="'.htmlspecialchars(trim($m->weburl)).'" target="_blank"><img src="'.Imageset('home_small.png').'" alt="Web"/></a>&nbsp;';
			}
					
			if($x!=''){
				$r.='<tr><td class="avatarTable_other">'.$x.'</td></tr>';
			}
		}
		
		$p=ArtaLoader::Plugin();
		$p->trigger('onShowAvatarTable', array(&$r, $u, $s, $compact));
		return $r;
	}
	
}
?>
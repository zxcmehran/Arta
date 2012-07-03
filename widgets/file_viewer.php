<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 3 $
 * @date		2009/10/29 18:02 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

if(!isset($_configuring)){
	
	function selectIcon($f){
		$ext=Artafile::getExt($f);
		switch(strtolower($ext)){
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'png':
			case 'bmp':
			case 'tif':
			case 'wmf':
				$img=Imageset('picture.png');
			break;
			case 'mp3':
			case 'wmv':
			case 'wav':
			case 'ogg':
				$img=Imageset('music.png');
			break;
			case 'wmv':
			case 'avi':
			case 'mov':
			case 'rm':
			case 'mp4':
			case 'mpg':
			case 'mpeg':
			case '3gp':
			case 'flv':
			case 'swf':
				$img=Imageset('video.png');
			break;
			case 'rar':
			case 'tar':
			case 'zip':
			case '7z':
			case 'tgz':
			case 'tbz':
			case 'bz':
			case 'gz':
			case 'z':
				$img=Imageset('archive.png');
			break;
			case 'pdf':
			case 'rtf':
			case 'doc':
			case 'docx':
			case 'xls':
			case 'xlsx':
			case 'ppt':
			case 'pptx':
			case 'txt':
		 	case 'xps':
		 		$img=Imageset('document.png');
	 		break;
	 		default:
	 			$img=Imageset('download.png');
 			break;
		}
		return $img;
	}
	if(isset($settings['files_description'])){
		$plug=ArtaLoader::Plugin();
		$plug->trigger('onShowBody', array(&$settings['files_description'], 'fileviewer-desc'));
		echo '<aside>'.$settings['files_description'].'</aside>';
	}
	if(@count($settings['selected_files_to_show'])){
		echo '<table>';
		foreach($settings['selected_files_to_show'] as $n=>$f){
			$size=@filesize(ARTAPATH_BASEDIR.'/'.$f);
			echo '<tr><td rowspan="2" width="48" align="center"><img src="'.selectIcon($f).'"/></td><td><span style="font-weight:bold;"><a target="_blank" href="'.htmlspecialchars($f).'">'.htmlspecialchars($n).'</a></span></td></tr><tr><td><small style="font-weight:bold;">';
			if($size!=false){
				if($size<1024){
					echo '1 KB';
				}elseif($size> 1024*1024){
					echo round($size/(1024*1024),1).' MB';
				}else{
					echo round($size/1024,1).' KB';
				}
			}
			echo '</small></td></tr>';
		}
		echo '</table>';
	}
}else{

	ArtaTagsHtml::addtoTmpl('<script>
	var browserURL=site_url+\'index.php?pack=filemanager&editor=1&tmpl=package\';
	</script>', 'beforebodyend');
?>
<a name="attach">&nbsp;</a><input type="button" onclick="openAttachments()" value="<?php echo trans('ADD FILE'); ?>" />
	<?php
	$j=0;
	$x='';
	$y='';
	if(isset($value) && is_array($value) && count($value)){
		foreach($value as  $k => $f){
			$x.= "<li id=\"__".$j."\"><img id=\"".$j."\" src=\"".Imageset('false.png')."\" onclick=\"deleteAttachment(this)\" title=\"".trans('REMOVE')."\"/> ".htmlspecialchars($k)." (".htmlspecialchars($f).')'."</li>";
			$y.="<input name=\"settings[selected_files_to_show][".htmlspecialchars($k)."]\" value=\"".htmlspecialchars($f)."\" type=\"hidden\" id=\"_".$j."\" />";
			$j++;
		}
	}
?>
	<ol id="attachs">
	<?php echo $x; ?>
	</ol>
	<div id="attachs_params">
	<?php echo $y; ?>
	</div>
	
<?php
$p=substr($_SERVER['SCRIPT_NAME'], 0, strlen($_SERVER['SCRIPT_NAME'])-strlen('index.php'));
ArtaTagsHtml::addtotmpl('<script>
  var idnum='.$j.';
  function SetUrl(url){
  	p="'.JSValue($p).'";
  	if(url.substr(0, p.length)==p){
  		url=url.substr(p.length);
  	}
  	exploded=url.split(\'/\');
  	filename=exploded[exploded.length-1];
  	at_name=at_win.prompt(\''.trans('ENTER FILE NAME').'\', filename);
  	at_name = at_name.replace(\'"\', \'&quot;\');
  	url = url.replace(\'"\', \'&quot;\');
  	$("attachs").innerHTML +="<li id=\"__"+idnum+"\"><img id=\""+idnum+"\" src=\"'.makeURL(Imageset('false.png')).'\" onclick=\"deleteAttachment(this)\" title=\"'.trans('REMOVE').'\" /> "+at_name+\' (\'+url+\')\'+"</li>";
  	
  	$("attachs_params").innerHTML +="<input name=\"settings[selected_files_to_show]["+at_name+"]\" value=\""+url+"\" type=\"hidden\" id=\"_"+idnum+"\" />";
  	idnum++;
  	new Effect.Highlight($("attachs"));
  }
  
  function deleteAttachment(att){
  	$("__"+att.id).remove();
  	$("_"+att.id).remove();
  }
  
  var at_win;
  
  function openAttachments(){
  	at_win=window.open("'.ArtaURL::getSiteURL().'index.php?pack=filemanager&editor=1&tmpl=package", "att_selector","height=650,width=550,scrollbars");
  } </script>', 'beforebodyend');
	
}


?>
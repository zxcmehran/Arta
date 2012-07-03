<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 3 $
 * @date		2009/3/18 13:26 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}

$grant=ArtaUsergroup::getPerm('can_edit_others_profilepage', 'package', 'user');

$u=$this->getCurrentUser();
$us=$this->get('user');
$s=unserialize($us->settings);

@$s->profile_page_order=(array)unserialize($s->profile_page_order);
if(@count($s->profile_page_order)!==0){
	$order=$s->profile_page_order;
}else{
	$order=array(0,1);
}

$theme=@$s->profile_theme?$s->profile_theme:'simple';
$themes=array(
	'simple'=>array(
		'bg'=>'',
		'row0'=>'#FAFAFA',
		'row1'=>'#F4F4F4',
		'section_bg'=>'',
		'msg_border'=>'',
		'msg_body'=>'',
		'msg_text'=>'',
		'texts'=>'',
		'username_color'=>'#990000'
	),
	'lime'=>array(
		'bg'=>'#F5FFEA',
		'row0'=>'#EFFFDD',
		'row1'=>'#EAFDD8',
		'section_bg'=>'#FDFFF4',
		'msg_border'=>'#98E001',
		'msg_body'=>'#EFFECF',
		'msg_text'=>'#476802',
		'texts'=>'#293D01',
		'username_color'=>'#308000'
	),
	'orange'=>array(
		'bg'=>'#FFE1B9',
		'row0'=>'#FFEDCA',
		'row1'=>'#FEDFB8',
		'section_bg'=>'#FFEFD9',
		'msg_border'=>'#FFAE17',
		'msg_body'=>'#FFDFB0',
		'msg_text'=>'#C68100',
		'texts'=>'#944A01',
		'username_color'=>'#F99500'
	),
	'red'=>array(
		'bg'=>/*'#FFC1C1'*/'#FF5252',
		'row0'=>'#FFB0B0',
		'row1'=>'#FFAAAA',
		'section_bg'=>'#FFCCCC',
		'msg_border'=>'#FF0000',
		'msg_body'=>'#FFD7D7',
		'msg_text'=>'#D01111',
		'texts'=>'#700A0A',
		'username_color'=>'#CC1111'
	),
	'blue'=>array(
		'bg'=>'#7CAEE0',
		'row0'=>'#BAD5EF',
		'row1'=>'#CAD6E9',
		'section_bg'=>'#DBE9F7',
		'msg_border'=>'#205080',
		'msg_body'=>'#CBDFF3',
		'msg_text'=>'#1D4B7A',
		'texts'=>'#14375A',
		'username_color'=>'#205080'
	)
);
if(!array_key_exists($theme, $themes)){
	$theme='simple';
}

$theme=$themes[$theme];

$style='<style>
ul#profileList {
	padding: 3px;	
}
ul#profileList fieldset legend{
	background:#eeeeee;
}
ul#profileList li table.profile_misc{
	width:100%;
}

.usertitle {
	font-size: 110%;
	padding-top:5px;
	padding-bottom:5px;
	font-weight: bold;
}

td.avatarTable_image a.changeAvatarHandle{
	position:absolute; 
	padding:5px;
	visibility: hidden;
	color:white;
	background: #333;
	opacity: 0.75;
}

td.avatarTable_image:hover a.changeAvatarHandle{
	visibility: visible;
}

td.avatarTable_image a.changeAvatarHandle:hover{
	opacity: 1.0;
}

';
if($theme['row0']){
	$style.='
ul#profileList li table tr.row0 td{
	background: '.$theme['row0'].';
}
ul#profileList li table tr.row1 td{
	background: '.$theme['row1'].';
}
';
}
if($theme['section_bg']){
	$style.='
ul#profileList li fieldset{
	background: '.$theme['section_bg'].';
}
';
}
if($theme['section_bg']){
	$style.='
ul#profileList li{
	color: '.$theme['texts'].';
}
';
}
if($theme['msg_border']){
	$style.='
ul#profileList li fieldset.profile.general span#profile_msg_content{
	background: '.$theme['msg_body'].';
	color: '.$theme['msg_text'].';
	border: 1px solid '.$theme['msg_border'].';
}
';
}
$style.='</style>';

ArtaTagsHtml::addHeader($style);


////////////////////////////// 1 /////////////////////
$ug=$this->get('ug');

if($ug!=null){
		$x='<tr><td>'.trans('USERGROUP').': '.htmlspecialchars($ug).'</td></tr>';
}else{
	$x='';
}

$sessdat=$this->get('sid');

$msg= ($sessdat!=null) ? trans('IS ONLINE') : trans('IS OFFLINE');
$is_online=($sessdat!=null) ? imageset('online.png') : imageset('offline.png');

if($u->id == $us->id){
	$changeAv = '<a href="index.php?pack=user&view=avatar" class="changeAvatarHandle">'.trans('CHANGE AVATAR').'</a>';
}else{
	$changeAv = '';
}

if(isset($s->usertitle) && (string)$s->usertitle!==''){
	$ut='<tr><td class="usertitle">'.htmlspecialchars($s->usertitle).'</td></tr>';
}else{
	$pos='';
	$ut='';
}
if($sessdat!=null && is_array(explode(',', $sessdat->position))){
	$pos='<tr><td style="padding-top:25px;">'.trans('CURRENT POSITION').':</td><td style="padding-top:25px;">'.htmlspecialchars(implode(' / ',ArtaString::split($sessdat->position, ',', false, true))).'</td></tr>';
}else{
	$pos='';
}
if((string)$us->lastvisit_date==''||(string)$us->lastvisit_date=='0'|| (string)$us->lastvisit_date=='0000-00-00 00:00:00'||(string)$us->lastvisit_date=='1970-01-01 00:00:00'){
	$lv=trans('never');
}else{
	$lv='<time datetime="'.ArtaDate::toHTML5($us->lastvisit_date).'">'.ArtaDate::_($us->lastvisit_date).'</time>';
}

$content=array();
$content[]='
<fieldset class="profile general"><legend class="profileHandler">'.trans('GENERAL').'</legend>
<table>
<tr><td width="90%" style="vertical-align:top">
<table width="100%">
<tr>
<td><b style="text-shadow:2px 2px 20px '.$theme['username_color'].';font-size:20px; color:'./*#990000*/$theme['username_color'].';">'.htmlspecialchars($us->username).'</b></td><td rowspan="3" align="'.(trans('_LANG_DIRECTION')=='ltr'?'right':'left').'">'.ArtaTagsHtml::Tooltip('<img src="'.$is_online.'"/>', htmlspecialchars($us->username).' '.$msg).'<br/><br/></td></tr>'.$ut.$x.'
</table>
<p>
<table>
<tr>
<td>'.trans('REGISTER_DATE').': </td><td><time datetime="'.ArtaDate::toHTML5($us->register_date).'">'.ArtaDate::_($us->register_date).'</time></td>
</tr>
<tr>
<td>'.trans('LASTVISIT_date').': </td><td>'.$lv.'</td>
</tr>
'.$pos.'
</table>
</td><td width="200" style="vertical-align:top">
<table class="avatarTable"><tr><td class="avatarTable_image">'.$changeAv.'<img src="index.php?pack=user&view=avatar&type=jpg&big=1&uid='.htmlspecialchars($us->id).'"/></td></tr>'.'
</table>
</td></tr>';

$grant2=ArtaUsergroup::getPerm('can_edit_others_profile_msg', 'package', 'user');

if(@$s->profile_msg!=null || $us->id==$u->id || $grant2){
	
	ArtaTagsHtml::addHeader('<style>
	p#profile_msg_container { 
		text-align:center; 
		color: #205090;  
		font-size: 1.2em;
	}
	span#profile_msg_content.tooltip{
		box-shadow: 2px 2px 10px #888;
		-moz-box-shadow: 2px 2px 10px #555;
	}
</style>');
	
	$content[0].= '<tr><td colspan="2"><h4><img src="'.imageset('comment.png').'"/> '.sprintf(trans('WHAT IS %s SAYING'), htmlspecialchars($us->username)).'</h4><p id="profile_msg_container">';
	
	if(@$s->profile_msg!=null){
		$content[0].= '<span class="tooltip" id="profile_msg_content">'.htmlspecialchars($s->profile_msg).'</span>';
	}elseif($us->id==$u->id || $grant2){
		$content[0].= '<span class="tooltip" id="profile_msg_content"></span>';
	}
	
	if($us->id==$u->id || $grant2){
		$content[0].='<img title="'.trans('EDIT PROFILE MSG').'" alt="'.trans('EDIT PROFILE MSG').'" src="'.Imageset('edit_small.png').'" onclick="pmipe.enterEditMode()" align="right" />';
		ArtaTagsHtml::addtoTmpl('<script>
		oldContent=$("profile_msg_content").innerHTML;
		pmipe=new Ajax.InPlaceEditor($("profile_msg_content"), site_url+"index.php?pack=user&view=profile&do=saveMsg&uid='.$us->id.'&token='.ArtaSession::genToken().'", {
        submitOnBlur: false, okButton: true, cancelButton: true, htmlResponse:false,
        paramName: "profile_msg",
        textBetweenControls: " ",
        cancelText: "'.JSValue(trans('CANCEL')).'",
        okText: "'.JSValue(trans('OK')).'",
        ajaxOptions: {method: "post" }, onComplete: function(s){if(s.status==200){
        	$("profile_msg_content").innerHTML=s.request.parameters.profile_msg.escapeHTML();
        	oldContent=$("profile_msg_content").innerHTML;
        }else{
        	alert("'.JSValue(trans('SERVER CONTACT ERR')).'");
        	$("profile_msg_content").innerHTML=oldContent;
        }
		}
        });
</script>', 'beforebodyend');
	}

	
	$content[0].= '</p></td></tr>';
}

$content[0].= '</table></fieldset>';

///////////////////////// 2 /////////////////////////////
$subcontent='';
$arr=array();
$db=ArtaLoader::DB();
foreach((array)unserialize($us->misc) as $k=>$v){
	if((string)$v!=''){
		$arr[]=$db->Quote($k);
	}
}
if(count($arr)){
	
	$m=$this->get('m');
	$r=ArtaUtility::keyByChild($m->getMiscRows($arr), 'var');
	$i=0;
	foreach(unserialize($us->misc) as $k=>$v){
		if(isset($r[$k]) && (string)$r[$k]->viewcode!==''){
			$subcontent.='<tr class="row'.$i.'"><td>'.(trans('MISC_'.strtoupper($k).'_LABEL')).': </td><td>';
			$value=$v;
			$name=$k;
			ob_start();
			eval($r[$k]->viewcode);
			$subcontent.=ob_get_contents();
			ob_end_clean();
			$subcontent.='</td></tr>';
		}else{
			$subcontent.='<tr class="row'.$i.'"><td>'.(trans('MISC_'.strtoupper($k).'_LABEL')).': </td><td>'.$v.'</td></tr>';
		}
		$i=$i==0?1:0;
	}
}else{
	$subcontent=trans('NO MISC PARAMETERS FOUND');
}
$content[]='
<fieldset class="profile misc"><legend class="profileHandler">'.trans('MISC INFO').'</legend>
<table class="profile_misc">
'.$subcontent.'
</table>
</fieldset>
';
//////////////////////////////////////////////////////////



if(getVar('uid', 0, 'default', 'int')==$u->id || $grant){
	$t=ArtaLoader::Template();
	$t->addtoTmpl('<script>
	
	Sortable.create("profileList",{handle:\'profileHandler\',scroll:window,constraint:false,hoverclass:\'over\',
	    onUpdate:function(sortable){
			new Ajax.Request("'.makeURL('index.php?pack=user&view=profile&do=saveOrder&uid='.getVar('uid', 0, 'default', 'int')).'&"+Sortable.serialize(sortable), 
			{parameters: {token: "'.ArtaSession::genToken().'"}, onFailure:function(t){alert("ERROR");}}
			);}
	  });
	</script>', 'beforebodyend');
	$t->addtoTmpl('<style>legend.profileHandler{cursor:move;}</style>', 'afterbody');
}
?>
<section>
<div id="profile_wrapper" style="width:100%;height:100%;<?php echo $theme['bg']?'background: '.$theme['bg']:''; ?>">
<table width="100%">
<tr>
<td>
	<ul style="list-style:none;" id="profileList">
<?php
$plugin=ArtaLoader::Plugin();
$content=array_merge($content, $plugin->trigger('onShowProfile', array($us, $u)));
@include(ARTAPATH_BASEDIR.'/tmp/profilePlugs.php');
if(isset($plugs)){
	$data=@unserialize($plugs);
	@asort($data);
	$c_data=@$plugin->map['onShowProfile'];
	@asort($c_data);
	if($data!==$c_data){
		$order=array(0,1);
	}
}
$order=(array)$order;
ArtaFile::write(ARTAPATH_BASEDIR.'/tmp/profilePlugs.php','<?php $plugs="'.ArtaFilteroutput::PHPValue(serialize(@$plugin->map['onShowProfile']), true).'";?>');
if(count($content)!==count($order)){
	foreach($content as $k=>$v){
		if(!@in_array($k, $order)){
			$order[]=$k;
		}
	}
}
foreach($order as $k=>$v){
	if(trim((string)$content[$v])!=''){
		echo '<li id="profileList_'.$v.'"><section>'.$content[$v].'</section></li>';
	}
}
?>
	</ul>
</td>
</tr>
</table>
<aside>
<?php 
echo '&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;'.
	trans('PROFILE_URL').': <div style="text-align:center;font-size:10px;">'.htmlspecialchars(ArtaURL::getURL(array('path'=>'','path_info'=>'','query'=>'')).makeURL('index.php?'.ArtaURL::getQuery())).'</div>';
?>
<?php
	if(getVar('uid', 0, 'default', 'int')==$u->id || $grant){
?>
<br /><br />
<input type="button" value="<?php
	echo trans('RESET LAYOUT');
?>" onclick="this.value='<?php echo JSValue(trans('PLEASE WAIT'));?>...';new Ajax.Request('<?php echo makeURL('index.php?pack=user&view=profile&do=saveOrder&uid='.getVar('uid', 0, 'default', 'int'))?>&profileList[]=0&profileList[]=1', {onSuccess: function(t){window.location.reload();}, onFailure:function(t){alert('ERROR');}, parameters: {token: '<?php echo ArtaSession::genToken()?>'}});"/>
<?php
	}
?>
</aside>
</div>
</section>
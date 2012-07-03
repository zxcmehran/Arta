<?php if(!defined('ARTA_VALID')){die('No access');}
$i=$this->get('post');
if($i->id>0){
	echo ArtaTagsHtml::openTranslation($i->title,$i->id, 'blogpost');
}

?>
<form method="post" name="adminform" action="<?php echo ('index.php'); ?>">
<fieldset>
<legend><?php
echo $this->get('title');
?></legend>
<table>
<tr><td>
<?php
	echo trans('POST TITLE').': '
?>
<input type="text" name="title" value="<?php echo htmlspecialchars($i->title);?>" maxlength="255"/>
</td><td>
<?php
	echo ArtaTagsHtml::Tooltip(trans('POST TITLE ALIAS'), trans('POST TITLE ALIAS DESC')).': '
?>
<input type="text" name="sef_alias" value="<?php echo htmlspecialchars($i->sef_alias);?>" maxlength="255"/>
</td><td><?php
	if(isset($i->comments)){
?><a href="index.php?pack=blog&view=comments&id=<?php echo $i->id; ?>"><?php
	echo trans('COMMENTS').' ('.(int)$i->comments.')';
?></a><?php
	}
?></td></tr></table>
<br /><br />
<?php
	$c=$i->morecontent==null ? $i->introcontent : $i->introcontent.'<hr id="readmore_handler" />'.$i->morecontent;
	echo ArtaTagsHtml::addEditor('content', $c);
	ArtaTagsHtml::addtoTmpl('<script>
function addReadmore(){ 
	separator=\'<hr id="readmore_handler" /><br>\';
	var content = tinyMCE.get(\'editor_content\').getContent();
	if (content.match(/<hr id="readmore_handler" \/>/)) {
		return false;
	} else {
		command= tinymce.isGecko ? "InsertHTML" : "mceInsertContent";
		tinyMCE.get(\'editor_content\').execCommand(command,false,separator);
		return true;
	}

	
}
var browserURL=site_url+\'index.php?pack=filemanager&editor=1&tmpl=package\';
</script>', 'beforebodyend');
?>
<br />

<input type="button" onclick="addReadmore()" value="<?php echo trans('INSERT READMORE');?>"/>
<fieldset>
<legend><?php echo trans('POST PARAMETERS');?></legend>
<table class="admintable">
<tr>
	<td class="label"><?php echo trans('ENABLED') ?></td><td class="value" colspan="2"><?php echo ArtaTagsHtml::radio('enabled', array(1=>trans('YES'), 0=>trans('NO')), $i->enabled); ?></td>
	<td class="label"><?php
	function popit($m){
		$a=array();
		foreach($m as $k=>$v){
			$a[$v->id]=$v->title;
		}
		return $a;
	}
	 $m=$this->getModel(); $cats=popit($m->getCategories()); echo trans('BLOGID') ?></td><td class="value" colspan="1" id="catContainer"><?php echo ArtaTagsHtml::select('blogid', $cats, $i->blogid); ?> <a href="index.php?pack=blog&view=newcat" onclick="window.open('index.php?pack=blog&view=newcat&tmpl=package', 'NewCat','height=600,width=800,scrollbars,resizable');return false;"><?php
	echo trans('CREATE NEW CAT');
?></a></td>
</tr>
<tr>
	<td class="label" rowspan="2"><?php echo ArtaTagsHtml::Tooltip(trans('DENIED'), trans('DENIED DESC')); ?></td><td class="value" rowspan="2"><?php
	 echo ArtaTagsHtml::PreFormItem('denied', $i->denied, 'usergroups', '$options["select_type"]=2;$options["guest"]=1;'); ?></td><td class="value" rowspan="2"><?php echo ArtaTagsHtml::select('denied_type', array(0=>trans('deny these'), 1=>trans('deny others')), (int)$i->denied_type); ?></td>
	 <td class="label"><?php echo trans('TAGS') ?></td><td class="value"><textarea id="TagsField" name="tags"><?php
	echo htmlspecialchars($i->tags);
?></textarea>
	<img src="<?php
		echo Imageset('loading_small.gif');
	?>" style="display: none;" id="TagsLoadingIndicator"/>
	<div id="TagsUpdate" class="tooltip"></div>
	</td>
</tr>
<tr>
	<td class="label"><?php echo trans('HITS') ?></td><td class="value" colspan="2"><input type="text" name="hits" value="<?php
	echo (int)$i->hits;
?>"/></td>
</tr>
<tr>
	<td class="label"><?php echo trans('ADDED_TIME') ?></td><td class="value" colspan="2"><input type="text" name="added_time" id="AddedTimeFiled" value="<?php
	echo ArtaDate::_($i->added_time, 'jscal');
?>"/><?php echo ArtaTagsHtml::Calendar('AddedTimeFiled'); ?></td>
	<td class="label"><?php echo trans('PUB_TIME') ?></td><td class="value" colspan="2"><input type="text" name="pub_time" id="PubTimeFiled" value="<?php
	echo ArtaDate::_($i->pub_time, 'jscal');
?>"/><?php echo ArtaTagsHtml::Calendar('PubTimeFiled'); ?></td>
</tr>
<tr>
	<td class="label"><?php echo trans('MOD_TIME') ?></td><td class="value"><input type="text" name="mod_time" id="ModTimeFiled" value="<?php
	if($i->mod_time && $i->mod_time!=='0000-00-00 00:00:00'){echo ArtaDate::_(time(), 'jscal');$ssssss=1;}else{
		if(getVar('id', 0, 'int')>0){
			echo ArtaDate::_(time(), 'jscal');
		}else{
			echo '';
		}
	}
?>"/><?php echo ArtaTagsHtml::Calendar('ModTimeFiled');?></td><td class="value"><?php if(isset($ssssss)){echo ' '.trans('last modified').': '.ArtaDate::_($i->mod_time, 'jscal');} ?> </td>
	<td class="label"><?php echo trans('UNPUB_TIME') ?></td><td class="value" colspan="2"><input type="text" name="unpub_time" id="UnPubTimeFiled" value="<?php
	echo $i->unpub_time && $i->unpub_time!=='0000-00-00 00:00:00' ? ArtaDate::_($i->unpub_time, 'jscal') : null;
?>"/><?php echo ArtaTagsHtml::Calendar('UnPubTimeFiled'); ?></td>
</tr>

<tr>
	
</tr>
<tr>
	<td class="label"><?php echo trans('ATTACHMENTS') ?></td><td class="value" colspan="4"><a name="attach">&nbsp;</a><input type="button" onclick="openAttachments()" value="<?php echo trans('ADD ATTACHMENT'); ?>" />
	<?php
	$j=0;
	$x='';
	$y='';
	if(isset($i->attachments) && is_array($i->attachments) && count($i->attachments)){
		foreach($i->attachments as  $atk => $at){
			$x.= "<li id=\"__".$j."\"><img id=\"".$j."\" src=\"".Imageset('false.png')."\" onclick=\"deleteAttachment(this)\" title=\"".trans('REMOVE')."\"/> ".$atk.' ('.$at.')'."</li>";
			$y.="<input name=\"att[".htmlspecialchars($atk)."]\" value=\"".$at."\" type=\"hidden\" id=\"_".$j."\" />";
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
	
	</td>
</tr>
</table>
</fieldset>
</fieldset>
<?php
	$t=ArtaLoader::Template();
	$dat='<style type="text/css">
	.selected { background-color: #888; }
</style>
<script type="text/javascript" language="javascript" charset="utf-8">
// <![CDATA[
  new Ajax.Autocompleter(\'TagsField\',\'TagsUpdate\',\''.
	addslashes(makeURL('index.php?pack=blog&view=new&type=xml')).'\', { 
    tokens: \',\', indicator: \'TagsLoadingIndicator\', fullSearch: true, partialSearch: true
  } );
  
  var idnum='.$j.';
  
  function SetUrl(url){
  	exploded=url.split(\'/\');
  	filename=exploded[exploded.length-1];
  	at_name=at_win.prompt(\''.JSValue(trans('ENTER ATTACHMENT NAME')).'\', filename);
  	at_name = at_name.replace(\'"\', \'&quot;\');
  	url = url.replace(\'"\', \'&quot;\');
  	$("attachs").innerHTML +="<li id=\"__"+idnum+"\"><img id=\""+idnum+"\" src=\"'.makeurl(Imageset('false.png')).'\" onclick=\"deleteAttachment(this)\" title=\"'.trans('REMOVE').'\" /> "+at_name+\' (\'+url+\')\'+"</li>";
  	
  	$("attachs_params").innerHTML +="<input name=\"att["+at_name+"]\" value=\""+url+"\" type=\"hidden\" id=\"_"+idnum+"\" />";
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
  }
  
  function refreshCats(){
        new Ajax.Request("index.php?pack=blog&view=new&type=xml&catlist=true&id='.$i->blogid.'", {
            onSuccess: function(r){
                var contents = r.responseText;
                $("catContainer").innerHTML = contents;
            },
            onFailure: function(r){
                alert("'.  JSValue(trans('ERROR')).'");
            }
        });
  }
  
// ]]>
</script>';
	if($t->added('beforebodyend', $dat)==false){
		$t->addtoTmpl($dat,'beforebodyend');
	}
?>
<input type="hidden" name="pack" value="blog"/>
<input type="hidden" name="view" value="posts"/>
<input type="hidden" name="task" value="save"/>
<?php
$v=getVar('ids', 0,'','array');
$v=ArtaFilterinput::clean(@array_shift($v), 'int');
	if($v>0){
		
?>
<input type="hidden" name="id" value="<?php
	echo $v;
?>"/>
<?php
	}
        $templ = ArtaLoader::Template();
        if($templ->tmpl=='package'){
		
?>
<input type="hidden" name="isPackage" value="1"/>
<?php
	}
?>
</form>

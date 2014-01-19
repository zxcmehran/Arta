<?php if(!defined('ARTA_VALID')){die('No access');}
$data=$this->get('data');
ArtaTagsHtml::addHeader('<script>
var editor;
function setLink(ln){
	while(ln.substr(0,10)=="index.php?"){
		ln=ln.substr(10);
	}
	document.adminform.link.value=ln;
	editor.close();
}
function setInner(type){
	if(type==\'inner\'){
		cont=\'index.php?<input size="30" type="text" name="link" value="'.htmlspecialchars($data->link).'" /> <input type="button" onclick="editor=window.open(\'index.php?pack=links&view=link_editor&tmpl=package\', \'le\',\'scrollbars,height=400,width=400\')" value="'.trans('LINK EDITOR').'" />\';
	}else{
		cont=\'<input size="30" type="text" name="link" value="'.htmlspecialchars($data->link).'" />\';
	}
	$(\'LinkBox\').innerHTML=cont;
}
</script>');
if($data->id!=0){
	echo ArtaTagsHtml::openTranslation($data->title, $data->id, 'link');
}

?>
<form name="adminform" method="post" action="index.php">
<fieldset>
<legend><?php
	if((int)$data->id==0){
			echo(trans('ADD LINK'));
		}else{
			echo(trans('EDIT LINK'));
		}
?></legend>
	
	<table class="admintable">
        <tr>
            <td class="label"><?php echo trans('TITLE'); ?></td>

            <td class="value" width="40%"><input type="text" name="title" value="<?php echo htmlspecialchars($data->title); ?>" maxlength="255" /></td>

            <td class="label"><?php echo ArtaTagsHtml::Tooltip(trans('LINKTYPE'),trans('LINKTYPE DESC')); ?></td>

            <td class="value"><input value="inner" type="radio" name="linktype" onclick="setInner('inner')" id="innerOption" <?php
	if($data->type=='inner' || $data->type=='default'){
		echo 'checked="checked" ';
	}
?>/><label for="innerOption"><?php	echo trans('linktype_inner');?></label> 
			<?php
	if($data->type=='outer'){
		ArtaTagsHtml::addtoTmpl('<script>setInner(\'outer\')</script>','beforebodyend');
		echo '<input value="outer" type="radio" name="linktype" onclick="setInner(\'outer\')" id="outerOption" checked="checked" />';
		
	}elseif($data->type=='default'){
		echo '<input value="outer" type="radio" name="linktype" onclick="setInner(\'outer\')" id="outerOption" disabled="true"/>';
	}else{
		echo '<input value="outer" type="radio" name="linktype" onclick="setInner(\'outer\')" id="outerOption" />';
	}
?><label for="outerOption"><?php	echo trans('linktype_outer').' '; if($data->type=='default'){echo ArtaTagsHtml::WarningTooltip(trans('SET ANOTHER AS DEFAULT TO ENABLE'));}?></label></td>
        </tr>

        <tr>
            <td class="label"><?php echo trans('LINK'); ?></td>

            <td class="value" style="direction:ltr;" id="LinkBox">
				index.php?<input size="30" maxlength="255" type="text" name="link" value="<?php echo htmlspecialchars($data->link); ?>" /> 
				<input type="button" onclick="editor=window.open('index.php?pack=links&view=link_editor&tmpl=package', 'le','scrollbars,height=400,width=500')" value="<?php	echo trans('LINK EDITOR'); ?>" />
			</td>

            <td class="label"><?php echo trans('LINKGROUP'); ?></td>

            <td class="value"><?php echo ArtaTagsHtml::select('group', $this->get('groups'),(int)$data->group);?></td>
        </tr>

        <tr>
            <td class="label"><?php echo trans('ENABLED'); ?></td>

            <td class="value"><?php echo ArtaTagsHtml::Radio('enabled', array(trans('NO'), trans('YES')),$data->enabled);?></td>
            
			<td class="label"><?php echo trans('OPEN IN NEWWIN'); ?></td>

            <td class="value"><?php echo ArtaTagsHtml::Radio('newwin', array(trans('NO'), trans('YES')),(int)$data->newwin);?></td>
        </tr>

        <tr>
            <td class="label"><?php echo (trans('DENIED')); ?></td>

            <td class="value" colspan="3">
                <table align="center">
                    <tr>
                        <td><?php echo ArtaTagsHtml::PreFormItem('denied', $data->denied, 'usergroups', '$options["select_type"]=2;$options["guest"]=1;');?></td>

                        <td><?php  echo ArtaTagsHtml::select('denied_type', array(trans('deny_these'),trans('deny_others')),$data->denied_type);?></td>
                    </tr>
                </table>
            </td>
        </tr>
        
    </table>
    </fieldset>
    <?php
	if($data->id!=false){
?>
    <input type="hidden" value="<?php echo htmlspecialchars($data->id); ?>" name="id" />
	<?php
	}
?>
    <input type="hidden" value="links" name="pack" />
    <input type="hidden" value="save" name="task" />
    </form>

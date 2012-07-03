<?php if(!defined('ARTA_VALID')){die('No access');}
$data=$this->get('data');
$up=$this->get('upackages');
$p=$this->get('packages');
$up2 = array();
foreach($up as $u){
	$up2[] = "'".JSValue($u)."'";
}

$params = ArtaURL::breakupQuery($data->params);

ArtaTagsHtml::addHeader('<script>
var forbidden=['.implode(',',$up2).'];
var editor;
function setLink(ln){
	while(ln.substr(0,10)=="index.php?"){
		ln=ln.substr(10);
	}
	pack=null;
	template=null;
	imgset=null;
	params = ln.split("&");
	for(i=0;i<params.length;i++){
		if(params[i].substr(0,5)=="pack="){
			pack=params[i].substr(5);
			delete params[i];
		}else{
			if(params[i].substr(0,9)=="template="){
				template=params[i].substr(9);
				delete params[i];
			}else if(params[i].substr(0,9)=="imageset="){
				imgset=params[i].substr(9);
				delete params[i];
			}
		}
	}
	if(pack==null){
		alert("'.trans('PLEASE SELECT A PACK').'");
		editor.close();
		return;
	}
	existence = forbidden.indexOf(pack);
	if(existence !==-1){
		$("packerr").show(); // should set the pack input but put (X) icon next to it.
	}else{
		$("packerr").hide();
	}
	$("pack").value = pack;
	if(template != null){
		$("tmpl").value = template;
	}else{
		$("tmpl").value = "-";
	}
	if(imgset != null){
		$("imgset").value = imgset;
	}else{
		$("imgset").value = "-";
	}
	$("preview").innerHTML = ln;
	other = params.join("&");
	while(other.substr(0,1)=="&"){
		other = other.substr(1);
	}
	$("other_params").value = other;
	editor.close();
}

function updatePreview(){
	pack = $F("pack");
	tmpl = $F("tmpl");
	imgset = $F("imgset");
	other = $F("other_params");
	other = other.replace(/^\s+|\s+$/g, "");
	while(other.substr(0,1)=="&"){
		other = other.substr(1);
	}
	if(pack==null){
		alert("'.trans('PLEASE SELECT A PACK').'");
		return false;
	}
	preview = "pack="+pack;
	if(other!=""){
		preview += "&"+other;
	}
	if(tmpl!="-"){
		preview += "&template="+tmpl;
	}
	if(imgset!="-"){
		preview += "&imageset="+imgset;
	}
	
	$("preview").innerHTML = preview;
	$("preview_value").value = preview;
	
}
</script>');
?>
<form name="adminform" method="post" action="index.php">
<fieldset>
<legend><?php
	if((int)$data->id==0){
			echo(trans('ADD DOMAIN'));
		}else{
			echo(trans('EDIT DOMAIN'));
		}
?></legend>
	
	<table class="admintable">
        <tr>
            <td class="label"><?php echo trans('ADDRESS'); ?></td>

            <td class="value" width="40%"><input type="text" name="address" value="<?php echo htmlspecialchars($data->address); ?>" maxlength="255" style="height: 25px; width: 300px; font-size: 15px; text-align:center;" /></td>
            
            <td class="label"><?php echo trans('MAIN DOMAIN');?></td>
			<td class="value"><?php
	$conf = ArtaLoader::Config();
	echo ArtaTagsHtml::Tooltip(null, trans('MAIN DOMAIN TIP')).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo $conf->main_domain!=''?'<b style="color:#205080;font-size:125%;">'.htmlspecialchars($conf->main_domain).'</b>':'<b style="color:red;">'.trans('NOT SET').'</b>';
?></td>
        </tr>

        <tr>
            <td class="label"><?php echo trans('PACKAGE'); ?></td>

            <td class="value">
            	<select size="5" name="package" id="pack" onchange="if(forbidden.indexOf($('pack').value)!=-1){$('packerr').show();}else{$('packerr').hide();}updatePreview();">
					<?php
					foreach($p as $lk=>$lv){
						echo '<option'.($lk == $params['pack']?' selected="selected"':'').' value="'.htmlspecialchars($lk).'"'.(in_array($lk, $up)?' style="background:#FA8383;"':'').'>'.$lv.'</option>';
					}
					?>
				</select>
				<span id="packerr" style="display:none;">
            	<?php 
					echo ArtaTagsHtml::Tooltip('<img src="'.Imageset('false.png').'" alt="&times;"/>', trans('INVALID PACK SELECTED'));
				?>
				</span>
			</td>
			
            <td class="label"><?php echo trans('PARAMETERS'); ?></td>

            <td class="value">
				<div id="preview" style="height: 40px; font-size: 125%;color:#205080; width:99%; font-family: monospace; overflow: auto; border: 1px solid gray; padding: 2px; background: #FFFFFF;"><?php echo htmlspecialchars($data->params); ?></div>
				<input id="preview_value" type="hidden" name="link" value="<?php echo htmlspecialchars($data->params); ?>" /> 
				<input type="button" onclick="editor=window.open(&quot;index.php?pack=links&view=link_editor&tmpl=package&quot;, &quot;le&quot;,&quot;scrollbars,height=400,width=500&quot;)" value="<?php	echo trans('LINK EDITOR'); ?>" />
			</td>
        </tr>

        <tr>
            <td class="label"><?php echo trans('TEMPLATE'); ?></td>

            <td class="value">
			<?php

	if((string)@$page->params['template']==''){
		$page->params['template']='-';
	}

	$db=ArtaLoader::DB();
	$sql="SELECT * FROM #__templates WHERE client='site' ORDER BY id";
	$db->setQuery($sql);
	$temps=$db->loadObjectList();
	$result=array('-'=>trans('DEFAULT TEMPLATE'));
	foreach($temps AS $v){
		$result[$v->name]=$v->title;
	}
	$x=array();
	$path=ArtaURL::getSiteURL();
	$id=ArtaString::makeRandStr(8);
	foreach($result as $key=>$tmpl){
		if($key!=='-'){
			$tip=JSValue('<img style="position:absolute;z-index:20;" src="'.$path.'templates/'.$key.'/thumb.png"/>');
			$x[$key]['onmouseover']='$("tmpl_preview_'.$id.'").innerHTML= "'.$tip.'";';
		}
	}
	$preview='<span style="vertical-align:top;" id="tmpl_preview_'.$id.'"></span>';
	$sp=array('onmouseout'=>'$("tmpl_preview_'.$id.'").innerHTML= "";', 'id'=>'tmpl', 'onchange'=>'updatePreview()');
	
	echo ArtaTagsHtml::select('page_template', $result, (string)@$params['template'], 0, $sp, $x).$preview;
	 ?>
			</td>
            
			<td class="label" rowspan="2"><?php echo trans('OTHER PARAMETERS'); ?></td>

            <td class="value" rowspan="2"><input name="other_params" id="other_params" size="35" onkeyup="updatePreview();" value="<?php 
			unset($params['pack']);
			if(isset($params['template'])){
				unset($params['template']);
			}
			if(isset($params['imageset'])){
				$imageset = $params['imageset'];
				unset($params['imageset']);
			}
			echo ArtaURL::makeupQuery($params);
			?>"/></td>
        </tr>
		
		<tr>
			<td class="label"><?php echo trans('IMAGESET'); ?></td>

            <td class="value">
			<?php

	if((string)@$page->params['imageset']==''){
		$page->params['imageset']='-';
	}

	$db=ArtaLoader::DB();
	$sql="SELECT * FROM #__imagesets WHERE client='site' ORDER BY id";
	$db->setQuery($sql);
	$temps=$db->loadObjectList();
	$result=array('-'=>trans('DEFAULT IMAGESET'));
	foreach($temps AS $v){
		$result[$v->name]=$v->title;
	}
	$x=array();
	$path=ArtaURL::getSiteURL();
	$id=ArtaString::makeRandStr(8);
	foreach($result as $key=>$tmpl){
		if($key!=='-'){
			$tip=JSValue('<img style="position:absolute;z-index:20;" src="'.$path.'imagesets/'.$key.'/imageset_thumb.png"/>');
			$x[$key]['onmouseover']='$("imgset_preview_'.$id.'").innerHTML= "'.$tip.'";';
		}
	}
	$preview='<span style="vertical-align:top;" id="imgset_preview_'.$id.'"></span>';
	$sp=array('onmouseout'=>'$("imgset_preview_'.$id.'").innerHTML= "";', 'id'=>'imgset', 'onchange'=>'updatePreview()');
	echo ArtaTagsHtml::select('page_imageset', $result, (string)@$imageset, 0, $sp, $x).$preview;
	 ?>
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
    <input type="hidden" value="domains" name="pack" />
    <input type="hidden" value="save" name="task" />
    </form>

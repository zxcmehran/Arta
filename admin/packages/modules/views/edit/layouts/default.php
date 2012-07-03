<?php if(!defined('ARTA_VALID')){die('No access');}
$data=$this->get('data');
ArtaTagsHtml::addHeader('
<script>
function setMenu(power){
	if(power){
		$(\'contentedit_container\').hide();
		$(\'menuedit_container\').show();
		document.adminform.client.value=0;
		document.adminform.client.disabled=true;
		document.adminform.ismenu.value=1;
	}else{
		$(\'menuedit_container\').hide();
		$(\'contentedit_container\').show();
		document.adminform.client.disabled=false;
		document.adminform.ismenu.value=0;
	}
}
</script>
');
if($data->id!=0 && $data->client=='site'){
	echo ArtaTagsHtml::openTranslation($data->title, $data->id, 'module');
}
?>
	<form name="adminform" method="post" action="index.php">
	<fieldset><legend><?php echo trans('MODULE DETAILS'); ?></legend>
    <table class="admintable">
        <tr>
            <td class="label"><?php echo trans('TITLE'); ?></td>

            <td class="value"><input type="text" name="title" maxlength="255" value="<?php echo htmlspecialchars($data->title); ?>" /></td>
            
            <td class="label"><?php echo trans('name'); ?></td>

            <td class="value"><?php if(getvar('id',false)!==false){
				if(isset($data->linkviewer)){
					echo $data->module; 
				}elseif((string)$data->module==''){
					echo trans('NA');
				}else{
					echo htmlspecialchars($data->module);
				}
			}else{
            	echo ArtaTagsHtml::radio('module', $this->get('mods'),0,  array(array('onclick'=>'setMenu(0)'),array('onclick'=>'setMenu(1)')));
            } ?></td>
        </tr>

        <tr>
            <td class="label"><?php echo trans('LOCATION'); ?></td>

            <td class="value"><?php $locs=array('top','left', 'right', 'bottom', 'pathway', 'banner','footer','header', 'copyright','custom1','custom2','custom3','custom4','custom5');
			$z=array();
			foreach($locs as $v){
				$z[$v]=$v;
			}
			  echo ArtaTagsHtml::select('location', $z,$data->location);?></td>
			  
     		<td class="label"><?php echo trans('ENABLED'); ?></td>

            <td class="value"><?php echo ArtaTagsHtml::Radio('enabled', array(trans('NO'), trans('YES')),$data->enabled);?></td>
        </tr>

        <tr>
            <td class="label" nowrap="nowrap"><?php echo trans('show title'); ?></td>

            <td class="value"><?php echo ArtaTagsHtml::Radio('showtitle', array(trans('NO'), trans('YES')),$data->showtitle);?></td>
    
        <?php
            if((string)$data->module==''){
            	// Only changable on Content-only modules
                ?>

            <td class="label"><?php echo trans('client'); ?></td>

            <td class="value"><?php echo ArtaTagsHtml::select('client', array(trans('site'),trans('admin')), ($data->client=='admin'));?></td>
        <?php
            }else{
            ?> 
            
            <td class="label"><?php echo trans('client'); ?></td>

            <td class="value"><?php echo trans($data->client);?></td>
			<?php	
            }
        ?>
		</tr>
		
		<tr>
            <td class="label" nowrap="nowrap"><?php echo ArtaTagsHtml::Tooltip(trans('SHOWAT'), trans('SHOWAT_TIP')); ?></td>

            <td class="value" colspan="3">
                <table align="center">
                    <tr>
                        <td><?php  echo ArtaTagsHtml::select('showat', $this->get('packs'),$data->showat, 2);?></td>

                        <td><?php  echo ArtaTagsHtml::select('showat_type', array(trans('show_on_these'),trans('unshow_on_these')),$data->showat_type);?></td>
                    </tr>
                </table>
            </td>
        </tr>
        
        <tr>
            <td class="label" nowrap="nowrap"><?php echo trans('DENIED'); ?></td>

            <td class="value" colspan="3">
                <table align="center">
                    <tr>
                        <td><?php  echo ArtaTagsHtml::preFormItem('denied', $data->denied, 'usergroups', '$options["select_type"]=2;$options["guest"]=1;');?></td>

                        <td><?php  echo ArtaTagsHtml::select('denied_type', array(trans('deny_these'),trans('deny_others')),$data->denied_type);?></td>
                    </tr>
                </table>
            </td>
        </tr>
		
        <tr id="contentedit_container"<?php //Hide if is linkviewer	
		echo isset($data->linkviewer) ? ' style="display:none;"' : ''; ?>>
            <td class="label"><?php echo trans('content'); ?></td>

            <td class="value" colspan="3"><?php echo ArtaTagsHtml::addEditor('content', $data->content);?></td>
        </tr>
        
        <tr id="menuedit_container"<?php //Hide if is NOT linkviewer
			echo isset($data->linkviewer)==false ? ' style="display:none;"' : ''; ?>>
            <td class="label"><?php echo trans('MENUGROUP'); ?></td>

            <td class="value" colspan="3"><?php	echo ArtaTagsHtml::select('menugroup', $this->get('groups'),(int)$data->content);?></td>
        </tr>
        
    </table>
    <?php
	if((int)$data->id>0){
?>
    <input type="hidden" value="<?php echo ($data->id); ?>" name="id" />
	<?php
	}
?>
	<input type="hidden" value="<?php
	echo (int)(isset($data->linkviewer));
?>" name="ismenu" />
    <input type="hidden" value="modules" name="pack" />
    <input type="hidden" value="save" name="task" />
    </fieldset>
    </form>

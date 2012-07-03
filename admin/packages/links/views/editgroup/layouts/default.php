<?php if(!defined('ARTA_VALID')){die('No access');}
$data=$this->get('data');
?>
	<form name="adminform" method="post">
	<fieldset>
	<legend><?php echo trans('LINKGROUP'); ?></legend>
    <table class="admintable">
        <tr>
            <td class="label"><?php echo trans('TITLE'); ?></td>
            <td class="value"><input type="text" name="title" value="<?php echo htmlspecialchars($data->title); ?>" maxlength="255" /></td>
        </tr>
    </table>
    </fieldset>
    <?php
	if((int)$data->id>0){
?>	<input type="hidden" value="<?php echo htmlspecialchars($data->id); ?>" name="id" /><?php
	}
?>
	<input type="hidden" value="links" name="pack" />
    <input type="hidden" value="save" name="task" />
    
    </form>

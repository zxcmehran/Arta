<?php if(!defined('ARTA_VALID')){die('No access');}
$m=$this->getModel();
ArtaTagsHtml::addHeader('<script>
var perm_editor;
function reloadPage(){perm_editor.close();location.reload();}</script>');
?>
<form method="post" action="<?php echo ArtaURL::make('index.php'); ?>" enctype="multipart/form-data" name="adminform">
<?php
	echo trans('INSTALL README');
?>
<br/><br/>
<?php echo trans('UPLOAD FILE') ?>: <input type="file" name="extension"/><br /><br />
<?php echo trans('HOSTED FILE') ?>: <input dir="ltr" type="text" size="100" name="localfile" value="<?php echo htmlspecialchars(ARTAPATH_BASEDIR) ?>"/><br /><br />
<input type="submit" value="<?php
	echo trans('INSTALL');
?>"/>
<input type="hidden" name="showdetails" value="1"/>
<input type="hidden" name="pack" value="installer"/>
<input type="hidden" name="task" value="install"/>
</form>

<?php
$partial=$m->getUploadedArchives();
if(count($partial)>0){
	echo '<hr/><b>'.trans('CURRENTLY UPLOADED ARCHIVES').':</b><table class="admintable">';
	echo '<thead><tr><th>#</th><th nowrap="nowrap">'.trans('FILE NAME').'</th><th size="20%">'.trans('FILE SIZE').'</th><th>'.trans('STATUS').'</th><th width="18"></th><th width="18"></th><th width="18"></th></tr></thead><tbody>';
	$j=1;
	foreach($partial as $a){
		echo '<tr class="row'.($j%2).'"><td align="center">'.$j.'</td><td align="center">'.htmlspecialchars($a['file']).'</td><td align="center">';
		$size = round($a['size']/1024, 3);
		$unit=' KB';
		if($size>1024){
			$size = round($a['size']/1024, 3);
			$unit=' MB';
		}
		echo $size.$unit;
		echo '</td><td align="center">';
		
		if($a['todo']==0){
			echo trans('NOT STARTED');
		}else{
			echo sprintf(trans('X OF Y EXTS ARE INSTALLED'),$a['done'],$a['todo']);
		}
		
		echo '</td><td align="center"><a href="index.php?pack=installer&task=install&localfile='.urlencode($a['relpath']).'" title="'.trans('INSTALL').'"><img src="'.Imageset('package.png').'" alt="'.trans('INSTALL').'" width="16" height="16"/></a></td>';
		echo '<td align="center"><a href="../'.$a['relpath'].'" title="'.trans('DOWNLAOD').'"><img src="'.Imageset('download.png').'" alt="'.trans('DOWNLOAD').'" width="16" height="16"/></a></td>';
		echo '<td align="center"><a href="index.php?pack=installer&task=delete&file='.urlencode($a['file']).'" title="'.trans('DELETE').'"><img src="'.Imageset('delete.png').'" alt="'.trans('DELETE').'" width="16" height="16"/></a></td></tr>';
		$j++;
	}
	
	echo '</tbody></table>';
} 
?>

<form method="post" name="adminform1" action="index.php" name="adminform">
<br/><hr/>
<?php
	echo trans('UNINSTALL README');
	
	$p_dangerous=array('login', 'installer');
	$pl_dangerous=array('admin__system__authorization', '*__system__url');
?>
<div style="display:none;" id="curid"></div>
<br/>
<br/>
<div class="tabs_container">
<ul id="extTabs" class="tabs">
	<li class="tab"><a class="active" href="#package"><?php
	echo trans('PACKAGE');
?></a></li>
<li class="tab"><a href="#module"><?php
	echo trans('MODULE');
?></a></li>
<li class="tab"><a href="#plugin"><?php
	echo trans('PLUGIN');
?></a></li>
<li class="tab"><a href="#template"><?php
	echo trans('TEMPLATE');
?></a></li>
<li class="tab"><a href="#language"><?php
	echo trans('LANGUAGE');
?></a></li>
<li class="tab"><a href="#imageset"><?php
	echo trans('IMAGESET');
?></a></li>
<li class="tab"><a href="#cron"><?php
	echo trans('CRON');
?></a></li>
<li class="tab"><a href="#webservice"><?php
	echo trans('WEBSERVICE');
?></a></li>
<li class="tab"><a href="#widget"><?php
	echo trans('WIDGET');
?></a></li>
<li class="tab"><a href="#library"><?php
	echo trans('LIBRARY');
?></a></li>
</ul>
</div>
<style>
table.admintable#untable tbody tr td {
	text-align:center;
}
</style>
<a name="hand"> </a>
<div id="package">
<table class="admintable" id="untable">
<thead>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo trans('TITLE'); ?>
		</th>
		<th>
			<?php echo trans('NAME'); ?>
		</th>
		<th width="10%">
			<?php echo trans('ENABLED'); ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo trans('ALLOWED UGS'); ?>
		</th>
		<th width="10%">
			<?php echo trans('VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<?php
	$i=0;
	$subject=$m->get('package');
	if(@count($subject)==0) 
		echo '<tr><td colspan="6">'.trans('NO RESULTS').'</td></tr>';
	else
	foreach($subject as $p){
		?>
		<tr <?php echo 'class=row'.$i;?>><td width="3%"><?php
	if($p->core==0){
?><input type="radio" name="pid" value="<?php echo htmlspecialchars($p->id) ?>" onclick="$('curid').innerHTML='<?php $x=explode('|', $p->id); echo htmlspecialchars(trans($x[2]).' - '.$x[0]);  ?>';" class="idcheck"/><?php
	}else{
		echo ' - ';
	}
?></td><td><?php echo htmlspecialchars($p->title); ?></td><td><?php echo htmlspecialchars($p->name) ?></td><td>
		<?php echo ArtaTagsHtml::BooleanControls($p->enabled, 'index.php?pack=installer&task=activate&pid='.urlencode($p->id), 'index.php?pack=installer&task=deactivate&pid='.urlencode($p->id)); if(in_array($p->name, $p_dangerous)){
			
			echo ArtaTagsHtml::WarningTooltip(trans('DISABLE WARNING'));
			
		} ?>
		</td>
		<td>
		<a href="#hand" onclick="perm_editor=window.open('index.php?pack=installer&view=perm_editor&tmpl=package&pid=<?php echo urlencode($p->id); ?>', 'perm_editor','scrollbars,resizable,location,height=200,width=500');"><?php if($p->denied=='-'){
			echo '<div style="color:red;">'.trans('P_NO').'</div>';
		}elseif((string)$p->denied==''){
			echo '<div style="color:green;">'.trans('P_ALL').'</div>';
		}else{
			echo '<div style="color: rgb(240,210,0);">'.trans('P_SOME').'</div>';
		} ?></a>
		</td>
		<td nowrap="nowrap">
			<?php echo htmlspecialchars($p->version);?>
		</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
</div>
<div id="module">
<p>
<?php
	echo trans('FOR MORE SEE MODULES');
?>
</p>
<table class="admintable" id="untable">
<thead>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo trans('TITLE'); ?>
		</th>
		<th>
			<?php echo trans('NAME'); ?>
		</th>
		<th>
			<?php echo trans('CLIENT'); ?>
		</th>
		<th width="10%">
			<?php echo trans('VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<?php
	$i=0;
	$subject=$m->get('module');
	if(@count($subject)==0) 
		echo '<tr><td colspan="4">'.trans('NO RESULTS').'</td></tr>';
	else
	foreach($subject as $p){
		?>
		<tr <?php echo 'class=row'.$i;?>><td width="3%"><?php
	if($p->core==0){
?><input type="radio" name="pid" value="<?php echo htmlspecialchars($p->id) ?>" onclick="$('curid').innerHTML='<?php $x=explode('|', $p->id); echo htmlspecialchars(trans($x[2]).' - '.$x[0]);  ?>';" class="idcheck"/><?php
	}else{
		echo ' - ';
	}
?></td><td><?php echo htmlspecialchars($p->title); ?></td><td><?php echo htmlspecialchars($p->module) ?></td><td><?php echo trans($p->client); ?></td>
		<td nowrap="nowrap">
			<?php echo htmlspecialchars($p->version);?>
		</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
</div>
<div id="plugin">
<table class="admintable" id="untable">
<thead>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo trans('TITLE'); ?>
		</th>
		<th>
			<?php echo trans('NAME'); ?>
		</th>
		<th>
			<?php echo trans('CLIENT'); ?>
		</th>
		<th width="10%">
			<?php echo trans('ENABLED'); ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo trans('ALLOWED UGS'); ?>
		</th>
		<th width="10%">
			<?php echo trans('VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<?php
	$i=0;
	$subject=$m->get('plugin');
	if(@count($subject)==0) 
		echo '<tr><td colspan="6">'.trans('NO RESULTS').'</td></tr>';
	else
	foreach($subject as $p){
		?>
		<tr <?php echo 'class=row'.$i;?>><td width="3%"><?php
	if($p->core==0){
?><input type="radio" name="pid" value="<?php echo htmlspecialchars($p->id) ?>" onclick="$('curid').innerHTML='<?php $x=explode('|', $p->id); echo htmlspecialchars(trans($x[2]).' - '.$x[0]);  ?>';" class="idcheck"/><?php
	}else{
		echo ' - ';
	}
?></td><td><?php echo htmlspecialchars($p->title); ?></td><td><?php echo htmlspecialchars($p->group.'::'.$p->plugin) ?></td><td><?php echo $p->client=='*' ? '*' : trans($p->client); ?></td><td>
		<?php echo ArtaTagsHtml::BooleanControls($p->enabled, 'index.php?pack=installer&task=activate&pid='.urlencode($p->id), 'index.php?pack=installer&task=deactivate&pid='.urlencode($p->id));if(in_array($p->client.'__'.$p->group.'__'.$p->plugin, $pl_dangerous)){
			
			echo ArtaTagsHtml::WarningTooltip(trans('DISABLE WARNING'));
			
		} ?>
		</td>
		<td>
		<a href="#hand" onclick="perm_editor=window.open('index.php?pack=installer&view=perm_editor&tmpl=package&pid=<?php echo urlencode($p->id); ?>', 'perm_editor','scrollbars,resizable,location,height=200,width=500');"><?php if($p->denied=='-'){
			echo '<div style="color:red;">'.trans('P_NO').'</div>';
		}elseif((string)$p->denied==''){
			echo '<div style="color:green;">'.trans('P_ALL').'</div>';
		}else{
			echo '<div style="color: rgb(240,210,0);">'.trans('P_SOME').'</div>';
		} ?></a>
		</td>
		<td nowrap="nowrap">
			<?php echo htmlspecialchars($p->version);?>
		</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
</div>
<div id="template">
<table class="admintable" id="untable">
<thead>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo trans('TITLE'); ?>
		</th>
		<th>
			<?php echo trans('NAME'); ?>
		</th>
		<th>
			<?php echo trans('CLIENT'); ?>
		</th>
		<th>
			<?php echo trans('screenshot'); ?>
		</th>
		<th width="10%">
			<?php echo trans('VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<?php
	$i=0;
	$subject=$m->get('template');
	if(@count($subject)==0) 
		echo '<tr><td colspan="5">'.trans('NO RESULTS').'</td></tr>';
	else
	foreach($subject as $p){
		?>
		<tr <?php echo 'class=row'.$i;?>><td width="3%"><?php
	if($p->core==0){
?><input type="radio" name="pid" value="<?php echo htmlspecialchars($p->id) ?>" onclick="$('curid').innerHTML='<?php $x=explode('|', $p->id); echo htmlspecialchars(trans($x[2]).' - '.$x[0]);  ?>';" class="idcheck"/><?php
	}else{
		echo ' - ';
	}
?></td><td><?php echo htmlspecialchars($p->title); ?></td><td><?php echo htmlspecialchars($p->name) ?></td><td><?php echo trans($p->client) ?></td><td><img src="<?php echo htmlspecialchars($p->image) ?>"/></td>
		<td nowrap="nowrap">
			<?php echo htmlspecialchars($p->version);?>
		</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
</div>
<div id="language">
<table class="admintable" id="untable">
<thead>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo trans('TITLE'); ?>
		</th>
		<th>
			<?php echo trans('NAME'); ?>
		</th>
		<th>
			<?php echo trans('CLIENT'); ?>
		</th>
		<th width="10%">
			<?php echo trans('VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<?php
	$i=0;
	$subject=$m->get('language');
	if(@count($subject)==0) 
		echo '<tr><td colspan="4">'.trans('NO RESULTS').'</td></tr>';
	else
	foreach($subject as $p){
		?>
		<tr <?php echo 'class=row'.$i;?>><td width="3%"><?php
	if($p->core==0){
?><input type="radio" name="pid" value="<?php echo htmlspecialchars($p->id) ?>" onclick="$('curid').innerHTML='<?php $x=explode('|', $p->id); echo htmlspecialchars(trans($x[2]).' - '.$x[0]);  ?>';" class="idcheck"/><?php
	}else{
		echo ' - ';
	}
?></td><td><?php echo htmlspecialchars($p->title); ?></td><td><?php echo htmlspecialchars($p->name) ?></td><td><?php echo trans($p->client) ?></td>
		<td nowrap="nowrap">
			<?php echo htmlspecialchars($p->version);?>
		</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
</div>
<div id="imageset">
<table class="admintable" id="untable">
<thead>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo trans('TITLE'); ?>
		</th>
		<th>
			<?php echo trans('NAME'); ?>
		</th>
		<th>
			<?php echo trans('CLIENT'); ?>
		</th>
		<th>
			<?php echo trans('screenshot'); ?>
		</th>
		<th width="10%">
			<?php echo trans('VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<?php
	$i=0;
	$subject=$m->get('imageset');
	if(@count($subject)==0) 
		echo '<tr><td colspan="5">'.trans('NO RESULTS').'</td></tr>';
	else
	foreach($subject as $p){
		?>
		<tr <?php echo 'class=row'.$i;?>><td width="3%"><?php
	if($p->core==0){
?><input type="radio" name="pid" value="<?php echo htmlspecialchars($p->id) ?>" onclick="$('curid').innerHTML='<?php $x=explode('|', $p->id); echo htmlspecialchars(trans($x[2]).' - '.$x[0]);  ?>';" class="idcheck"/><?php
	}else{
		echo ' - ';
	}
?></td><td><?php echo htmlspecialchars($p->title); ?></td><td><?php echo htmlspecialchars($p->name) ?></td><td><?php echo trans($p->client) ?></td><td><img src="<?php echo htmlspecialchars($p->image) ?>"/></td>
		<td nowrap="nowrap">
			<?php echo htmlspecialchars($p->version);?>
		</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
</div>
<div id="cron">
<?php echo trans('ABOUT CRONS'); ?>
<table class="admintable" id="untable">
<thead>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo trans('TITLE'); ?>
		</th>
		<th>
			<?php echo trans('NAME'); ?>
		</th>
		<th width="10%">
			<?php echo trans('ENABLED'); ?>
		</th>
		<th width="20%">
			<?php echo trans('NEXT RUN'); echo ' '.ArtaTagsHtml::WarningTooltip(trans('ITS APPROXIMATE VALUE'))?>
		</th>
		<th width="20%">
			<?php echo trans('RUN LOOP TIME');?>
		</th>
		<th width="10%">
			<?php echo trans('VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<?php
	$i=0;
	$subject=$m->get('cron');
	if(@count($subject)==0) 
		echo '<tr><td colspan="6">'.trans('NO RESULTS').'</td></tr>';
	else
	foreach($subject as $p){
		?>
		<tr <?php echo 'class=row'.$i;?>><td width="3%"><?php
	if($p->core==0){
?><input type="radio" name="pid" value="<?php echo htmlspecialchars($p->id) ?>" onclick="$('curid').innerHTML='<?php $x=explode('|', $p->id); echo htmlspecialchars(trans($x[2]).' - '.$x[0]);  ?>';" class="idcheck"/><?php
	}else{
		echo ' - ';
	}
?></td><td><?php echo htmlspecialchars($p->title); ?></td><td><?php echo htmlspecialchars($p->cron) ?></td><td>
		<?php echo ArtaTagsHtml::BooleanControls($p->enabled, 'index.php?pack=installer&task=activate&pid='.urlencode($p->id), 'index.php?pack=installer&task=deactivate&pid='.urlencode($p->id)); ?>
		</td>
		<td><a href="#hand" onclick="perm_editor=window.open('index.php?pack=installer&view=cron_editor&tmpl=package&pid=<?php echo urlencode($p->id); ?>', 'perm_editor','scrollbars,resizable,location,height=200,width=500');"><?php if($p->nextrun==9999999999){echo trans('NEVER');}else{echo ArtaDate::_($p->nextrun);} ?></a></td>
		<td><a href="#hand" onclick="perm_editor=window.open('index.php?pack=installer&view=cron_editor&tmpl=package&pid=<?php echo urlencode($p->id); ?>', 'perm_editor','scrollbars,resizable,location,height=200,width=500');">
		<?php if($p->runloop==0){echo 0;}else{echo sprintf(trans('_ HOURS'), round($p->runloop/3600, 2));
		if($p->runloop > 3600){echo ' ('.sprintf(trans('ABOUT _ DAYS'), round($p->runloop/86400, 2)).')';}} ?></a></td>
		<td nowrap="nowrap">
			<?php echo htmlspecialchars($p->version);?>
		</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
</div>
<div id="webservice">
<table class="admintable" id="untable">
<thead>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo trans('TITLE'); ?>
		</th>
		<th>
			<?php echo trans('NAME'); ?>
		</th>
		<th width="10%">
			<?php echo trans('ENABLED'); ?>
		</th>
		<th width="10%">
			<?php echo trans('VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<?php
	$i=0;
	$subject=$m->get('webservice');
	if(@count($subject)==0) 
		echo '<tr><td colspan="4">'.trans('NO RESULTS').'</td></tr>';
	else
	foreach($subject as $p){
		?>
		<tr <?php echo 'class=row'.$i;?>><td width="3%"><?php
	if($p->core==0){
?><input type="radio" name="pid" value="<?php echo htmlspecialchars($p->id) ?>" onclick="$('curid').innerHTML='<?php $x=explode('|', $p->id); echo htmlspecialchars(trans($x[2]).' - '.$x[0]);  ?>';" class="idcheck"/><?php
	}else{
		echo ' - ';
	}
?></td><td><?php echo htmlspecialchars($p->title); ?></td><td><?php echo htmlspecialchars($p->webservice) ?></td><td>
		<?php echo ArtaTagsHtml::BooleanControls($p->enabled, 'index.php?pack=installer&task=activate&pid='.urlencode($p->id), 'index.php?pack=installer&task=deactivate&pid='.urlencode($p->id)); ?>
		</td>
		<td nowrap="nowrap">
			<?php echo htmlspecialchars($p->version);?>
		</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
</div>
<div id="widget">
<table class="admintable" id="untable">
<thead>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo trans('TITLE'); ?>
		</th>
		<th>
			<?php echo trans('NAME'); ?>
		</th>
		<th width="10%">
			<?php echo trans('VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<?php
	$i=0;
	$subject=$m->get('widget');
	if(@count($subject)==0) 
		echo '<tr><td colspan="4">'.trans('NO RESULTS').'</td></tr>';
	else
	foreach($subject as $p){
		?>
		<tr <?php echo 'class=row'.$i;?>><td width="3%"><?php
	if($p->core==0){
?><input type="radio" name="pid" value="<?php echo htmlspecialchars($p->id) ?>" onclick="$('curid').innerHTML='<?php $x=explode('|', $p->id); echo htmlspecialchars(trans($x[2]).' - '.$x[0]);  ?>';" class="idcheck"/><?php
	}else{
		echo ' - ';
	}
?></td><td><?php echo htmlspecialchars($p->title); ?></td><td><?php echo htmlspecialchars($p->filename) ?></td>
		<td nowrap="nowrap">
			<?php echo htmlspecialchars($p->version);?>
		</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
</div>
<div id="library">
<table class="admintable" id="untable">
<thead>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo trans('TITLE'); ?>
		</th>
		<th>
			<?php echo trans('NAME'); ?>
		</th>
		<th width="10%">
			<?php echo trans('VERSION'); ?>
		</th>
	</tr>
</thead>
<tbody>
	<?php
	$i=0;
	$subject=$m->get('library');
	if(@count($subject)==0) 
		echo '<tr><td colspan="4">'.trans('NO RESULTS').'</td></tr>';
	else
	foreach($subject as $p){
		?>
		<tr <?php echo 'class=row'.$i;?>><td width="3%"><?php
	if($p->core==0){
?><input type="radio" name="pid" value="<?php echo htmlspecialchars($p->id) ?>" onclick="$('curid').innerHTML='<?php $x=explode('|', $p->id); echo htmlspecialchars(trans($x[2]).' - '.$x[0]);  ?>';" class="idcheck"/><?php
	}else{
		echo ' - ';
	}
?></td><td><?php echo htmlspecialchars($p->title); ?></td><td><?php echo htmlspecialchars($p->name) ?></td>
		<td nowrap="nowrap">
			<?php echo htmlspecialchars($p->version);?>
		</td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
</div>

<input type="hidden" name="pack" value="installer"/>
<input type="hidden" name="task" value="uninstall"/>
</form>
<?php
	ArtaTagsHtml::addLibraryScript('livepipe_tabs');
	$t=ArtaLoader::template();
	$t->addtoTmpl('<script>new Control.Tabs(\'extTabs\');</script>', 'beforebodyend');
?>
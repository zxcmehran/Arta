<?php if(!defined('ARTA_VALID')){die('No access');}
?>
<table><tr><td>
<?php
echo ArtaTagsHtml::SortControls(array('id'=>trans('id'),'address'=>trans('ADDRESS'),'params'=>trans('PARAMS'),'enabled'=>trans('ENABLED')), 'id', 'desc');
?></td><td style="padding-left: 50px;padding-right: 50px;">
<?php
	$conf = ArtaLoader::Config();
	echo ArtaTagsHtml::Tooltip(null, trans('MAIN DOMAIN TIP')).'&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;';
	echo trans('MAIN DOMAIN').': '.($conf->main_domain!=''?'<b style="color:#205080;font-size:125%;">'.htmlspecialchars($conf->main_domain).'</b>':'<b style="color:red;">'.trans('NOT SET').'</b>');
?></td>
</tr>
</table>
<form method="get" action="<?php echo ArtaURL::make('index.php'); ?>" name="adminform">
<table class="admintable">
<thead>
	<tr>
		<th width="16">
			<?php echo '#'; ?>
		</th>
		<th width="5%" nowrap="nowrap">
			<?php echo ArtaTagsHtml::SortLink(trans('id'), 'id'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('ADDRESS'), 'address');  ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('params'), 'params'); ?>
		</th>
		<th width="10%" nowrap="nowrap">
			<?php echo ArtaTagsHtml::SortLink(trans('ENABLED'), 'enabled');  ?>
		</th>
	</tr>
</thead>
<tfoot>
	<tr>
		<th>
			<?php echo '#'; ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('id'), 'id'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('ADDRESS'), 'address');  ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('params'), 'params'); ?>
		</th>
		<th>
			<?php echo ArtaTagsHtml::SortLink(trans('ENABLED'), 'enabled');  ?>
		</th>
	</tr>
</tfoot>
<tbody>
	<?php
	$i=0;
	$dat=$this->get('data');
	if(@count($dat)==0){
		echo '<tr><td colspan="8">'.ArtaTagsHtml::msgBox(trans('NO RESULTS')).'</td></tr>';
	}else
	foreach($dat as $k=>$p){
		?>
		<tr <?php echo 'class=row'.$i;?> style="height: 30px;">
			<td width="3%" align="center"><input type="radio" name="id" value="<?php echo htmlspecialchars($p->id) ?>" class="idcheck"/></td>
			<td width="3%" align="center" nowrap="nowrap"><?php echo htmlspecialchars($p->id) ?></td>
			<td align="center" ><?php echo '<a href="index.php?pack=domains&view=edit&id='.$p->id.'">'.htmlspecialchars($p->address).'</a>'; ?></td>
			<td align="center" ><?php
			$params = ArtaURL::breakupQuery($p->params);
			 foreach($params as $var=>$val){
			 	$params[$var] = '<b>'.$var.'</b>: '.$val;
			 } 
			 echo implode(' , ', $params);
			 ?></td>
			<td style="text-align:center;"><?php echo ArtaTagsHtml::BooleanControls($p->enabled, 'index.php?pack=domains&task=activate&id='.urlencode($p->id).'&token='.ArtaSession::genToken(), 'index.php?pack=domains&task=deactivate&id='.urlencode($p->id).'&token='.ArtaSession::genToken()); ?></td>
		</tr>
		<?php
		$i= $i==0 ? 1 : 0;
	}
?>
</tbody>
</table>
<input type="hidden" name="pack" value="domains"/>
<input type="hidden" name="task" value="display"/>
<input type="hidden" name="view" value="list"/>
</form>
<?php
echo ArtaTagsHtml::LimitControls($this->get('c'));
?>


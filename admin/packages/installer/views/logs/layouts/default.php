<?php if(!defined('ARTA_VALID')){die('No access');}
$i=$this->get('logs');

?>
<style>
table.logs td{
	text-align:center;
}
</style>
<table class="admintable logs">
	<thead>
		<tr>
			<th><?php echo trans('TITLE');?></th>
			<th><?php echo trans('NAME');?></th>
			<th><?php echo trans('TYPE');?></th>
			<th><?php echo trans('CLIENT');?></th>
			<th><?php echo trans('VERSION');?></th>
			<th><?php echo trans('TIME');?></th>
			<th><?php echo trans('ACTION');?></th>			
		</tr>
	</thead>
	<tbody>
		<?php if(count($i)==0){echo '<tr><td colspan="7">'.trans('NO RESULTS').'</td></tr>';}else{
			$j=0;
			foreach($i as $l){
				echo '<tr class="row'.($j%2).'"><td>'.htmlspecialchars($l->title).'</td><td>'.htmlspecialchars($l->name).'</td><td>'.trans($l->type).'</td><td>'.trans($l->client=='*'?'BOTH':$l->client).'</td>';
				if(strpos($l->version,'|')!==false){
					$l->version=explode('|',$l->version);
				}
				echo '<td>'.(is_array($l->version)?htmlspecialchars($l->version[0]).' => '.htmlspecialchars($l->version[1]):htmlspecialchars($l->version)).'</td><td>'.htmlspecialchars(ArtaDate::_($l->time)).'</td>';
				if($l->action=='install'){
					$l->action='<div style="color:green;font-weight:bold;">'.trans('INSTALL').'</div>';
				}elseif($l->action=='uninstall'){
					$l->action='<div style="color:red;font-weight:bold;">'.trans('UNINSTALL').'</div>';
				}else{
					$l->action='<div style="color:#086CE7;font-weight:bold;">'.trans('UPDATE').'</div>';
				}
				echo '<td>'.$l->action.'</td>';
				$j++;
			}
		} ?>
	</tbody>
</table>
<?php echo ArtaTagsHtml::LimitControls($this->get('c')); ?>
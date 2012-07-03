<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ToolsViewOptimize extends ArtaPackageView{
	
	function generateLog(){
		$this->setTitle(trans('WEBSITE TOOLS').' - '.trans('OPTIMIZATION RESULTS'));
		echo '<p>'.trans('OPTIMIZATION MSG').'</p>';
		echo '<table class="admintable">';
		echo '<thead><tr>';
		echo '<th>'.trans('TABLE_NAME').'</th>';
		echo '<th>'.trans('TABLE_ENGINE').'</th>';
		echo '<th width="10%" nowrap="nowrap">'.trans('TABLE_ROWS').'</th>';
		echo '<th>'.trans('TABLE_COLLATION').'</th>';
		echo '<th width="10%" nowrap="nowrap">'.trans('TABLE_OVERHEAD_BEFORE').'</th>';
		echo'</tr></thead>';
		echo '<tbody>';
		$i=0;
		$j=0;
		foreach($this->get('res') as $t=>$d){
			$j+=round($d->Data_free/1024, 2);
			echo '<tr class="row'.$i.'">';
			echo '<td>'.htmlspecialchars($d->Name).'</td>';
			if(strtolower($d->Engine)!=='myisam'){
				echo '<td align="center" style="color:red;">'.ArtaTagsHtml::Tooltip($d->Engine, trans('MYISAM IS RECOMMENDED')).'</td>';	
			}else{
				echo '<td align="center">'.htmlspecialchars($d->Engine).'</td>';
			}
			echo '<td align="center">'.htmlspecialchars($d->Rows).'</td>';
			echo '<td align="center">'.htmlspecialchars($d->Collation).'</td>';
			echo '<td align="center">'.($d->Data_free==0 ? '-':(round($d->Data_free/1024, 2).' KB')).'</td>';
			echo '</tr>';
			if($i==0){$i=1;}else{$i=0;}
		}
		echo '</tbody>';
		echo '<tfoot><tr><th></th><th></th><th></th><th></th><th>'.trans('TOTAL').': '.$j.' KB</th></tr></tfoot>';
		echo '</table>';
	}

}
?>
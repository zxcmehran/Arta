<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ToolsViewRepair extends ArtaPackageView{
	
	function generateLog(){
		$this->setTitle(trans('WEBSITE TOOLS').' - '.trans('REPAIR RESULTS'));
		echo '<p>'.trans('REPAIR MSG').'</p>';
		echo '<table class="admintable">';
		echo '<thead><tr>';
		echo '<th>'.trans('TABLE_NAME').'</th>';
		echo '<th>'.trans('TABLE_MSG').'</th>';
		echo'</tr></thead>';
		echo '<tbody>';
		$i=0;
		$j=0;
		foreach($this->get('res') as $t=>$d){
			echo '<tr class="row'.$i.'">';
			echo '<td align="center">'.htmlspecialchars($d->Table).'</td>';
			echo '<td align="center">';
			if($d->Msg_type=='status'){
				echo '<span>Status:</span>';
			}elseif($d->Msg_type=='error'){
				echo '<span style="color:red;">Error:</span>';
			}elseif($d->Msg_type=='info'){
				echo '<span style="color:blue;">Info:</span>';
			}elseif($d->Msg_type=='warning'){
				echo '<span style="color:orange;">Warning:</span>';
			}
			echo ' '.htmlspecialchars($d->Msg_text);
			echo '</td>';
			echo '</tr>';
			if($i==0){$i=1;}else{$i=0;}
		}
		echo '</tbody>';
		echo '</table>';
	}

}
?>
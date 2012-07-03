<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ToolsViewCache extends ArtaPackageView{
	
	function generateLog(){
		$this->setTitle(trans('WEBSITE TOOLS').' - '.trans('CACHE CLEANING RESULTS'));
		if(count($this->get('res'))){
			echo '<p>'.trans('CACHE CLEANING MSG').'</p>';
			echo '<table class="admintable">';
			echo '<thead><tr>';
			echo '<th>'.trans('CACHE_NAME').'</th>';
			echo '<th>'.trans('CLIENT').'</th>';
			echo '<th>'.trans('CACHE_SIZE').'</th>';
			echo '<th width="10%" nowrap="nowrap">'.trans('CACHE_DELETED').'</th>';
			echo '</tr></thead>';
			echo '<tbody>';
			$i=0;
			$j=0;
			foreach($this->get('res') as $t=>$d){
				$j+=round($d['size']/1024,2);
				echo '<tr class="row'.$i.'">';
				echo '<td>'.htmlspecialchars($d['name']).'</td>';
				echo '<td>'.trans($d['client']).'</td>';
				echo '<td align="center">'.(round($d['size']/1024,2).' KB').'</td>';
				echo '<td align="center">'.($d['del']==false ? '<font color="red">'.trans('NOT_DELETED').'</font>' : '<font color="green">'.trans('DELETED').'</font>').'</td>';
				echo '</tr>';
				if($i==0){$i=1;}else{$i=0;}
			}
			echo '</tbody>';
			echo '<tfoot><tr><th></th><th></th><th>'.trans('TOTAL').': '.$j.' KB</th><th></th></tr></tfoot>';
			echo '</table>';
		}else{
			echo ArtaTagsHtml::msgBox(trans('NO CACHED ITEMS FOUND'));
		}
	}

}
?>
<?php if(!defined('ARTA_VALID')){die('No access');} 
if(!isset($GLOBALS['_BUTTONS'])){
	$GLOBALS['_BUTTONS']=array();
}
$data=$GLOBALS['_BUTTONS'];
$i=$this->get('show_images');
?>
<table class="adminbuttons">
<?php 
	foreach($data as $k=>$v){
		if($i==false){
			$v=ArtaFilterinput::rstrip_tags($v, '<img><br>');
		}
		echo '<tr><td><center>'.$v.'</center></td></tr>';
	}
?>
</table>
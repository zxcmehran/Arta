<?php 
$cron=ArtaLoader::Cron();

$db=ArtaLoader::DB();

$db->setQuery('SHOW TABLE STATUS');
$tables_data=$db->loadObjectList('Name');

$this->data=$tables_data;
$tables=array();
foreach($tables_data as $tbl){
	$tables[]=$db->CQuote($tbl->Name);
}

$db->setQuery('OPTIMIZE TABLE '.implode(',',$tables));
if($db->query()){
	$cron->ReportLog('Tables optimized successfully.');
}else{
	$cron->ReportLog('There was an error in optimizing tables.');
}

?>
<?php 
$cron=ArtaLoader::Cron();

$db=ArtaLoader::DB();
$db->setQuery('DELETE FROM #__users WHERE 
			(lastvisit_date=\'1970-01-01 00:00:00\' OR 
				lastvisit_date=\'0000-00-00 00:00:00\' OR
				lastvisit_date IS NULL) AND activation!=\'0\' AND activation!=\'MODERATOR\' AND register_date < '.$db->Quote(ArtaDate::toMySQL(time()- 259200 /* Three days */)));

if($db->query()){
	$cron->ReportLog('The task cleaned inactive users who had registered past three days ago successfully.');
}else{
	$cron->ReportLog(trans('ERROR IN DB'));
}

?>
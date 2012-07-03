<?php
$path=@substr($_SERVER['SCRIPT_NAME'], 0, (strlen($_SERVER['SCRIPT_NAME'])-strlen('index.php')));
$quesmark=strpos($_SERVER['REQUEST_URI'],'?');
if($quesmark>0){
	$redirect_url=substr($_SERVER['REQUEST_URI'],0,$quesmark);
}else{
	$redirect_url=$_SERVER['REQUEST_URI'];
}
$vars=@substr($redirect_url, strlen($path));
if($vars=='test_rewriting_method'){
	echo '<body bgcolor="green" text="white">OK</body>';
}else{
	echo 'Failed';
}
?>

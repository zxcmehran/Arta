<?php
define('ARTAINSTALLER_INSIDE',true);
define( 'DS', DIRECTORY_SEPARATOR);
define('ROOTDIR', dirname( __FILE__ ) );
define('IS_WIN', (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN'));
define('LICENSE_VIEW',true);
require(ROOTDIR.'/session.php');
session_start() or die('Unable to initialize session.');

define('ARTA_VALID', true);
require(ROOTDIR.'/../library/version.php');
define('VERSION', ArtaVersion::VERSION);

if(file_exists(ROOTDIR.'/../config.php')==true && filesize(ROOTDIR.'/../config.php')>512 && @$_REQUEST['step']!=6){
	die('<b>System Error:</b> You already have installed Arta.');
}

require(ROOTDIR.'/controller.php');
require(ROOTDIR.'/view.php');
require(ROOTDIR.'/model.php');
require(ROOTDIR.'/helper.php');

$view= new InstallerView();
$helper= new InstallerHelper();
$model= new InstallerModel();
$controller= new InstallerController();

if(@$_SESSION['lang']==null && @$_REQUEST['lang']==null){
    $view->toString(0);
    echo '<form method="post"><select class="textbox" size="5" name="lang" style="width:200px; background: #f1f1f1 url(images/languages.png) no-repeat center;color:black;">';
    $langs = (array)$model->getAvailableLangs();
    foreach($langs as $id=>$name){
        echo '<option value="'.htmlspecialchars($id).'">'.htmlspecialchars($name).'</option>';
    }
    echo '</select>';
    echo '<input type="submit" class="btn" value="&gt;&gt;&gt;" style="margin:3px; float:right;"/>';
    echo '</form>';
	
    $view->toString(1);
    exit();
}elseif(@$_REQUEST['lang']!==null){
    $controller->setLang($_REQUEST['lang']);
}

$helper->loadLangFile();

$_step=@(int)$_REQUEST['step'];

if(@$_step==null || @$_step<1){
    $_step=1;
}
$view->add('dir', LANG_DIR);
$view->add('header', '<img src="images/arta.png" style="position:absolute; margin-top:-55px;margin-'.(LANG_DIR=='ltr'?'left':'right').':-100px;"/>'.ARTA_INSTALLATION);
$view->add('title', ARTA_INSTALLATION.' - '.$helper->getStepName($_step));
$view->add('steps', $helper->generateSteps());
$view->add('sidebar', $helper->generateStepDetails());
$view->add('smalltitle', $helper->getStepName($_step));
$view->add('stepinfo', $helper->getStepInfo($_step));

$view->toString(0);

$helper->getLevel($_step);

$view->toString(1);

?>

<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 $
 * @date		2009/2/20 20:37 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
$GLOBALS['_DISABLE_POSITION_LOGGING']=true;
$this->setDoctype('raw');

$id=getVar('id',null, '', 'string');

$pkg=ArtaLoader::Package();

ArtaLoader::Import('#misc->captcha');
$c = new ArtaCAPTCHA($pkg->getSetting('captcha_length', '6', 'captcha', 'site'));

$c->type=$pkg->getSetting('captcha_format', 'png', 'captcha', 'site');
$c->filters=array(
'blur'=>$pkg->getSetting('use_blur', true, 'captcha', 'site'),
'noise'=>$pkg->getSetting('use_noise', false, 'captcha', 'site'),
'signs'=>$pkg->getSetting('use_signs', true, 'captcha', 'site')
);
$c->genNew($id);
?>
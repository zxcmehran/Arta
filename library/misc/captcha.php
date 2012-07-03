<?php
/**
 * ArtaCAPTCHA Class
 * Generates CAPTCHA images to verify humans.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://www.artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2011  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
 
//Check arta
if(!defined('ARTA_VALID')){die('No access');}

/**
 * ArtaCAPTCHA Class
 * Generates CAPTCHA images to verify humans.
 */

class ArtaCaptcha{
	
	/**
	 * Length of CAPTCHA string
	 *
	 * @var	int
	 * @access	private
	 */
	private $Length;

	/**
	 * CAPTCHA string
	 *
	 * @var	string
	 * @access	private
	 */
	private $CaptchaString;

	/**
	 * Fonts path
	 *
	 * @var	string
	 * @access	private
	 */
	private $fontpath;

	/**
	 * Fonts array
	 *
	 * @var	array
	 * @access	private
	 */
	private $fonts;

	/**
	 * Image Type
	 *
	 * @var	string
	 */
	var $type='png';

	/**
	 * Errors
	 *
	 * @var	array
	 */
	var $errors=array();
	
	/**
	 * Filters Usage option
	 *
	 * @var	array
	 */
	var $filters=array('blur'=>true, 'noise'=>false, 'signs'=>true);

	/**
	 * Constructor
	 *
	 * @param	int	$length	string length
	 */
	function __construct($length=6){
	  $this->Length=$length;
	}

	/**
	 * Adds header that its an image
	 */
	function setHeader(){
		header('Content-type: image/'.$this->type);
	}

	/**
	 * Outputs image according to image type then destroys it
	 *
	 * @param	object	$res	Image Resource
	 */
	function Output($res){
		eval('image'.$this->type.'($res);');
		imagedestroy($res);
	}

	/**
	 * Generates new image then outputs it.
	 * @param	string	$id	String ID
	 */
	function genNew($id=null){
		$this->setHeader();
		$this->fontpath = ARTAPATH_MEDIA.'/fonts/';	  
		$this->fonts = $this->getFonts();
		if($this->type=='jpg'){$this->type='jpeg';}
		$this->type=strtolower($this->type);
		$this->stringGen($id);
				
		if($this->fonts == FALSE){
			$this->addError('CODE : '.$this->CaptchaString);
			$this->addError('No fonts available!');
			$this->displayError();
	  	}

		if(function_exists('imagettftext') == FALSE){
			$this->addError('CODE : '.$this->CaptchaString);
			$this->addError('CAPTCHA Font not supported!');
			$this->displayError();
		}
		
		$this->makeCaptcha();
	}
	
	/**
	 * Returns String. It's static and should be used when generation is done.
	 * @static
	 * @param	string	$id	String ID
	 * @return	string
	 */
	static function toString($id=null){
		return ($id==null ? @$_SESSION['_ARTACAPTCHA_STRING_GLOBAL'] : @$_SESSION['_ARTACAPTCHA_STRING'][$id]);
	}
	
	/**
	 * Verifies Captcha phrase. Its case-insensitive. 
	 * It's static and should be used when generation is done. 
	 * @static
	 * @param	string	$code	CAPTCHA code to verify
	 * @param	string	$id	String ID
	 * @return	bool
	 */
	static function verifyCode($code, $id=null){
		return (ArtaCaptcha::toString($id)===strtoupper((string)$code));
	}

	/**
	 * Adds an error
	 */
	function addError ($errormsg){
		$this->errors[] = $errormsg;
	}

	/**
	 * Displays error
	 */
	function displayError (){
		$iheight	 = count($this->errors) * 20 + 17;	  
	//	$iheight	 = ($iheight < 130) ? 130 : $iheight;
		$image	   = imagecreate(300, $iheight);
		$errorsign   = imagecreatefrompng(ARTAPATH_BASEDIR.'/imagesets/default/false.png');
		imagecopy($image, $errorsign, 1, 1, 1, 1, 16, 16);
		$bgcolor	 = imagecolorallocate($image, 255, 255, 255);
		$stringcolor = imagecolorallocate($image, 0, 0, 0);

		for ($i = 0; $i < count($this->errors); $i++)
		{
			$imx = ($i == 0) ? $i * 20 + 5 : $i * 20;
			$msg = 'Error[' . $i . ']: ' . $this->errors[$i];
			imagestring($image, 5, 0, $imx+17, $msg, $stringcolor);
		}
		$this->Output($image);
	}
	
	/**
	 * Checks that any errors happened
	 *
	 * @return	bool
	 */
	function isError (){	
		if (count($this->errors) == 0){
			return FALSE;
		}else{	  	
			return TRUE;
		}
	}
	
	/**
	 * Gets fonts
	 *
	 * @return	bool
	 */
	function getFonts (){
		$fonts = array();
		$f=ArtaFile::listDir($this->fontpath);
		foreach($f as $k=>$v){
			$extension = ArtaFile::getExt($v);
			if(strtolower($extension) =='ttf'){
				$fonts[]=$v;
			}
		}
			
		if (count($fonts) == 0){
			return FALSE;
		}else{
			return $fonts;
		}
	}
	
	/**
	 * Gets random font
	 *
	 * @return	string	font name
	 */
	function getRandFont(){
		$l=count($this->fonts)-1;
		return $this->fontpath . $this->fonts[mt_rand(0, $l)];
	}

	/**
	 * Generates Strings
	 * @param	string	$id	String ID
	 */
	function stringGen($id=null){
		//if(!count($this->errors)){
			$CharPool  = range('A', 'Z');
			$PoolLength = count($CharPool) - 1;
			for($i = 0; $i < $this->Length; $i++){
				$this->CaptchaString .= $CharPool[mt_rand(0, $PoolLength)];
			}
		//}else{
		//	$this->CaptchaString='ABCDEF';
		//}
		if($id==null){
			@$_SESSION['_ARTACAPTCHA_STRING_GLOBAL']=$this->CaptchaString;
		}else{
			@$_SESSION['_ARTACAPTCHA_STRING'][$id]=$this->CaptchaString;
		}
	}

	/**
	 * Makes CAPTCHA
	 */
	function makeCaptcha ()
	{
		if(count($this->errors)==0){
			$imagelength = $this->Length * 32 + 16;
			$imageheight = 75;
			$image=imagecreate($imagelength, $imageheight);
			$fc=array(
				array(122,20,100),
				array(243,157,21),
				array(193,54,146),
				array(54,193,90),
				array(193,54,55),
				array(119,207,191),
				array(70,102,63),
				array(34,85,187),
				array(193,54,186),
				array(50,50,50),
				array(127,113,215),
				array(204,210,49)
			);
			$bc=array(
				array(172,199,255),
				array(186,255,181),
				array(254,255,181),
				array(255,219,181),
				array(211,251,255),
				array(255,181,181),
				array(210,230,250),
			);
			$r=$bc[mt_rand(0,(count($bc)-1))];
			$r=$this->lighten($r,mt_rand(30,55));
/*			foreach($fc as $v){
			echo '<div style="font-size:100px;color:rgb('.$v[0].', '.$v[1].', '.$v[2].');">'.$v[0].','.$v[1].','.$v[2].'</div>';
			}
			foreach($bc as $v){
			echo '<div style="font-size:100px;background-color:rgb('.$v[0].', '.$v[1].', '.$v[2].');">'.$v[0].','.$v[1].','.$v[2].'</div>';
			}
			header('Content-Type: text/html');
			die();*/
			$bgcolor	 = imagecolorallocate($image, $r[0], $r[1], $r[2]);
			if($this->filters['signs']){
				$this->signs($image, $this->getRandFont(), 8, $r);
			}
			for ($i = 0; $i < strlen($this->CaptchaString); $i++){
				$r=$fc[mt_rand(0,(count($fc)-1))];
				$r=$this->change($r,60);

				$r=$this->darken($r, mt_rand(50,80));

				$stringcolor = imagecolorallocate($image, $r[0], $r[1], $r[2]);
				imagettftext($image, 30, mt_rand(-20, 20), $i * 32 + 10,
							mt_rand(30, 60),
							 $stringcolor,
							 $this->getRandFont(),
							 $this->CaptchaString{$i});
			}
			if($this->filters['noise']){
				$this->noise($image, mt_rand(0,20));
			}
			if($this->filters['blur']){
				$this->blur($image, (mt_rand(1,7)*(.1)));
			}
			
			$this->Output($image);
		}
	}

	function getCaptchaString (){
	  return $this->CaptchaString;
	}

##############################################
#	FILTERS
	
	/**
	 * Adds noise
	 *
	 * @param	object	$image	 image resource
	 * @param	int	$runs	noise amount
	 */
	function noise (&$image, $runs = 30){
		$w = imagesx($image);
		$h = imagesy($image);
		for ($n = 0; $n < $runs; $n++){
			for ($i = 1; $i <= $h; $i++){
				$randcolor = imagecolorallocate($image,
										  mt_rand(0, 255),
										  mt_rand(0, 255),
										  mt_rand(0, 255));
				imagesetpixel($image,
						mt_rand(1, $w),
						mt_rand(1, $h),
						$randcolor);
			}
		}  
	}
	
	/**
	 * Adds character signs
	 *
	 * @param	object	$image	 image resource
	 * @param	string	$font	font name
	 * @param	int	$cells	cell count
	 * @param	string	$c	bg color
	 */
	function signs (&$image, $font, $cells = 3, $c){

		$w = imagesx($image);
		$h = imagesy($image);

		for ($i = 0; $i < $cells; $i++){
			$centerX	 = mt_rand(1, $w);
			$centerY	 = mt_rand(1, $h);
			$amount	  = mt_rand(1, 15);
			$stringcolor = imagecolorallocate($image, $c[0]-75, $c[1]-75, $c[2]-75);
			for ($n = 0; $n < $amount; $n++){
				$signs = range('A', 'Z');
				$sign = $signs[mt_rand(0, count($signs) - 1)];
				imagettftext($image, 25, 
						   mt_rand(-15, 15), 
						   $centerX + mt_rand(-50, 50),
						   $centerY + mt_rand(-50, 50),
						   $stringcolor, $font, $sign);
			}
		}
	}

	/**
	 * Rnadomly changes color values 
	 *
	 * @param	array	$arr	 color array
	 * @param	int	$am	amount
	 * @return	array
	 */
	function change($arr, $am=50){

		if($arr[0] < $am){
			$a[0]=$arr[0];
		}else{
			$a[0]=$am;
		}
		if($arr[0]> (255-$am)){
			$arr[0]=255;
			$a[0]=0;
		}

		if($arr[1] < $am){
			$a[1]=$arr[1];
		}else{
			$a[1]=$am;
		}
		if($arr[1]> (255-$am)){
			$arr[1]=255;
			$a[1]=0;
		}

		if($arr[2] < $am){
			$a[2]=$arr[2];
		}else{
			$a[2]=$am;
		}
		if($arr[2]> (255-$am)){
			$arr[2]=255;
			$a[2]=0;
		}

		return array($arr[0]+mt_rand(	($a[0]*(-1)), $a[0]	),
			$arr[1]+mt_rand(	($a[1]*(-1)), $a[1]	),
			$arr[2]+mt_rand(	($a[2]*(-1)), $a[2]	)
			);
	}

	/**
	 * Darkens color values 
	 *
	 * @param	array	$r	 color array
	 * @param	int	$a	amount
	 * @return	array
	 */
	function darken($r, $a){
		//$a is amount
		foreach($r as $k=>$v){
			if($v> $a){
				$r[$k]=$v-$a;
			}else{
				$r[$k]=0;
			}
		}
		return $r;
	}

	/**
	 * Lightens color values 
	 *
	 * @param	array	$r	 color array
	 * @param	int	$a	amount
	 * @return	array
	 */
	function lighten($r, $a){
		//$a is amount
		foreach($r as $k=>$v){
			if($v+$a <= 255){
				$r[$k]=$v+$a;
			}else{
				$r[$k]=255;
			}
		}
		return $r;
	}
	
	/**
	 * Adds blur
	 *
	 * @param	object	$image	 image resource
	 * @param	int	$radius	amount of blur
	 */
	function blur (&$image, $radius = 3)
	{
		$radius  = round(max(0, min($radius, 50)) * 2);
		$w= imagesx($image);
		$h= imagesy($image);
		$imgBlur = imagecreate($w, $h);
		for ($i = 0; $i < $radius; $i++){
			imagecopy	 ($imgBlur, $image,   0, 0, 1, 1, $w - 1, $h - 1);
			imagecopymerge($imgBlur, $image,   1, 1, 0, 0, $w,	 $h,	 50.0000);
			imagecopymerge($imgBlur, $image,   0, 1, 1, 0, $w - 1, $h,	 33.3333);
			imagecopymerge($imgBlur, $image,   1, 0, 0, 1, $w,	 $h - 1, 25.0000);
			imagecopymerge($imgBlur, $image,   0, 0, 1, 0, $w - 1, $h,	 33.3333);
			imagecopymerge($imgBlur, $image,   1, 0, 0, 0, $w,	 $h,	 25.0000);
			imagecopymerge($imgBlur, $image,   0, 0, 0, 1, $w,	 $h - 1, 20.0000);
			imagecopymerge($imgBlur, $image,   0, 1, 0, 0, $w,	 $h,	 16.6667);
			imagecopymerge($imgBlur, $image,   0, 0, 0, 0, $w,	 $h,	 50.0000);
			imagecopy	 ($image  , $imgBlur, 0, 0, 0, 0, $w,	 $h);
		}
		imagedestroy($imgBlur);
	}
}


?>
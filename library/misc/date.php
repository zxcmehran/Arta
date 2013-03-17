<?php 
/**
 * Date methods to use in Arta
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 1 2011/08/02 14:20 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */

//Check arta
if(!defined('ARTA_VALID')){die('No access');}

if(version_compare(PHP_VERSION, '5.1.0', '<') && !function_exists('date_default_timezone_set')){
	ArtaError::AddAdminAlert('Date Class', 'Setting Timezone', 'Failed to set Timezone so some times and dates will be invalid. Upgrade your PHP to >= 5.1.0 version to solve this problem.', false);
}else{
	date_default_timezone_set('GMT');
}
/**
 * ArtaDate class
 * Date and time generation and conversion. Supports Gregorian and Jalali dates.
 * This class aims on storing dates in gregorian and time in system time zone (GMT if
 * possible, see above lines) and show dates and times in specific time zone and
 * calendar system according to every user's settings.
 * So, you should store timestamp in system time zone using date(), NOT gmdate()!
 * 
 * see {@see    ArtaDate::gmtime()} for more information.
 * 
 * However, by using date_default_timezone_set('GMT') will try to set system 
 * timezone to GMT and then gmdate() and date() will return same results.
 * 
 * @static
 */
class ArtaDate {
	
	/**
	 * Gets offset from Configuration
	 * 
	 * @static
	 * @return	float
	 */
	static function getOffset(){
		$config = ArtaLoader::Config();
		return ((float)$config->time_offset * 3600);
	}

	/**
	 * Gets Time Format from Configuration
	 * 
	 * @static
	 * @return	string
	 */
	static function getFormat(){
		$config = ArtaLoader::Config();
		return $config->time_format;
	}

	/**
	 * Converts datetime to MySQL format
	 * ONLY accepts Gregorian Calendar Type Dates
	 * 
	 * @static
	 * @param	string	$str	date/time/timestamp
	 * @return	string
	 */
	static function toMySQL($str){
		$epoch=strtotime($str);
		if($epoch<1){
			$epoch=$str;
		}
		return date('Y-m-d H:i:s', $epoch);
	}
	
	/**
	 * Converts datetime to HTML5 &lt;time&gt; format
	 * ONLY accepts Gregorian Calendar Type Dates
	 * 
	 * @static
	 * @param	string	$str	date/time/timestamp
	 * @return	string
	 */
	static function toHTML5($str){
		$epoch=strtotime($str);
		if($epoch<1){
			$epoch=$str;
		}
		return gmdate('Y-m-d\TH:i\Z', $epoch);
	}

	/**
	 * Converts datetime to Default format
	 * ONLY accepts Gregorian Calendar Type Dates
	 * 
	 * @static
	 * @param	string	$str	date/time/timestamp
	 * @return	string
	 */
	static function toFormat($str){
		$epoch=strtotime($str);
		if($epoch<1){
			$epoch=$str;
		}
		return date(ArtaDate::getFormat(), $epoch);
	}

	/**
	 * Converts datetime to custom formats
	 * ONLY accepts Gregorian Calendar Type Dates
	 * 
	 * @static
	 * @param	string	$str	date/time/timestamp
	 * @param	string	$to	datetime format
	 * @return	string
	 */
	static function Translate($str, $to){
		$epoch=strtotime($str);
		if($epoch<1){
			$epoch=$str;
		}
		return date($to, $epoch);
	}
	
	/**
	 * Translates week or month names according to language files.
	 * 
	 * @static
	 * @param	string	$t	Datetime String to find names on it	
	 * @return	string
	 */
	 static function useLanguage($t){
	 	$t=strtolower($t);
	 	$c=ArtaLoader::Config();
	 	if($c->cal_type=='jalali'){
	 		$month_0=array('farvardin', 'ordibehest', 'khordad', 'tir', 'mordad', 'shahrivar', 'mehr', 'aban','azar', 'dey', 'bahman', 'esfand');
	 		$month_1=array('far', 'ord', 'kho', 'tir', 'mor', 'sha', 'meh', 'aba', 'aza', 'dey', 'bah','esf');
	 	}else{
		 	$month_0=array('january', 'february', 'march', 'april', 'may', 'june', 'july', 'august','september', 'october', 'november', 'december');
		 	$month_1=array('jan', 'feb', 'mar', 'apr', 'may', 'jun', 'jul', 'aug', 'sep', 'oct', 'nov','dec');
	 	}
	 	$w_0=array('sunday', 'monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday');
	 	$w_1=array('sun','mon','tue','wed','thu','fri','sat');
	 	$m=substr($c->cal_type,0,1);
	 	$a=array();
		foreach($month_0 as $v){
			if(is_int(strpos($t, $v))){
				$r=ArtaString::makeRandStr(6);
				$t=str_replace($v, '['.$r.']', $t);
				$a[$r]=trans($m.'_month_0_'.strtoupper($v));
			}
		}
		foreach($month_1 as $v){
			if(is_int(strpos($t, $v))){
				$r=ArtaString::makeRandStr(6);
				while(array_key_exists($r,$a)){
					$r=ArtaString::makeRandStr(6);
				}
				$t=str_replace($v, '['.$r.']', $t);
				$a[$r]=trans($m.'_month_1_'.strtoupper($v));
			}
		}
		foreach($w_0 as $v){
			if(is_int(strpos($t, $v))){
				$r=ArtaString::makeRandStr(6);
				while(array_key_exists($r,$a)){
					$r=ArtaString::makeRandStr(6);
				}
				$t=str_replace($v, '['.$r.']', $t);
				$a[$r]=trans('weekday_0_'.strtoupper($v));
			}
		}
		foreach($w_1 as $v){
			if(is_int(strpos($t, $v))){
				$r=ArtaString::makeRandStr(6);
				while(array_key_exists($r,$a)){
					$r=ArtaString::makeRandStr(6);
				}
				$t=str_replace($v, '['.$r.']', $t);
				$a[$r]=trans('weekday_1_'.strtoupper($v));
			}
		}
		foreach($a as $k=>$v){
			$t=str_replace('['.$k.']', $v, $t);
		}
		
		return $t;
	 }
	 
	 /**
	  * Jalali Date Function
	  * 
	  * @static
	  * @param	string	$format	DateTime Format
	  * @param	int	$timestamp	Unix Timestamp or Datetime formatted text
	  * @param	bool	$from_gmdate	in case of passing Formatted datetime to $timestamp - is this string generated from gmdate() ? 
	  * @return	string
	  */
	 static function JalaliFormat($format, $timestamp, $from_gmdate=false){
	 	$res='';
	 	$d=array();
	 	if(!is_numeric($timestamp)){
	 		$timestamp=strtotime($timestamp);
	 		if($from_gmdate){
				$diff=self::getDifference();
				if($timestamp>0 && $diff!==0){
					$timestamp+=$diff*3600;
				}
			}
	 	}

	 	$d[0]=gmdate('Y',$timestamp);
	 	$d[1]=gmdate('m',$timestamp);
	 	$d[2]=gmdate('d',$timestamp);
	 	$d=self::gregorian_to_jalali($d[0],$d[1],$d[2]);
	 	$i=0;
	 	while($i<strlen($format)){
	 		$x=$format{$i};
	 		if($x=='\\'){
	 			$x=substr($format, $i, 2);
	 		}
	 		switch($x){
	 			case 'd':
	 				$r=$d[2];
	 				if(strlen((string)$r)==1){
	 					$r='0'.$r;
	 				}
 				break;
	 			case 'j':
	 				$r=$d[2];
 				break;
 				case 'N':
 					$r=gmdate('N',$timestamp);
 					$r +=2;
 					if($r>7){
 						$r -=7;
 					}
				break;
 				case 'F':
		 			$month=array('farvardin', 'ordibehest', 'khordad', 'tir', 'mordad', 'shahrivar', 'mehr', 'aban','azar', 'dey', 'bahman', 'esfand');
 					$r =$month[$d[1]-1];
				break;
				case 'm':
	 				$r=$d[1];
	 				if(strlen((string)$r)==1){
	 					$r='0'.$r;
	 				}
 				break;
			 	case 'M':
	 				$month=array('far', 'ord', 'kho', 'tir', 'mor', 'sha', 'meh', 'aba', 'aza', 'dey', 'bah','esf');
 					$r =$month[$d[1]-1];
				break;
				case 'n':
	 				$r=$d[1];
 				break;
				case 't':
					$days=array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
	 				$r=$daya[$d[1]-1];
 				break;
 				case 'y':
 					$r =substr((string)$d[0],2);
				break;
				case 'Y':
 					$r =$d[0];
				break;
				default:
					$r=gmdate($x,$timestamp);
					
					if(strlen($r)==0){
						$r=$x;
					}
				break;
	 		}
	 		
	 		$res .=$r;
	 		if(strlen($x)>1){$i++;}
	 		$i++;
	 	}
	 	return $res;
	 	
	 }

	/**
	 * Nothing extra. Just return integer value of divide
	 * 
	 * @static
	 * @param	mixed	$a
	 * @param	mixed	$b
	 * @return	int
	 */
 	static function div($a,$b) {
	    return (int) ($a / $b);
	}

	/**
	 * Converts Gregorian date to Jalali date
	 * 
	 * @static
	 * @param	int	$g_y	Gregorian year
	 * @param	int	$g_m	Gregorian month
	 * @param	int	$g_d	Gregorian date
	 * @return	array
	 */
	static function gregorian_to_jalali ($g_y, $g_m, $g_d) 
	{
	    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); 
	    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);     
	    


	   

	   $gy = $g_y-1600; 
	   $gm = $g_m-1; 
	   $gd = $g_d-1; 

	   $g_day_no = 365*$gy+self::div($gy+3,4)-self::div($gy+99,100)+self::div($gy+399,400); 

	   for ($i=0; $i < $gm; ++$i) 
	      $g_day_no += $g_days_in_month[$i]; 
	   if ($gm>1 && (($gy%4==0 && $gy%100!=0) || ($gy%400==0))) 
	      /* leap and after Feb */ 
	      $g_day_no++; 
	   $g_day_no += $gd; 

	   $j_day_no = $g_day_no-79; 

	   $j_np = self::div($j_day_no, 12053); /* 12053 = 365*33 + 32/4 */ 
	   $j_day_no = $j_day_no % 12053; 

	   $jy = 979+33*$j_np+4*self::div($j_day_no,1461); /* 1461 = 365*4 + 4/4 */ 

	   $j_day_no %= 1461; 

	   if ($j_day_no >= 366) { 
	      $jy += self::div($j_day_no-1, 365); 
	      $j_day_no = ($j_day_no-1)%365; 
	   } 

	   for ($i = 0; $i < 11 && $j_day_no >= $j_days_in_month[$i]; ++$i) 
	      $j_day_no -= $j_days_in_month[$i]; 
	   $jm = $i+1; 
	   $jd = $j_day_no+1; 

	   return array($jy, $jm, $jd); 
	} 

	/**
	 * Converts Jalali date to Gregorian date
	 * 
	 * @static
	 * @param	int	$j_y	Jalali year
	 * @param	int	$j_m	Jalali month
	 * @param	int	$j_d	Jalali date
	 * @return	array
	 */
	static function jalali_to_gregorian($j_y, $j_m, $j_d) 
	{ 
	    $g_days_in_month = array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); 
	    $j_days_in_month = array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
	    
	   

	   $jy = $j_y-979; 
	   $jm = $j_m-1; 
	   $jd = $j_d-1; 

	   $j_day_no = 365*$jy + self::div($jy, 33)*8 + self::div($jy%33+3, 4); 
	   for ($i=0; $i < $jm; ++$i) 
	      $j_day_no += $j_days_in_month[$i]; 

	   $j_day_no += $jd; 

	   $g_day_no = $j_day_no+79; 

	   $gy = 1600 + 400*self::div($g_day_no, 146097); /* 146097 = 365*400 + 400/4 - 400/100 + 400/400 */ 
	   $g_day_no = $g_day_no % 146097; 

	   $leap = true; 
	   if ($g_day_no >= 36525) /* 36525 = 365*100 + 100/4 */ 
	   { 
	      $g_day_no--; 
	      $gy += 100*self::div($g_day_no,  36524); /* 36524 = 365*100 + 100/4 - 100/100 */ 
	      $g_day_no = $g_day_no % 36524; 

	      if ($g_day_no >= 365) 
	         $g_day_no++; 
	      else 
	         $leap = false; 
	   } 

	   $gy += 4*self::div($g_day_no, 1461); /* 1461 = 365*4 + 4/4 */ 
	   $g_day_no %= 1461; 

	   if ($g_day_no >= 366) { 
	      $leap = false; 

	      $g_day_no--; 
	      $gy += self::div($g_day_no, 365); 
	      $g_day_no = $g_day_no % 365; 
	   } 

	   for ($i = 0; $g_day_no >= $g_days_in_month[$i] + ($i == 1 && $leap); $i++) 
	      $g_day_no -= $g_days_in_month[$i] + ($i == 1 && $leap); 
	   $gm = $i+1; 
	   $gd = $g_day_no+1; 

	   return array($gy, $gm, $gd); 
	}
	
	
	/**
	 * Returns Calendar Type
	 * 
	 * @static
	 * @return	string
	 */
	static function getCalendarType(){
		$config=ArtaLoader::Config();
		return $config->cal_type;
	}
	
	/**
	 * Converts inputs to gregorian(if needed)
	 * So remember that inputs must be always in MySQL format (Y-m-d H:i:s)
	 * 
	 * @static
	 * @param	string	$str	DateTime String
	 * @param	bool	$doOffset	Remove offset to make GMT time?
	 * @param	bool	$onlydate	Ignore Time and return Date only(Y-m-d) ? 
	 * @return	string	Datetime in MySQL format
	 */
	static function convertInput($str, $doOffset=true, $onlydate=false){
		$str=trim($str);
		@$s=explode(' ',$str);
		if(is_array($s) && count($s)==2){
			@$d=explode('-',$s[0]);
			@$t=explode(':',$s[1]);
			if(@count($t)<3){
				$t=array('00','00','00');
			}
		}else{
			@$d=explode('-', $str);
			$t=array('00','00','00');
		}
		if(@count($d)<3){
			return false;
		}
		// detect jalali
		if(@$d[0]<1700){
			$d=self::jalali_to_gregorian($d[0],$d[1],$d[2]);
		}
		$r=$d[0].'-'.$d[1].'-'.$d[2].' '.$t[0].':'.$t[1].':'.$t[2];
		
		$stamp = strtotime($r);
		
		if($doOffset){
			$c=ArtaLoader::Config();
			$stamp-=($c->time_offset*3600);
		}
		
		
		$diff=self::getDifference();
		if($stamp>0 && $diff!==0){
			$stamp+=$diff*3600;
		}
		
		
		// gmdate inactives those three lines above
		if($onlydate==true){
			$r=date('Y-m-d', $stamp);
		}else{
			$r=date('Y-m-d H:i:s', $stamp);
		}
		return $r;
	}
	
	/**
	 * Function Redirection: {@link	ArtaDate::convertInput()}
	 * @static
	 */
	static function __($str, $doOffset=true, $onlydate=false){
		return self::convertInput($str,$doOffset, $onlydate);
	}
	
	/**
	 * Converts Date to output. If conversion to Jalali is needed then Date Format will be MySQL Format.
	 * @static
	 * @param	string	$stamp	Timestamp or Formatted datetime (must be supported by <b>strtotime()</b>)
	 * @param	string	$format	Custom format. pass "jscal" to pass ArtaCalendarJS Friendly dates
	 * @param	bool	$doOffset	Add offset to output local time?
	 * @param	bool	$from_gmdate	is this stamp generated from gmdate() or ArtaDate::gmtime()? It means you are passing GMT time or GMT+Offset? 
	 * @return	string	Format will be converted to MySQL on Jalali Calendar Type.
	 */
	static function convertOutput($stamp, $format=null, $doOffset=true, $from_gmdate=false){
		if(!is_numeric($stamp)){
			// on gmdate() generated datetime from current time() we will have 
			// $stamp=time()-[GMT Difference]
			$stamp=@strtotime($stamp);
		}
		// so make $stamp = time()-[GMT Difference] + [GMT Difference] = time()
		// Because using gmdate() again will result $stamp = time()-([GMT Difference]*2)
		// for example if you have current time 5:00 and GMT Difference +2, so your gmdate() will be 3:00 and after converting it to timestamp in previous lines it will be time() - 7200 so we add 2*3600 (Diffrence * 3600 secs) to stamp to get current time()
        // as PHP 5.1.0 timezone will be set to GMT and we will have [GMT Difference]=0
        // this method is just for make correct times in PHP < 5.1.0
		if($from_gmdate){
			$diff=self::getDifference();
			if($stamp>0 && $diff!==0){
				$stamp+=$diff*3600;
			}
		}
		$config=ArtaLoader::Config();
		if($doOffset){
			$stamp += ($config->time_offset*3600);
		}
		if($format==null){
			$f=$config->time_format;
		}else{
			$f=$format;
		}
		if($format=='jscal'){
			$f='Y-m-d H:i:s';
		}
		if($config->cal_type=='jalali'){
			$str=self::JalaliFormat($f, $stamp);
		}else{
			$str=gmdate($f, $stamp);			
		}
		$str=self::useLanguage($str);
		return $str;
	}
	
    /**
     * Returns GMT time difference like "+3.5"
     * @static
     * @return  float
     */
	static function getDifference(){
		$offset= date('O', time());
		if(@$offset{0}=='-'){
			$x='-';
			$offset = substr($offset,1);
		}elseif(@$offset{0}=='+'){
			$x='+';
			$offset = substr($offset,1);
		}else{
			$x='+';
		}
		return (float)($x.(((substr($offset,0,2)*60)+substr($offset,2))/60));
	}
    
    /**
     * Returns time() but in GMT.
     * Note thet time() will return GMT time in arta context on PHP > 5.1.0.
     * It's recommended to use it if you want to create GMT times for compatibility on 
     * PHP < 5.1.0.
     * 
     * NOTE: ALWAYS store times in non GMT( default PHP time() function ) on DBs, Files,...
     *       if you stored them in GMT, don't forget to set last parameter to TRUE 
     *       when you use ArtaDate::_() or ArtaDate::convertOutput() .
     * @static
     * @return  float
     */
    static function gmtime(){
        return time()-(self::getDifference()*3600);
    }
	
	/**
	 * Function Redirection: {@link	ArtaDate::convertOutput()}
	 * @static
	 */
	static function _($str, $format=null, $doOffset=true, $from_gmdate=false){
		return self::convertOutput($str, $format, $doOffset, $from_gmdate);
	}
}

?>
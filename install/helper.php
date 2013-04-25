<?php
defined('ARTAINSTALLER_INSIDE') or die();

class InstallerHelper{

    function loadLangFile(){
        require ROOTDIR.'/languages/'.$_SESSION['lang'].'.php';
    }
    
    function isPrivsEnough($p){
        $p = strtoupper(trim($p));
        if($p=='ALL' || $p=='ALL PRIVILEGES'){
            return true;
        }
        if($p=='USAGE'){
            return false;
        }
        $p = (array)explode(',',$p);
        $p = array_map('trim', $p);
        $privs = array('SELECT'=>0,'INSERT'=>0,'UPDATE'=>0,'DELETE'=>0,'CREATE'=>0,'ALTER'=>0, 'INDEX'=>0, 'DROP'=>0);
        foreach($p as $priv){
            if(isset($privs[$priv])){
                $privs[$priv]=1;
            }
        }
        return in_array(0,$privs)==false;
        
    }    
    
    function generateSteps(){
        global $_step;
        $tmpl='<span style="padding-top:3px;margin:5px;margin-top:-15px;width:35px;height:32px;background:url(\'images/step.png\') no-repeat;color:white;display:inline-block;text-align:center;vertical-align:middle;font-size: 20px; ">?</span>';
        $i=1;
        $r=array();
        while($i<=$this->getStepsCount()){
            if($_step==$i){
                $r[]=str_replace(array('?', '; "'),array($i, ';background:url(\'images/step_on.png\') no-repeat;"'),$tmpl);
            }else{
                $r[]=str_replace('?',$i,$tmpl);
            }
            $i++;
        }
        return implode('<img src="images/arrow_'.LANG_DIR.'.png" style="vertical-align:middle;display:inline-block;margin-top:-20px;">',$r);
    }
    
    function generateStepDetails(){
        $r='<ul>';
        $r.='<li'.$this->addStepDetailsEffect(1).'>'.STEP_WELCOME.'</li>';
        $r.='<li'.$this->addStepDetailsEffect(2).'>'.STEP_ESSENTIALS.'</li>';
        if(LICENSE_VIEW==true){
            $r.='<li'.$this->addStepDetailsEffect(3).'>'.STEP_LICENSE.'</li>';
        }
        $r.='<li'.$this->addStepDetailsEffect(4).'>'.STEP_DB.'</li>';
        $r.='<li'.$this->addStepDetailsEffect(5).'>'.STEP_INFO.'</li>';
        $r.='<li'.$this->addStepDetailsEffect(6).'>'.STEP_FINISH.'</li>';
        $r.='</ul>';
        return $r;
    }
    
    function addStepDetailsEffect($i){
        global $_step;
        if(LICENSE_VIEW==false && $i>3) $i--;
        if($_step==$i){
            return ' style="font-weight: bold;"';
        }else{
            return;
        }
    }
    
    function getStepsCount(){
        return LICENSE_VIEW ? 6 : 5;
    }
    
    function getStepName($i){
        $steps=array(null,STEP_WELCOME, STEP_ESSENTIALS);
        if(LICENSE_VIEW==true){
            $steps[]=STEP_LICENSE;
        }
        $steps[]=STEP_DB;
        $steps[]=STEP_INFO;
        $steps[]=STEP_FINISH;
        return @$steps[$i];
    }
    
    function getStepInfo($i){
        $steps=array(null,STEP_WELCOME_I, STEP_ESSENTIALS_I);
        if(LICENSE_VIEW==true){
            $steps[]=STEP_LICENSE_I;
        }
        $steps[]=STEP_DB_I;
        $steps[]=STEP_INFO_I;
        $steps[]=STEP_FINISH_I;
        return @$steps[$i];
    }
    
    function getLevel($l){
        global $controller;
        $l= @'level'.(int)$l;
        if(method_exists($controller, $l)){
            $controller->$l();
        }else{
            die('Invalid Method.');
        }
    }
    
    function addNext($key=null, $other_tags=null){
        global $_step;
        $x='';
        if($key){
            $x='<input type="hidden" name="key" value="'.htmlspecialchars($key).'"/>';
        }
        return '<form><input type="hidden" name="step" value="'.($_step+1).'"/>'.$x.$other_tags.'<input type="submit" class="btn" value="'.FORM_NEXT.'" align="center"/></form>';
    }
    
    function addBack(){
        return '<input type="button" class="btn" name="back" onclick="history.go(-1);" value="'.FORM_BACK.'"/>';
    }
    
   	function makeRandStr($length=6) {
		$salt = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
		$len = strlen($salt);
		$makepass="";
		mt_srand(10000000*(double)microtime());
		for ($i = 0; $i < $length; $i++)
		$makepass .= $salt[mt_rand(0,$len - 1)];
		return $makepass;
	}
	
	function is_email($user_email) {
		$chars = "/^([a-z0-9+_]|\\-|\\.)+@(([a-z0-9_]|\\-)+\\.)+[a-z]{2,6}\$/i";
		if (strpos($user_email, '@') !== false && strpos($user_email, '.') !== false) {
			if (preg_match($chars, $user_email)) {
				return true;
			} else {
				return false;
			}
		} else {
			return false;
		}
	}
	
	function getCalendarOffsetSelectTag($name='v_time_offset'){
		return '
<select name="'.htmlspecialchars($name).'" size="1" class="textbox" style="font-size: 10px; width: 100%;">
<option value="-12">(UTC -12:00) International Date Line West</option>
<option value="-11">(UTC -11:00) Midway Island, Samoa</option>
<option value="-10">(UTC -10:00) Hawaii</option>
<option value="-9.5">(UTC -09:30) Taiohae, Marquesas Islands</option>
<option value="-9">(UTC -09:00) Alaska</option>
<option value="-8">(UTC -08:00) Pacific Time (US &amp; Canada)</option>
<option value="-7">(UTC -07:00) Mountain Time (US &amp; Canada)</option>
<option value="-6">(UTC -06:00) Central Time (US &amp; Canada), Mexico City</option>
<option value="-5">(UTC -05:00) Eastern Time (US &amp; Canada), Bogota, Lima</option>
<option value="-4">(UTC -04:00) Atlantic Time (Canada), Caracas, La Paz</option>
<option value="-3.5">(UTC -03:30) St. John\'s, Newfoundland and Labrador</option>
<option value="-3">(UTC -03:00) Brazil, Buenos Aires, Georgetown</option>
<option value="-2">(UTC -02:00) Mid-Atlantic</option>
<option value="-1">(UTC -01:00) Azores, Cape Verde Islands</option>
<option value="0" selected="selected">(UTC 00:00) Western Europe Time, London, Lisbon, Casablanca</option>
<option value="1">(UTC +01:00) Amsterdam, Berlin, Brussels, Copenhagen, Madrid, Paris</option>
<option value="2">(UTC +02:00) Istanbul, Jerusalem, Kaliningrad, South Africa</option>
<option value="3">(UTC +03:00) Baghdad, Riyadh, Moscow, St. Petersburg</option>
<option value="3.5">(UTC +03:30) Tehran</option>
<option value="4">(UTC +04:00) Abu Dhabi, Muscat, Baku, Tbilisi</option>
<option value="4.5">(UTC +04:30) Kabul</option>
<option value="5">(UTC +05:00) Ekaterinburg, Islamabad, Karachi, Tashkent</option>
<option value="5.5">(UTC +05:30) Bombay, Calcutta, Madras, New Delhi</option>
<option value="5.75">(UTC +05:45) Kathmandu</option>
<option value="6">(UTC +06:00) Almaty, Dhaka, Colombo</option>
<option value="6.3">(UTC +06:30) Yagoon</option>
<option value="7">(UTC +07:00) Bangkok, Hanoi, Jakarta</option>
<option value="8">(UTC +08:00) Beijing, Perth, Singapore, Hong Kong</option>
<option value="8.75">(UTC +08:00) Western Australia</option>
<option value="9">(UTC +09:00) Tokyo, Seoul, Osaka, Sapporo, Yakutsk</option>
<option value="9.5">(UTC +09:30) Adelaide, Darwin, Yakutsk</option>
<option value="10">(UTC +10:00) Eastern Australia, Guam, Vladivostok</option>
<option value="10.5">(UTC +10:30) Lord Howe Island (Australia)</option>
<option value="11">(UTC +11:00) Magadan, Solomon Islands, New Caledonia</option>
<option value="11.3">(UTC +11:30) Norfolk Island</option>
<option value="12">(UTC +12:00) Auckland, Wellington, Fiji, Kamchatka</option>
<option value="12.75">(UTC +12:45) Chatham Island</option>
<option value="13">(UTC +13:00) Tonga</option>
<option value="14">(UTC +14:00) Kiribati</option>
</select>';
	}
	
	function splitSQL($queries){
		$start = 0;
		$open = false;
		$open_char = '';
		$end = strlen($queries);
		$query_split = array();
		for($i=0;$i<$end;$i++) {
			$current = substr($queries,$i,1);
			if(($current == '"' || $current == '\'' || $current == '`')) {
				$n = 2;
				while(substr($queries,$i - $n + 1, 1) == '\\' && $n < $i) {
					$n ++;
				}
				if($n%2==0) {
					if ($open) {
						if($current == $open_char) {
							$open = false;
							$open_char = '';
						}
					} else {
						$open = true;
						$open_char = $current;
					}
				}
			} 
			if(($current == ';' && !$open)|| $i == $end - 1) {
				$query_split[] = substr($queries, $start, ($i - $start + 1));
				$start = $i + 1;
			}
		}

		return $query_split;
	}
	
	function hash($str){
		$str=md5($str);
		$len = 32;
		return md5(substr(md5($_SESSION['secret']), 0, sprintf('%u', $len/2)).$str.substr(md5($_SESSION['secret']), sprintf('%u', $len/2), $len));
	}
}


function redirect($url=null) {
	if($url == null) {
		$url = 'index.php';
	}
	if(headers_sent()) {
		echo "<script>document.location.href='$url';</script><noscript><a href=\"$url\">$url</a></noscript>\n";
	} else {
		header('HTTP/1.1 301 Moved Permanently');
		header('Location: '.$url);
	}
	
	die();
}
?>
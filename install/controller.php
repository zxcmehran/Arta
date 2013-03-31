<?php
defined('ARTAINSTALLER_INSIDE') or die();

class InstallerController{

    function setLang($lang){
        global $model;
        $available=$model->getAvailableLangs();
        if(@$available[$lang]!=false){
            $_SESSION['lang']=$lang;
        }else{
            die('Invalid Language File.');
        }
        redirect();
    }
    
    function getRespectiveLevel($i){
        if(!LICENSE_VIEW && $i>2) $i++;
        if(!method_exists($this, '_level'.$i)){
            die('Invalid Method.');
        }
        $this->{'_level'.$i}();
    }
    
    function level1(){
        global $helper;
        printf(WELCOME_MSG, VERSION);
        if(LICENSE_VIEW==false){
        	echo ARTA_IS_UNDER_GPL;
        }
        echo $helper->addNext();
    }
    
    function level2(){
        global $helper,$model;
        $_SESSION['key']=$helper->makeRandStr(12);
        echo THE_FOLLOWING_IS_MANDATORY;
        echo '<table width="100%" cellspacing="10">';
        $conditions=array(IS_PHP5=> version_compare(PHP_VERSION, '5.0.0', '>'),
        IS_MYSQL_ENABLED=>extension_loaded('mysql')||extension_loaded('mysqli'),
        IS_PCRE_ENABLED=>extension_loaded('pcre'),
        IS_SIMPLEXML_ENABLED=>extension_loaded('simplexml'),
        IS_XMLPARSER_ENABLED=>extension_loaded('xml'),
        IS_GD_ENABLED=>extension_loaded('gd'),
        IS_CURL_ENABLED=>extension_loaded('curl'),
        IS_ZLIB_ENABLED=>extension_loaded('zlib'),
        );
        foreach($conditions as $n=>$b){
            echo '<tr><td>'.htmlspecialchars($n).'</td><td><b style="color:'.($b?'green;">'.htmlspecialchars(BOOL_YES):'red;">'.htmlspecialchars(BOOL_NO)).'</b></td></tr>';
        }
        echo '</table>';
        echo '<hr/>';
        
        echo THE_FOLDERS_ARE_MANDATORY;
        echo '<table width="100%" cellspacing="10">';
        $dirs=$model->getDirectoryPerms();
        $writeable= '<b><font color="green">'. FILE_WRITABLE .'</font></b>';
		$unwriteable= '<b><font color="red">'. FILE_UNWRITABLE .'</font></b>';
        foreach($dirs as $k=>$v){
        	echo '<tr><td>'.htmlspecialchars($k).'</td><td>'.($v?$writeable:$unwriteable).'</td></tr>';
        }
        echo '</table>';
        
        if(in_array(false, $conditions) || in_array(false, $dirs)){
            echo YOU_ARE_FAILED_IN_REQUIREMENTS;
        }else{
            echo YOU_ARE_DONE_IN_REQUIREMENTS;
            echo $helper->addNext($_SESSION['key']);
        }
    }
    
    function level3(){
        $this->getRespectiveLevel(3);        
    }
    
    function level4(){
        $this->getRespectiveLevel(4);        
    }
    
    function level5(){
        $this->getRespectiveLevel(5);        
    }
    
    function level6(){
        $this->getRespectiveLevel(6);
    }
    
    function _level3(){
        global $model,$helper,$_step;
        if(@$_REQUEST['key'] !== @$_SESSION['key'] && @$_SESSION['last_valid_step']!= $_step){ // last_valid_step FOR avoid recheck $_REQUEST['key'] while $_SESSION['key'] is changed in this step on page refresh.
            die('It seems that you are not passed last step. Please go back.');
        }else{
            $_SESSION['last_valid_step']= $_step;
        }
        
        echo '<textarea readonly="readonly" style="width:95%; height:300px; overflow:auto;direction:ltr; background:white;">';
        echo htmlspecialchars($model->getLicenseAgreement());
        echo '</textarea>';
        
        $_SESSION['key']=$helper->makeRandStr(12);       
        echo $helper->addNext($_SESSION['key'], '<input name="agree" type="checkbox" value="1" id="agree_check"/><label for="agree_check">'.I_AGREE_LICENSE.'</label><br/>');
    }
    
    function _level4(){
        global $_step,$helper;
        if(LICENSE_VIEW && @$_REQUEST['agree']!=true && @$_SESSION['agree']!=true){
            die(YOUMUST_ACCEPT_LICENSE);
        }else{
        	$_SESSION['agree']=@$_REQUEST['agree'];
        }
        if(@$_REQUEST['key'] !== @$_SESSION['key'] && @$_SESSION['last_valid_step']!= $_step){
            die('It seems that you are not passed last step. Please go back.');
        }else{
            $_SESSION['last_valid_step']= $_step;
        }
        $_SESSION['key']=$helper->makeRandStr(12);
        
        @$_POST['db_host']!=null ? $this->_level4b() : $this->_level4a();
    }
    
    function _level4a(){
        global $_step;
        echo SET_DB_DETAILS;
        echo '<form method="post" action="index.php">';
        
        echo SET_DB_HOST;
        echo '<table style="width:95%;"><tr><td style="width:40%;">'.DB_HOST.':</td><td><input name="db_host" value="localhost"/></td></tr></table><hr/>';
                
        echo SET_DB_NAME;
        echo '<table style="width:95%;"><tr><td style="width:40%;">'.DB_NAME.':</td><td><input name="db_name" value=""/></td></tr></table><hr/>';
        
        echo SET_DB_CREDENTIALS;
        echo '<table style="width:95%;"><tr><td style="width:40%;">'.DB_USER.':</td><td><input name="db_user" value=""/></td></tr><tr><td style="width:40%;">'.DB_PASS.':</td><td><input name="db_pass" type="password" value=""/> *</td></tr></table><hr/>';
        
        echo SET_DB_PREFIX;
        echo '<table style="width:95%;"><tr><td style="width:40%;">'.DB_PREFIX.':</td><td><input name="db_prefix" value="arta_"/></td></tr></table><hr/>';
        
        echo SET_DB_TYPE;
        echo '<table style="width:95%;"><tr><td style="width:40%;">'.DB_TYPE.':</td><td><select name="db_type">';
        if(extension_loaded('mysqli')){
            echo '<option value="mysqli" selected="true">MySQLi</option>';
        }
        if(extension_loaded('mysql') && extension_loaded('mysqli')==false){
            echo '<option value="mysql" selected="true">MySQL</option>';
        }elseif(extension_loaded('mysql')){
            echo '<option value="mysql">MySQL</option>';
        }
        echo '</select></td></tr></table><br/>';
        echo '<br/><input type="hidden" name="step" value="'.($_step).'"/><input type="submit" value="'.FORM_VERIFY.'" align="center"/>';
                
        echo '</form>';
    }
    
    function _level4b(){
        global $helper,$model;
        
        if (!preg_match('#^[a-zA-Z]+[a-zA-Z0-9_]*$#', $_POST['db_prefix'])) {
			printf('<div class="error">'.INVALID_DB_PPREFIX_CHARS.'</div>');
            echo $helper->addBack();
            return;
		}
        
        if($_POST['db_type']=='mysqli'){
            $db=@ new mysqli($_POST['db_host'],$_POST['db_user'], $_POST['db_pass'], $_POST['db_name']);
            if(mysqli_connect_errno()!=0){
                printf('<div class="error">'.INVALID_DB_INFO.'</div>', @('<span dir="ltr">'.mysqli_connect_errno().': '.mysqli_connect_error().'</span>'));
                echo $helper->addBack();
                return;
            }
            
            $info= $db->server_info;
            $verParts = explode('.', $info);
	       	if(!((int)$verParts[0] >= 5 || ((int)$verParts[0] == 4 && (int)$verParts[1] >= 1 && (int)$verParts[2] >= 2))){
	       	    echo '<div class="error">'.MYSQL_MUSTBE_GREATER_THAN_412.'</div>';
                return;
	       	}
            
            echo DB_CONNECTED_SUCC;
            
            $db->query("SET NAMES 'utf8'");
            $db->query("SET CHARACTER SET 'utf8'");
            $db->query("SET sql_mode = 'MYSQL40'");
            
            /*
			 Commented out because:
			 http://bugs.mysql.com/bug.php?id=53645
			
            $r = @$db->query('SHOW GRANTS FOR CURRENT_USER()');
            if($r){ // if possible do a test.
                $obj = @$r->fetch_array();
                preg_match_all('@GRANT +([A-Za-z, ]*) +ON @mi',@$obj[0],$match); 
                if(@$match[1][0]!=null){
                    if($helper->isPrivsEnough($match[1][0])==false){
                        echo '<div class="error">'.NO_ENOUGH_PRIVS.'</div>';
                        echo $helper->addBack();
                        return;
                    }
                }
            }
			 */
            
            if($model->testMYSQLI($db)==false){
                echo '<div class="error">'.CANNOT_EXECUTE_TEST_QUERIES.'<br/><pre>'.($db->errno==0?'Collation Error: Could not read inserted unicode sample content.':($db->errno.': '.$db->error)).'</pre></div>';
                echo $helper->addBack();
                return;
            }
            echo '<p>'.TEST_QUERIES_EXECD_SUCC.'</p>';
        }else{
            $db=@ mysql_connect($_POST['db_host'],$_POST['db_user'], $_POST['db_pass']);
            
            if($db==false || @mysql_select_db($_POST['db_name'], $db)==false){
                printf('<div class="error">'.INVALID_DB_INFO.'</div>', @('<span dir="ltr">'.mysql_errno().': '.mysql_error().'<span>'));
                echo $helper->addBack();
                return;
            }
            
            $info= mysql_get_server_info($db);
            $verParts = explode('.', $info);
	       	if(!((int)$verParts[0] >= 5 || ((int)$verParts[0] == 4 && (int)$verParts[1] >= 1 && (int)$verParts[2] >= 2))){
	       	    echo '<div class="error">'.MYSQL_MUSTBE_GREATER_THAN_412.'</div>';
                return;
	       	}
	       	
	       	echo '<p>'.DB_CONNECTED_SUCC.'</p>';
            
            mysql_query("SET NAMES 'utf8'", $db);
            mysql_query("SET CHARACTER SET 'utf8'", $db);
            mysql_query("SET sql_mode = 'MYSQL40'", $db);
            
			/*
			 Commented out because:
			 http://bugs.mysql.com/bug.php?id=53645
			
            $r = @mysql_query('SHOW GRANTS FOR CURRENT_USER()', $db);
            if($r){ // if possible do a test.
                $obj = @mysql_fetch_array($r);
                preg_match_all('@GRANT +([A-Za-z, ]*) +ON @mi',@$obj[0],$match); 
                if(@$match[1][0]!=null){
                    if($helper->isPrivsEnough($match[1][0])==false){
                        echo '<div class="error">'.NO_ENOUGH_PRIVS.'</div>';
                        echo $helper->addBack();
                        return;
                    }
                }
            }
            */
            if($model->testMYSQL($db)==false){
                echo '<div class="error">'.CANNOT_EXECUTE_TEST_QUERIES.'<br/><pre>'.(mysql_errno($db)==0?'Collation Error: Could not read inserted unicode sample content.':(mysql_errno($db).': '.mysql_error($db))).'</pre></div>';
                echo $helper->addBack();
                return;
            }
            
            echo '<p>'.TEST_QUERIES_EXECD_SUCC.'</p>';
        }
        
        $_SESSION['db_data']=$_POST;
        
        echo '<div class="succ">'.DB_IS_NOW_AVAILABLE.'</div>';
        
        echo $helper->addNext($_SESSION['key']);
        
    }
    
    function _level5(){
        global $_step,$helper;
        if(@$_REQUEST['key'] !== $_SESSION['key'] && @$_SESSION['last_valid_step']!= $_step){
            die('It seems that you are not passed last step. Please go back.');
        }else{
            $_SESSION['last_valid_step']= $_step;
        }
		$_SESSION['key']=$helper->makeRandStr(12);

		if(@$_POST['verify']=='true'){
			
			if(trim($_POST['v_sitename'])==''){
				echo '<div class="error">'.NO_SITENAME_SPECIFIED.'</div>';
				echo $helper->addBack();
				return;
			}
			
			if(trim($_POST['v_username'])=='' || strpos($_POST['v_username'], ' ')!==false){
				echo '<div class="error">'.NO_USERNAME_SPECIFIED.'</div>';
				echo $helper->addBack();
				return;
			}
			
			if(strlen($_POST['v_password'])<6){
				echo '<div class="error">'.INVALID_PASS_SPECIFIED.'</div>';
				echo $helper->addBack();
				return;
			}
			
			if($_POST['v_password'] !== $_POST['v_password_verify']){
				echo '<div class="error">'.INVALID_PASSV_SPECIFIED.'</div>';
				echo $helper->addBack();
				return;
			}
			
			if($helper->is_email($_POST['v_email'])==false){
				echo '<div class="error">'.INVALID_EMAIL_SPECIFIED.'</div>';
				echo $helper->addBack();
				return;
			}
			$_SESSION['inf']=$_POST;
			echo '<div class="succ">'.READY_TO_INST.'</div>';
			echo $helper->addNext($_SESSION['key']);
			
			return;
		}
        
        echo THE_LAST_STEP_IS_HERE;
        echo '<form method="post" action="index.php">';
        
        echo SET_WEBSITE_TITLE;
        echo '<table style="width:95%;">
<tr><td style="width:40%;">'.L_SITENAME.':</td><td><input name="v_sitename" value="Arta"/></td></tr>
<tr><td style="width:40%;">'.L_HOMEPAGE_TITLE.':</td><td><input name="v_homepage_title" value="Welcome to Arta! The Web Revolution..." style="width:100%"/></td></tr>
<tr><td style="width:40%;">'.L_DESCRIPTION.':</td><td><textarea style="width:100%; height:80px;" name="v_description">The most flexible content management system is here to to power this website. This website is powered by Arta Content Management Framework.</textarea></td></tr>
<tr><td style="width:40%;">'.L_KEYWORDS.':</td><td><textarea style="width:100%" name="v_keywords">Arta, CMS, flexible, powerful, secure</textarea></td></tr>
</table><hr/>';

        echo SET_USER_CREDENTIALS;
        echo '<table style="width:95%;">
<tr><td style="width:40%;">'.L_USERNAME.':</td><td><input name="v_username" value="admin"/></td></tr>
<tr><td style="width:40%;">'.L_PASSWORD.':</td><td><input name="v_password" type="password"/></td></tr>
<tr><td style="width:40%;">'.L_PASSWORD_VERIFY.':</td><td><input name="v_password_verify" type="password"/></td></tr>
<tr><td style="width:40%;">'.L_EMAIL.':</td><td><input name="v_email"/></td></tr>
</table><hr/>';

        echo SET_CALENDAR;
        echo '<table style="width:95%;">
<tr><td style="width:40%;">'.L_TIME_OFFSET.':</td><td>'.$helper->getCalendarOffsetSelectTag().'</td></tr>
<tr><td style="width:40%;">'.L_CAL_TYPE.':</td><td>
<select name="v_cal_type">
	<option value="gregorian" selected="selected">'.CAL_GRE.'</option>		
	<option value="jalali">'.CAL_JAL.'</option>		
</select>
</td></tr>
</table><hr/>';
		echo '<iframe src="rewrite_test/test_rewriting_method" width="50" height="50" align="'.(LANG_DIR=='ltr'?'right':'left').'" style=" border: 1px solid white;overflow:hidden; background:white;"></iframe>';
		echo SET_SEF;
        echo '<table style="width:95%;">
<tr><td style="width:40%;">'.L_URL_FRIENDLY.'</td><td>
<input type="radio" value="1" name="v_url_friendly" id="uf1"/><label for="uf1">'.YES.'</label> 
<input type="radio" value="0" name="v_url_friendly" checked="checked" id="uf0"/><label for="uf0">'.NO.'</label>
</td></tr>
</table><hr/>';
        
        echo '<br/><input type="hidden" name="step" value="'.($_step).'"/><input type="hidden" name="verify" value="true"/><input type="submit" value="'.FORM_VERIFY.'" align="center"/>';
                
        echo '</form>';
    }
    
    function _level6(){
    	global $_step,$helper;
        if(@$_REQUEST['key'] !== $_SESSION['key'] && @$_SESSION['last_valid_step']!= $_step){
            die('It seems that you are not passed last step. Please go back.');
        }else{
            $_SESSION['last_valid_step']= $_step;
        }
    	if(!isset($_REQUEST['process']) || (int)$_REQUEST['process']<=0){
    		$_REQUEST['process']=1;
    	}
    	$_REQUEST['process']=(int)$_REQUEST['process'];
    	define('DBTYPE', $_SESSION['db_data']['db_type']);
    	eval('$this->_level6'.$_REQUEST['process'].'();');
    }
    
    function _level61(){
    	global $model,$helper,$_step;
    	
    	$data=file_get_contents('db_create.sql');
    	$queries=$helper->splitSQL($data);
    	$qc=count($queries);
    	if(trim($queries[$qc-1])==''){
    		array_pop($queries);
    		$qc--;
    	}
    	$_SESSION['rand']=mt_rand(1,99);
    	echo '<br/><code style="width: 95%;">';
    	foreach($queries as $k=>$q){
    		if(strpos($q, '{$RAND}')){
    			$q=str_replace('{$RAND}', $_SESSION['rand'], $q);
    		}
    		if($model->query(trim($q))!==false || @$_SESSION['CREATE']==true){
    			echo 'Created table '.($k+1).' of '.$qc.' successfully.<br/>';
    			flush();
    		}else{
    			echo 'Error on creating table '.($k+1).' of '.$qc.':<br/>'.htmlspecialchars($q).'<br/>'.htmlspecialchars($model->getError()).'</code>';
    			return false;
    		}
		}
    	
    	echo '</code>';
    	
    	$_SESSION['CREATE']=true;
    	
    	echo '<br/><form><input type="hidden" name="step" value="'.($_step).'"/><input type="hidden" name="process" value="2"/><input type="submit" value="'.FORM_NEXT.'" align="center"/>';
                
        echo '</form>';
    }
    
    function _level62(){
    	global $model,$helper,$_step;
    	
    	$data=file_get_contents('db_insert.sql');
    	$queries=$helper->splitSQL($data);
    	$qc=count($queries);
    	if(trim($queries[$qc-1])==''){
    		array_pop($queries);
    		$qc--;
    	}
    	echo '<br/><code style="width: 95%;">';
    	foreach($queries as $k=>$q){
    		if(strpos($q, '{$RAND}') || strpos($q, '{$VERSION}')){
    			$q=str_replace(array('{$RAND}', '{$VERSION}'), array($_SESSION['rand'], VERSION), $q);
    		}
    		if($model->query(trim($q))!==false || @$_SESSION['INSERT']==true){
    			echo 'Inserted data into table '.($k+1).' of '.$qc.' successfully.<br/>';
    			flush();
    		}else{
    			echo 'Error on inserting data into table '.($k+1).' of '.$qc.':<br/>'.htmlspecialchars($q).'<br/>'.htmlspecialchars($model->getError()).'</code>';
    			return false;
    		}
		}
		
		$_SESSION['secret']=md5($helper->makeRandStr(12));
		$q='INSERT INTO #__users VALUES (NULL, \'Administrator\', '.$model->Quote($_SESSION['inf']['v_username']).','.
						$model->Quote($_SESSION['inf']['v_email']).','.$model->Quote($helper->hash($_SESSION['inf']['v_password'])).',2,0,\'\','.
						$model->Quote(gmdate('Y-m-d H:i:s', time())).','.$model->Quote(gmdate('Y-m-d H:i:s', time())).',0,\'\',NULL,NULL)';
		if($model->query($q)!==false || @$_SESSION['INSERT']==true){
			echo 'Created first user successfully.<br/>';
			flush();
		}else{
			echo 'Error on creating first user:<br/>'.htmlspecialchars($q).'<br/>'.htmlspecialchars($model->getError()).'</code>';
			return false;
		}
		
    	$_SESSION['INSERT']=true;
    	
    	echo '</code>';
    	
    	echo '<br/><form><input type="hidden" name="step" value="'.($_step).'"/><input type="hidden" name="process" value="3"/><input type="submit" value="'.FORM_NEXT.'" align="center"/>';
                
        echo '</form>';
    }
    
    function _level63(){
    	global $model,$helper,$_step;
    	
    	if(@$_SESSION['CONFIG']==true){
    		return $this->_level64();
    	}
    	
    	$data=file_get_contents('config_template.php');
    	$replace=array(
    		'{$version}',
    		'{$time_formatted}',
			'{$site_name}',
			'{$homepage_title}',
			'{$description}',
			'{$keywords}',
			'{$time_offset}',
			'{$cal_type}',
			'{$db_host}',
			'{$db_user}',
			'{$db_pass}',
			'{$db_name}',
			'{$db_prefix}',
			'{$db_type}',
			'{$sef}',
			'{$sef_rewrite}',
			'{$v_email}',
			'{$secret}',
			'{$time}'
		);
		$val=array(
			VERSION,
			gmdate('Y-m-d H:i:s'),
			$_SESSION['inf']['v_sitename'],
			$_SESSION['inf']['v_homepage_title'],
			$_SESSION['inf']['v_description'],
			$_SESSION['inf']['v_keywords'],
			$_SESSION['inf']['v_time_offset'],
			$_SESSION['inf']['v_cal_type'],
			$_SESSION['db_data']['db_host'],
			$_SESSION['db_data']['db_user'],
			$_SESSION['db_data']['db_pass'],
			$_SESSION['db_data']['db_name'],
			$_SESSION['db_data']['db_prefix'],
			$_SESSION['db_data']['db_type'],
			$_SESSION['inf']['v_url_friendly']?2:0,
			$_SESSION['inf']['v_url_friendly']?1:0,
			$_SESSION['inf']['v_email'],
			$_SESSION['secret'],
			time()
		);
		$val=array_map('addslashes', $val);
		
		$data=str_replace($replace, $val, $data);
		@chmod(ROOTDIR.'/../config.php', 0644);
		if(file_put_contents(ROOTDIR.'/../config.php', $data) || @$_SESSION['CONFIG']==true){
			@chmod(ROOTDIR.'/../config.php', 0444);
			$_SESSION['CONFIG']=true;
			echo '<div class="succ">'.CONFIG_WRITE_SUCC.'</div>';
		}else{
			echo '<div class="error">'.CONFIG_WRITE_ERROR.'</div>';
			echo '<br/><form><input type="hidden" name="step" value="'.($_step).'"/><input type="hidden" name="process" value="3"/><input type="submit" value="'.FORM_RETRY.'" align="center"/></form>';
		}
		
    	echo '<br/><form><input type="hidden" name="step" value="'.($_step).'"/><input type="hidden" name="process" value="4"/><input type="submit" value="'.FORM_NEXT.'" align="center"/></form>';    
    }
    
    function _level64(){
    	$url=substr($_SERVER['SCRIPT_NAME'],0, strlen($_SERVER['SCRIPT_NAME'])-strlen('install/index.php')).'admin';
		require_once('../library/xmlrpc/xmlrpc/lib/xmlrpc.inc');
		require_once('../library/xmlrpc/xmlrpc/lib/xmlrpc_wrappers.inc');
		$connection=new xmlrpc_client('/arta/logger.php', 'cc.artaproject.com');
		$GLOBALS['xmlrpc_defencoding'] = "UTF8";
		$GLOBALS['xmlrpc_internalencoding'] = 'UTF-8';
		$GLOBALS['xmlrpcName'].=' on Arta Installation';
		$GLOBALS['xmlrpcVersion'] = '';
		$connection->request_charset_encoding='UTF-8';
		$connection->setAcceptedCompression('gzip');
		
		$params = array(
			(string)md5(md5($_SESSION['secret'])),
			(string)VERSION,
			
		);
		foreach($params as $k=>$v){
			$params[$k]=php_xmlrpc_encode($v);
		}
		$f=new xmlrpcmsg('logger.logCoreInstallation', $params);
		//$connection->setDebug(2);
		$connection->send($f);
		
    	echo sprintf(FINISH_MSG, $url.'/index.php?pack=config&view=config');
    	echo LICENSE_VIEW?LICENSE_MSG:'';
    	echo '<b>'.REMOVAL_MSG.'</b>';
    	
    	
    	echo '<a style="font-size:200%; color: #FFFFFF;" href="'.htmlspecialchars($url).'">'.TO_ADMIN.'</a>';
    	
    	echo '<br/><br/>'.BYEBYE_MSG;
		session_destroy();
    }
}

?>
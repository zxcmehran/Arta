<?php
defined('ARTAINSTALLER_INSIDE') or die();

class InstallerModel{

    function getAvailableLangs(){
        if(is_file(ROOTDIR.'/languages/map.ini')==false){
            die('No language map found.');
        }
        $c = file_get_contents(ROOTDIR.'/languages/map.ini');
        $c .= "\n";
        $c = str_replace(array("\r\n","\r","\n"),"\n",$c);
        $c = explode("\n",$c);
        $data = array();
        foreach($c as $l){
            if(trim($l)==''){
                continue;
            }
            if(strpos($l, '=')){
                $term=explode('=',$l);
                $data[array_shift($term)]=implode('=', $term);
            }else{
                $data[$l]=$l;
            }
        }
        foreach($data as $k=>$v){
            if(!file_exists(ROOTDIR.'/languages/'.$k.'.php')){
                unset($data[$k]);
            }
        }
        return $data;        
    }    
    
    function getLicenseAgreement(){
        if(!is_file(ROOTDIR.'/license.txt')){
            die('No license agreement file found.');
        }
        return file_get_contents(ROOTDIR.'/license.txt');
    }
    
    function getDirectoryPerms(){
    	global $helper;
    	$d=ROOTDIR.'/..';
    	$l=array(
		'admin',
		'admin/backup',
		'admin/help',
		'admin/imagesets',
		'admin/includes',
		'admin/languages',
		'admin/modules',
		'admin/packages',
		'admin/plugins',
		'admin/templates',
		'admin/tmp',
		'content',
		'crons',
		'imagesets',
		'includes',
		'languages',
		'library',
		'library/external',
		'media',
		'modules',
		'packages',
		'plugins',
		'templates',
		'tmp',
		'webservices',
		'widgets');
		$output=array();
		foreach($l as $v){
			if(!is_dir($d.'/'.$v)){
				@mkdir($d.'/'.$v);
			}
			$output[$v]=is_writeable($d.'/'.$v);
		}
		return $output;
    }
    
    function testMYSQLI($db){
        global $helper;
        $uniq=$helper->makeRandStr();
        $sql = 'CREATE TABLE testing_'.$uniq.' (id MEDIUMINT(11) NOT NULL AUTO_INCREMENT, content VARCHAR(50) NOT NULL, PRIMARY KEY (id)) ENGINE = MyISAM CHARACTER SET utf8'; 
        if($db->query($sql)==false){
            return false;
        }
        
        $sql = 'INSERT INTO testing_'.$uniq.' VALUES (NULL, \'Test\'), (NULL, \''.base64_decode('2KrYs9iqINqp2KfYsdin2qnYqtixINmH2KfbjCDbjNmI2YbbjNqp2K8=').'\')'; 
        if($db->query($sql)==false){
            return false;
        }
        
        $sql = 'SELECT * FROM testing_'.$uniq; 
        $r=$db->query($sql);
        if($r==false){
            return false;
        }
        
        $row1 = $r->fetch_array();
        $row2 = $r->fetch_array();
        
        if(!($row1['id']=='1' && $row2['id']=='2' && $row1['content']=='Test' && $row2['content']==base64_decode('2KrYs9iqINqp2KfYsdin2qnYqtixINmH2KfbjCDbjNmI2YbbjNqp2K8='))){
            return false;
        }
        
        $sql = 'DROP TABLE testing_'.$uniq; 
        if($db->query($sql)==false){
            return false;
        }
        
        return true;
    }
    
    function testMYSQL($db){
        global $helper;
        $uniq=$helper->makeRandStr();
        $sql = 'CREATE TABLE testing_'.$uniq.' (id MEDIUMINT(11) NOT NULL AUTO_INCREMENT, content VARCHAR(50) NOT NULL, PRIMARY KEY (id)) ENGINE = MyISAM CHARACTER SET utf8'; 
        if(mysql_query($sql, $db)==false){
            return false;
        }
        
        $sql = 'INSERT INTO testing_'.$uniq.' VALUES (NULL, \'Test\'), (NULL, \''.base64_decode('2KrYs9iqINqp2KfYsdin2qnYqtixINmH2KfbjCDbjNmI2YbbjNqp2K8=').'\')'; 
        if(mysql_query($sql, $db)==false){
            return false;
        }
        
        $sql = 'SELECT * FROM testing_'.$uniq; 
        $r=mysql_query($sql, $db);
        if($r==false){
            return false;
        }
        
        $row1 = mysql_fetch_array($r);
        $row2 = mysql_fetch_array($r);
        
        if(!($row1['id']=='1' && $row2['id']=='2' && $row1['content']=='Test' && $row2['content']==base64_decode('2KrYs9iqINqp2KfYsdin2qnYqtixINmH2KfbjCDbjNmI2YbbjNqp2K8='))){
            return false;
        }
        
        $sql = 'DROP TABLE testing_'.$uniq; 
        if(mysql_query($sql,$db)==false){
            return false;
        }
        
        return true;
    }
    
    function getConnection(){
    	global $dbc;
    	if($dbc==null){
    		if(DBTYPE=='mysqli'){
    			$dbc = @ new mysqli($_SESSION['db_data']['db_host'],$_SESSION['db_data']['db_user'], $_SESSION['db_data']['db_pass'], $_SESSION['db_data']['db_name']);
	            if(mysqli_connect_errno()!=0){
	                return false;
	            }
	            $dbc->query("SET NAMES 'utf8'");
	            $dbc->query("SET CHARACTER SET 'utf8'");
	            $dbc->query("SET sql_mode = 'MYSQL40'");
	    	}else{
	    		$dbc =@ mysql_connect($_SESSION['db_data']['db_host'],$_SESSION['db_data']['db_user'], $_SESSION['db_data']['db_pass']);
	            if(mysql_errno($dbc)!=0 || @mysql_select_db($_SESSION['db_data']['db_name'], $dbc)==false){
	                return false;
	            }
	            mysql_query("SET NAMES 'utf8'", $dbc);
	            mysql_query("SET CHARACTER SET 'utf8'", $dbc);
	            mysql_query("SET sql_mode = 'MYSQL40'", $dbc);
	            
    		}
    	}
    	return $dbc;
    } 
    
    function query($sql){
    	$db=$this->getConnection();
    	if($db==false){
    		return false;
    	}
    	$sql=str_replace('#__', $_SESSION['db_data']['db_prefix'], $sql);
    	if(DBTYPE=='mysqli'){
    		return $db->query($sql);
    	}else{
    		return mysql_query($sql, $db);
    	}
    }
    
    function Quote($string, $escape=true){
		if($escape){
			return "'".$this->getEscaped($string)."'";
		}else{
			return "'".$string."'";
		}
	}
	
	function getError(){
    	$db=$this->getConnection();
    	if($db==false){
    		return false;
    	}
    	if(DBTYPE=='mysqli'){
    		return $db->errno.': '.$db->error;
    	}else{
    		return mysql_errno($db).': '.mysql_error($db);
    	}
    }
	
	function getEscaped($text){
		$db=$this->getConnection();
		if(DBTYPE=='mysqli'){
			$result = mysqli_real_escape_string($db, $text);
		}else{
			$result = mysql_real_escape_string($text, $db);
		}
		return $result;
	}
    
}

?>
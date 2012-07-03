<?php
defined('ARTAINSTALLER_INSIDE') or die();

class InstallerView{
    
    private $content=array();
    
    function __construct(){
        if(is_file(ROOTDIR.'/template/index.html')==false){
            die('No Template found.');
        }
        $content=file_get_contents(ROOTDIR.'/template/index.html');
        $content=explode('<--SEPARATOR-->',$content);
        $this->content=$content;
    }
    
    function add($name,$content){
        $this->content[0]=str_replace('<TMPLTAG:'.strtoupper($name).'>', $content, $this->content[0]);
        $this->content[1]=str_replace('<TMPLTAG:'.strtoupper($name).'>', $content, $this->content[1]);
    }
    
    function toString($i){
        echo $this->removeDust($this->content[$i]);
    }
    
    function addPreContents(){
        
    }
    
    function removeDust($x){
        $x = preg_replace('@\<TMPLTAG\:[^\>]*\>@mi', '', $x);
        return $x;
    }
    
    
    
}

?>
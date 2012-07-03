<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ModuleMenuHelper{

	function makeContent($level=1, $parent=0){
		if(isset($this->r[$parent])){
			
			if($level==1){
				$style='horizontal" id="root';
			}elseif($level==2){
				$style='dropdown';
			}else{
				$style='flyout';
			}
			
			if(is_object($this->r[$parent])){
				$this->r[$parent]=array($this->r[$parent]);
			}
			$r='<ul class="level'.$level.' '.$style.'">';
				
				
				
				foreach($this->r[$parent] as $k=>$v){
					$unique='leftmenu_'.ArtaString::makeRandStr();					
					
					$r.='<li class="level'.$level.'">';
					if($v->link==''){
						$v->link='#';
					}
					$r.='<a href="'.htmlspecialchars($v->link).'">';
					
					$r.='<span class="menuicon">';
					if($this->img && strlen($v->pic)>0){
						$r.='<img src="'.($v->pic{0}=='#' ? htmlspecialchars(substr($v->pic, 1)) : imageset($v->pic)).'" width="16" height="16"/> ';
					}elseif($this->img){
						$r.='<img src="'.imageset('spacer.png').'" width="16" height="16"/> ';
					}
					$term=trans(str_replace('=','_',$v->title));
					if($term==strtoupper(str_replace('=','_',$v->title)) || trim($term)==''){
						$term=$v->title;
					}
					$r.='</span>'.htmlspecialchars($term).'</a>';
					$r.= $this->makeContent( $level+1, $v->id);
					
					$r.='</li>';
				}
			
			$r.='</ul>';
			return $r;
		}
	}
	
}
?>
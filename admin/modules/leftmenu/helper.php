<?php 
if(!defined('ARTA_VALID')){die('No access');}
class ModuleLeftmenuHelper{

	function makeContent($cookie, $level=1, $parent=0, $collapse=true, $uni=null){
		
		if(isset($this->r[$parent])){
			
			if(is_object($this->r[$parent])){
				$this->r[$parent]=array($this->r[$parent]);
			}
			$r='<ul class="level'.$level.'"'.($collapse!==true ? ' style="display:none;"' : '').($uni==null ? '' : ' id="'.$uni.'"').'>';
				
				
				
				foreach($this->r[$parent] as $k=>$v){
					$unique='leftmenu_'.ArtaString::makeRandStr();					
					
					$r.='<li class="level'.$level.'">';
					if(isset($this->r[$v->id])){
						$r.='<img src="'.(
								in_array($v->id,$cookie) ? 
									imageset('collapse.png') : 
									imageset('uncollapse.png'))
							.'" id="image_'.$unique.'" onclick="menu_handle(\''.$unique.'\', '.$v->id.')"/>';
					}else{
						$r.='<img src="'.imageset('spacer.png').'" width="16" height="16"/>';
					}
					if($v->link==''){
						$v->link='#';
					}
					$r.=' <a href="'.htmlspecialchars($v->link).'">';
					
					if($this->img && strlen($v->pic)>0){
						$r.='<img src="'.($v->pic{0}=='#' ? htmlspecialchars(substr($v->pic, 1)) : imageset($v->pic)).'" width="16" height="16"/> ';
					}elseif($this->img){
						$r.='<img src="'.imageset('spacer.png').'" width="16" height="16"/> ';
					}
					
				/*	if($level==1){
						$r.='<b>'.htmlspecialchars($v->title).'</b></a>';
					}else{*/
						$term=trans(str_replace('=','_',$v->title)); // define terms in "menu" module language file instead of "leftmenu" lang file.
						if($term==strtoupper(str_replace('=','_',$v->title)) || trim($term)==''){
							$term=$v->title;
						}
						$r.=htmlspecialchars($term).'</a>';
					//}
					
					$r.= $this->makeContent($cookie, $level+1, $v->id, in_array($v->id,$cookie), $unique);
					$r.='</li>';
				}
			
			$r.='</ul>';
			
			return $r;
		}
	}
	
}
?>
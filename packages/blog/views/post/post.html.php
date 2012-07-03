<?php
if(!defined('ARTA_VALID')){die('No access');}
class BlogViewPost extends ArtaPackageView{
	
	function Display(){
		
		$m=$this->getModel();
		
		$id=getVar('id',0,'','int');
		
		$post=$m->getPost($id);
		
		$cats=@$this->getCategoryPath($post->blogid->id, $m);
		
		foreach($cats as $k=>$v){
			$this->addPath($v, 'index.php?pack=blog&view=last&blogid='.$k);
		}
		
		$this->addPath($post->title,'index.php?pack=blog&view=post&id='.$post->id);
		
		$this->setTitle($post->title);
		
		$this->assign('m', $m);
		
		$canedit=ArtaUsergroup::getPerm('can_edit_post_comments', 'package', 'blog');
		$caneditothers=ArtaUsergroup::getPerm('can_edit_others_comments', 'package', 'blog');
		$canpub=ArtaUsergroup::getPerm('can_change_comments_publish_status', 'package', 'blog');
		$candel=ArtaUsergroup::getPerm('can_delete_posts_comments', 'package', 'blog');
		$canacc=ArtaUsergroup::getPerm('can_access_unpublished_comments', 'package', 'blog');	
		$canleave=ArtaUsergroup::getPerm('can_leave_comments', 'package', 'blog');	
		$cantouch=ArtaUsergroup::getPerm('can_touch_comment_points', 'package', 'blog');
		$can=ArtaUsergroup::getPerm('can_access_post_comments', 'package', 'blog');
		
		
		$this->assign('canedit', $canedit);
		$this->assign('caneditothers', $caneditothers);
		$this->assign('canpub', $canpub);
		$this->assign('candel', $candel);
		$this->assign('canacc', $canacc);
		$this->assign('canleave', $canleave);
		$this->assign('cantouch', $cantouch);
		$this->assign('can', $can);
		
		if($can){
			$this->assign('comments', $m->getComments($post->id, $canacc));
			
			$ml=$this->getSetting('multilingual_comments',0, 'blog');
			$this->assign('ml',$ml);
			if($ml){
				$this->assign('langs', $m->getLanguages());
			}
		}
		
		$this->assign('cats', $cats);
		$this->assign('post', $post);
		$this->render();
	}
	
	function getCategoryPath($id, $m){
		$p=array();
		$p_ids=array();

		$r=$m->getBlogID($id);
		while(is_numeric($r->parent) && $r->parent!=='0'){
			$p[]=htmlspecialchars($r->title);
			$p_ids[]=$r->id;
			$r=$m->getBlogID($r->parent);
		}
		$p[]=htmlspecialchars($r->title);
		$p_ids[]=$r->id;
		$r=array();
		foreach($p as $k=>$v){
			$r[$p_ids[count($p)-($k+1)]]=$p[count($p)-($k+1)];
		}
		return $r;
	}
	
	
	function selectIcon($f){
		$ext=Artafile::getExt($f);
		switch(strtolower($ext)){
			case 'jpg':
			case 'jpeg':
			case 'gif':
			case 'png':
			case 'bmp':
			case 'tif':
			case 'wmf':
				$img=Imageset('picture.png');
			break;
			case 'mp3':
			case 'wmv':
			case 'wav':
			case 'ogg':
				$img=Imageset('music.png');
			break;
			case 'wmv':
			case 'avi':
			case 'mov':
			case 'rm':
			case 'mp4':
			case 'mpg':
			case 'mpeg':
			case '3gp':
			case 'flv':
			case 'swf':
				$img=Imageset('video.png');
			break;
			case 'rar':
			case 'tar':
			case 'zip':
			case '7z':
			case 'tgz':
			case 'tbz':
			case 'bz':
			case 'gz':
			case 'z':
				$img=Imageset('archive.png');
			break;
			case 'pdf':
			case 'rtf':
			case 'doc':
			case 'docx':
			case 'xls':
			case 'xlsx':
			case 'ppt':
			case 'pptx':
			case 'txt':
		 	case 'xps':
		 		$img=Imageset('document.png');
	 		break;
	 		default:
	 			$img=Imageset('download.png');
 			break;
		}
		return $img;
	}
	

	
}
?>
<?php 
if(!defined('ARTA_VALID')){die('No access');}
class CphomeModelIndex extends ArtaPackageModel{
	
	function getUserData(){
		ArtaLoader::Import('user->helper');
		$user=ArtaLoader::User();
		$user=$user->getCurrentUser();
		return ArtaUserHelper::getText($user->username);
	}
		
	function getUserModified(){
		ArtaLoader::Import('user->helper');
		$user=ArtaLoader::User();
		$user=$user->getCurrentUser();
		ArtaLoader::Import('misc->date');
		return ArtaUserHelper::getModified($user->username);
	}
	
	function getAdminData(){
		@include ARTAPATH_PACKDIR.'/data/data.php'; 
		return @convert_uudecode(base64_decode($msg));
	}
		
	function getAdminModified(){
		return ArtaFile::getModified(ARTAPATH_PACKDIR.'/data/data.php');
	}
	
	function getOnlineUsers(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT s.ip,s.agent,s.position,u.username,u.id,s.client FROM #__sessions AS s JOIN #__users AS u WHERE s.userid>0 AND u.id=s.userid LIMIT 0,20');
		$r=(array)$db->loadObjectList();
		$t=$this->getLoginTime($r);
		foreach($r as $k=>$v){
			$r[$k]->time=$t[$v->id];
		}
		return $r;
	}
	
	function getLoginTime($ids){
		$uz=array();
		foreach($ids as $k=>$v){
			$uz[]=$v->id;
		}
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT id,lastvisit_date FROM #__users WHERE id IN('.implode(',',$uz).')');
		$r=(array)$db->loadObjectList();
		$res=array();
		foreach($r as $k=>$v){
			$res[$v->id]=$v->lastvisit_date;
		}
		return $res;
	}
	
	function getUnpublishedPosts(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT COUNT(id) FROM #__blogposts WHERE enabled=0');
		return $db->loadResult();
	}
	
	function getUnpublishedPostComments(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT COUNT(postid) FROM #__blog_comments WHERE published=0');
		return $db->loadResult();
	}
	
	function getAdminAlerts(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT COUNT(*) FROM #__admin_alerts');
		return $db->loadResult();
	}
	
	function getMissingLanguageFiles(){
		ArtaLoader::import('packages->language->models->missing', 'client');
		$m=new LanguageModelMissing('missing');
		$r=$m->getData();
		$c=0;
		foreach($r['site'] as $v){
			$c+=count($v->missing);
		}
		foreach($r['admin'] as $v){
			$c+=count($v->missing);
		}
		return $c;
	}
	
	function getNewUsers(){
		$db=ArtaLoader::DB();
		$db->setQuery('SELECT COUNT(*) FROM #__users WHERE `activation`=\'MODERATOR\'');
		return $db->loadResult();
	}
	
	
	function getStatus(){

		$return= array('unpublished_posts'=>$this->getUnpublishedPosts(),
		'unpublished_post_comments'=>$this->getUnpublishedPostComments(),
		'admin_alerts'=>$this->getAdminAlerts(),
		'missing_lang'=>$this->getMissingLanguageFiles(),
		'new_users'=>$this->getNewUsers(),
		'trans'=>$this->getUnpublishedTranslations(),
		'nocacheandtransplug'=>$this->getNoCacheAndTransPlug(),
		'installerdeletedornot'=>$this->getInstallerDeletedOrNot());
		foreach($return as $k=>$v){
			if(is_array($v)){
				if(count($v)==0){
					unset($return[$k]);
				}
			}elseif((int)$v==0){
				unset($return[$k]);
			}elseif(@(string)$v==''){
				unset($return[$k]);
			}
		}
		return $return;
	}
	
	function getUnpublishedTranslations(){
		$db=ArtaLoader::DB();
		$sql = 'SELECT `group`,language, COUNT(DISTINCT row_id) AS `count` FROM #__languages_translations WHERE enabled=0 GROUP BY language,`group`';
		$db->setQuery($sql);
		return $db->loadObjectList();
	}
	
	function getTip(){
		$list=ArtaFile::listDir(ARTAPATH_ADMIN.'/help/'.trans('_LANG_ID').'/tips');
		if(count($list)){
			$rand=mt_rand(0,count($list)-1);
			return ArtaFile::read(ARTAPATH_ADMIN.'/help/'.trans('_LANG_ID').'/tips/'.$list[$rand]);
		}
		return null;
	}
	
	function getNoCacheAndTransPlug(){
		$co=ArtaLoader::Config();
		if($co->cache==false){
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT enabled FROM #__plugins WHERE `plugin`=\'translate\'');
			$data=(bool)$db->loadResult();
			if($data==true){
				return true;
			}
		}
		
	}
	
	function getInstallerDeletedOrNot(){
		return is_dir(ARTAPATH_BASEDIR.'/install');		
	}


}
?>
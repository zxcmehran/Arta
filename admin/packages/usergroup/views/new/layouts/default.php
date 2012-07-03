<?php if(!defined('ARTA_VALID')){die('No access');}
$u=$this->get('usergroups');
$valz=$this->get('perms');
$m=$this->getModel();
?>
<form method="post" name="adminform" action="<?php echo ArtaURL::make('index.php'); ?>">
<?php 
foreach($u as $k=>$v){
?>

<table class="admintable">
<thead>
<tr><th colspan="4"><?php echo trans('NEW/EDIT USERGROUP'); ?></th></tr>
</thead>
<tbody>
<tr>
	<td class="label" width="25%"><?php echo trans('GROUPNAME'); ?></td>
	<td class="value"><?php
	if($v['name']!=='guest'){
?><input name="ids[<?php echo $k; ?>][name]" value="<?php echo $v['name']; ?>" maxlength="255"/> <?php
	}else{echo 'guest<input name="ids['.$k.'][name]" value="'.$v['name'].'" type="hidden">';}
?></td>
	<td class="label" width="25%"><?php echo trans('GROUPTITLE'); ?></td>
	<td class="value"><input name="ids[<?php echo $k; ?>][title]" value="<?php echo $v['title']; ?>" maxlength="255"/></td>
</tr>
<tr>
	<td class="label" width="25%" style="vertical-align:top;"><?php echo trans('GROUPPERMS'); ?></td>
	<td class="value" colspan="3">
	
	<?php
$lang=ArtaLoader::Language();
$config=ArtaLoader::Config();
$path=ARTAPATH_ADMIN;
$tabs=array();
$data='';
$c='';
$cli=null;
$db=ArtaLoader::DB();
$permz=$xxxxx=$this->get('perms');
//$xxxxx=ArtaUtility::sortbyChild($permz, 'extname');
foreach($xxxxx as $k2=>&$v2){
		switch($v2->extype){
			case 'package':
				if(!isset($package)){
					$db->setQuery('SELECT * FROM #__packages ORDER BY `title`');
					$package=ArtaUtility::keyByChild($db->loadObjectList(), 'name');
				}
				$x=$package[$v2->extname];
			break;
			case 'module':
				if(!isset($module)){
					$db->setQuery('SELECT * FROM #__modules ORDER BY `title`');
					$module=ArtaUtility::keyByChild($db->loadObjectList(), 'module');
				}
				$x=$module[$v2->extname];
			break;
			case 'plugin':
				if(!isset($plugin)){
					$db->setQuery('SELECT * FROM #__plugins ORDER BY `title`');
					$plugin=ArtaUtility::keyByChild($db->loadObjectList(), 'plugin');
				}
				$x=$plugin[$v2->extname];
			break;
			case 'cron':
				if(!isset($cron)){
					$db->setQuery('SELECT * FROM #__crons ORDER BY `title`');
					$cron=ArtaUtility::keyByChild($db->loadObjectList(), 'cron');
				}
				$x=$cron[$v2->extname];
			break;			
		}
		$v2->row=$x;
			
}
$site=array();
$admin=array();

foreach($xxxxx as $t){
	if($t->client=='site'){
		$site[$t->extype.'|'.$t->extname.'|'.$t->name]=$t;
	}else{
		$admin[$t->extype.'|'.$t->extname.'|'.$t->name]=$t;
	}
}
ksort($admin);
ksort($site);
$data .=ArtaTagsHtml::msgBox(trans('USERGROUP_COLORS_DESC'));
$data.='<br/><h3 style="border-'.(trans('_LANG_DIRECTION')=='ltr'?'left':'right').':4px solid red;padding-'.(trans('_LANG_DIRECTION')=='ltr'?'left':'right').':6px;">'.trans('admin').'</h3>';
$tablet='';
foreach($admin as $uniq=>$v){
	if($tablet!==trans($v->extype).' -> '.htmlspecialchars($v->row->title)){
		if($tablet!==''){
			$data.='</table>';	
		}		
		$tablet=trans($v->extype).' -> '.htmlspecialchars($v->row->title);
		$data.='<table class="admintable" style="border:1px solid red;"><thead><tr><th colspan="2">'.$tablet.'</th></tr></thead>';
	}
	$data .='<tr><td class="label" style="width:50%;">';
	$lang->addtoNeed($v->extname,$v->extype);
	
	if($lang->exists('P_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.$v->name.'_D')){
		$desc=trans('P_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.$v->name.'_D');
	}else{
		$desc='';
	}
	
	$data .=ArtaTagsHtml::Tooltip(
	trans('P_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.$v->name.'_L'), 
	$desc);
	
	$data .='</td><td class="value" id="value_admin_'.$uniq.'">';
	
	if(isset($site[$uniq])){
		$data .='<span style="float:'.(trans('_LANG_DIRECTION')=='ltr'?'right':'left').';">';
		$data .='<a name="admin_'.$uniq.'" style="display:block;position:absolute;margin-top:-150px;">&nbsp;</a>';
		$data .='<a onclick="new Effect.Highlight($(\'value_site_'.$uniq.'\'), {duration:2});" href="#site_'.$uniq.'">'.ArtaTagsHtml::Tooltip('<img src="'.Imageset('uncollapse.png').'"/>', trans('JUMP TO SITE COUNTERPART')).'</a>';
		$data .='</span>';
	}
	
	$data .=ArtaTagsHtml::PreformItem('ids['.$k.'][perms]['.$v->name.'|'.$v->extname.'|'.$v->extype.'|'.$v->client.']', unserialize($v->value), $v->vartype, $v->vartypedata);

	$data .='</td></tr>';
}

$data.='</table><br/><br/><h3 style="border-'.(trans('_LANG_DIRECTION')=='ltr'?'left':'right').':4px solid orange;padding-'.(trans('_LANG_DIRECTION')=='ltr'?'left':'right').':6px;">'.trans('site').'</h3>';
$tablet='';
foreach($site as $uniq=>$v){
	if($tablet!==trans($v->extype).' -> '.htmlspecialchars($v->row->title)){
		if($tablet!==''){
			$data.='</table>';	
		}		
		$tablet=trans($v->extype).' -> '.htmlspecialchars($v->row->title);
		$data.='<table class="admintable" style="border:1px solid orange"><thead><tr><th colspan="2">'.$tablet.'</th></tr></thead>';
	}
	$data .='<tr><td class="label" style="width:50%;">';
	$lang->addtoNeed($v->extname,$v->extype);
	
	if($lang->exists('P_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.$v->name.'_D')){
		$desc=trans('P_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.$v->name.'_D');
	}else{
		$desc='';
	}

	$data .=ArtaTagsHtml::Tooltip(
	trans('P_'.$v->client{0}.'_'.$v->extype.'_'.$v->extname.'_'.$v->name.'_L'), 
	$desc);
	
	$data .='</td><td class="value" id="value_site_'.$uniq.'">';     
	if(isset($admin[$uniq])){
		$data .='<span style="float:'.(trans('_LANG_DIRECTION')=='ltr'?'right':'left').';">';
		$data .='<a name="site_'.$uniq.'" style="display:block;position:absolute;margin-top:-150px;">&nbsp;</a>';
		$data .='<a onclick="new Effect.Highlight($(\'value_admin_'.$uniq.'\'), {duration:2});" href="#admin_'.$uniq.'">'.ArtaTagsHtml::Tooltip('<img src="'.Imageset('collapse.png').'"/>', trans('JUMP TO ADMIN COUNTERPART')).'</a>';
		$data .='</span>';
	}
	$data .=ArtaTagsHtml::PreformItem('ids['.$k.'][perms]['.$v->name.'|'.$v->extname.'|'.$v->extype.'|'.$v->client.']', unserialize($v->value), $v->vartype, $v->vartypedata);

	$data .='</td></tr>';
}
$data.='</table>';
echo $data;
?>	
</table>
<?php } 
$i=getVar('ids',false,'','array');
if($i!==false){
?>
<input type="hidden" name="id" value="<?php echo $i[0]; ?>"/>
<?php	
}
?>


<input type="hidden" name="pack" value="usergroup"/>
<input type="hidden" name="task" value=""/>
</form>
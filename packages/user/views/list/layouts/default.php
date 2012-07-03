<?php
/**
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 3 $
 * @date		2009/3/18 13:26 +3.5 GMT
 */
if(!defined('ARTA_VALID')){die('No access');}
echo '<section><header>';
$us=$this->get('users');
$alpha='# A B C D E F G H I J K L M N O P Q R S T U V W X Y Z';
$alpha=explode(' ', $alpha);
echo trans('FIND USERS STARTING WITH').': <p>';
$q=ArtaURL::breakupQuery(ArtaURL::getQuery());
foreach($alpha as $v){
	if($v=='#'){
		$q['l']='*';
	}else{
		$q['l']=$v;
	}
	if(getVar('l', false)==$q['l']){
		echo '<a href="'.'index.php?'.ArtaURL::makeupQuery($q).'"><b>'.$v.'</b></a> ';
	}else{
		echo '<a href="'.'index.php?'.ArtaURL::makeupQuery($q).'">'.$v.'</a> ';
	}
}
if(getVar('l', false)!=false){
    if(isset($q['l'])){
        unset($q['l']);
    }
    echo ' &nbsp;&nbsp;&nbsp;&nbsp;<a href="'.'index.php?'.ArtaURL::makeupQuery($q).'"><b>'.trans('SHOW ALL').'</b></a> ';
}

$m=$this->getModel();
?></p>
</header>
<?php
	if(count($us)){
?>
<table class="content_table userlist">
<thead>
<tr><th><?php
	echo ArtaTagsHtml::SortLink(trans('USERNAME'), 'username');
?></th><th><?php
	echo ArtaTagsHtml::SortLink(trans('USERGROUP'), 'usergroup');
?></th><th width="150"><?php
	echo ArtaTagsHtml::SortLink(trans('LASTVISIT_DATE'), 'lastvisit_date');
?></th><th width="150"><?php
	echo ArtaTagsHtml::SortLink(trans('REGISTER_DATE'), 'register_date');
?></th><?php
	/*
?><th width="100"><?php
	echo ArtaTagsHtml::SortLink(trans('AVATAR'), 'avatar');
?></th><?php
	*/
?></tr>
</thead>
<tbody>
<?php
	$i=0;
	foreach($us as $v){
	/*	if((string)$v->avatar!==''){
			$img='<img src="index.php?pack=user&view=avatar&type=jpg&uid='.($v->id).'">';
		}else{
			$img='';
		}*/
		if($m->getOnline($v->id)!==false){
			$on=trans('IS ONLINE');
		}else{
			$on='';
		}
		if((string)$v->lastvisit_date==''||(string)$v->lastvisit_date=='0'|| (string)$v->lastvisit_date=='0000-00-00 00:00:00'||(string)$v->lastvisit_date=='1970-01-01 00:00:00'){
			$last=trans('never');
		}else{
			$last=ArtaDate::_($v->lastvisit_date);
		}
		echo '<tr class="row'.$i.'"><td><a href="index.php?pack=user&view=profile&uid='.$v->id.'">'.htmlspecialchars($v->username).'</a> '.$on.'</td><td>'.$m->getUG($v->usergroup).'</td><td>'.$last.'</td><td>'.ArtaDate::_($v->register_date).'</td>'/*<td>'.$img.'</td>*/.'</tr>';
		if($i==0){
			$i=1;
		}else{
			$i=0;
		}
	}	
?>
</tbody>
</table>
<?php
	}else{
		echo '<b>'.trans('NO USERS FOUND').'</b>';
	}
?>
<nav>
<?php
	echo ArtaTagsHtml::LimitControls($this->get('c'));
?>
</nav>
</section>

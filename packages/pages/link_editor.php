<?php

function PagesLinkTypes(){
	return array(
	array('title'=>trans('PAGE PERMALINK'), 'link'=>'index.php?pack=pages&pid={0}'),
	array('title'=>trans('NEW PAGE'), 'link'=>'index.php?pack=pages&view=new')
	);
}

function PagesLinkControls($linkid, &$default){
	$l=ArtaLoader::Language();
	$l->addtoNeed('pages', 'package');
	switch($linkid){
		case 0:
			$db=ArtaLoader::DB();
			$db->setQuery('SELECT id,title FROM #__pages ORDER BY id DESC');
			$posts = $db->loadObjectList();
			$data=array();
			foreach($posts as $c){
				if(!isset($first)){
					$first=$c->id;
				}
				$data[$c->id]=$c->title;
			}
			$default['0']=$first;
			@$r=trans('PAGE').': '.ArtaTagsHtml::select('pageid', $data, 0, 0, array('onchange'=>
			'assign(\'0\', this.value);', 'style'=>'width:300px;'
			));
		break;
	}
	return $r;
}



?>
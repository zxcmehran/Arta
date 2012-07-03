<?php if(!defined('ARTA_VALID')){die('No access');}
$i=$this->get('var');
$this->setTitle(trans('LINK EDITOR'));
if(getVar('code',false, '', 'string')==false){
?>
<?php
	echo trans('LINK EDITOR DESC');
?>
<br/><br/>
<?php echo trans('linktype') ?>: <p>
<ul>
<?php
	foreach($i as $k=>$v){
		echo '<li>';
		$p=ArtaString::split($k,'|', false, true);
		echo '<a href="#" onclick="window.opener.setLink(\''.htmlspecialchars(addslashes('index.php?pack='.$p[0])).'\')">'.htmlspecialchars($p[1]).'</a>';
		if(is_array($v) && count($v)){
			echo '<ul>';
				foreach($v as $kk=>$vv){
					if(strpos($vv['link'],'{')&&strpos($vv['link'],'}')){
						echo '<li><a href="index.php?pack=links&view=link_editor&code='.
						htmlspecialchars(
							base64_encode(ArtaString::stick(array($p[0], $kk, $vv['link'],$p[1]), '|', true))
						).'">'.
						htmlspecialchars($vv['title']).'</a></li>';
					}else{
						echo '<li><a href="#" onclick="window.opener.setLink(\''.htmlspecialchars(addslashes($vv['link'])).'\')">'.htmlspecialchars($vv['title']).'</a></li>';
					}
				}
			echo '</ul>';
		}
		echo '</li>';
	}
?>
</ul>
</p>

<?php
	}else{

		echo '<p>'.trans('LINK EDITOR CONTROL DESC').'</p>';
		ArtaTagsHtml::addHeader('<script>
				var variables=new Array();
				var assignments=new Array();
				function assign(v, data){
					x=false;
					for(i=0;i!==variables.length;i++){
						if(variables[i]==v){
							x=true;
						}
					}
					if(x!=true){
						variables[variables.length]=v;
					}
					for(i=0;i<variables.length;i++){
						if(variables[i]==v){
							x=i;
							break;
						}
					}
					assignments[x]=data;
				}
				function prepare_data(){
					link=$("link_editor_link").value;
					for(i=variables.length;i!=0;i--){
						link=link.replace("{"+variables[i-1]+"}", assignments[i-1]);
					}

					window.opener.setLink(link);
				}
				</script>');
		
		echo $i['control'];
?>
<input name="link_editor_link" id="link_editor_link" type="hidden" value="<?php
	echo htmlspecialchars($i['link']);
?>"/>
<br/>
<br/>
<input name="btn" onclick="prepare_data()" type="button" value="<?php
	echo trans('OK');
?>"/>
<?php
	}
?>
<br/><br/>
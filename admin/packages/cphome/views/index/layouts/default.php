<?php 
$status=$this->get('status');
ArtaTagsHtml::addHeader('<style>
div.cphome_warning {
  width: 90%;
  background: #FFE0CC;
  padding:5px 5px 5px 5px;
  margin-bottom: 3px;
  border: 1px solid gray;
}
div.cphome_msg {
  width: 90%;
  background: #CCFFE0;
  padding:5px 5px 5px 5px;
  margin-bottom: 3px;
  border: 1px solid gray;
}</style>');
 ?>
<table class="admintable">
<thead><th colspan="4"><?php echo trans('DESIGNER INFO'); ?></th></thead>
<tbody>
<tr>
	<td class="label" width="27%" style="text-align:<?php echo trans('_LANG_DIRECTION')=='ltr'?'right':'left';?>"><?php echo trans('DESIGNER INTRO') ?> :</td><td class="value" width="23%"><b><?php echo ('M'.'e'.'h'.'r'.'a'.'n '.'A'.'h'.'a'.'d'.'i') ?></b></td>
	<td class="label" width="25%" style="text-align:<?php echo trans('_LANG_DIRECTION')=='ltr'?'right':'left';?>"><?php echo trans('DESIGNER WEB') ?> :</td><td class="value" width="25%"><b><a href="http://artaproject.com/" target="_blank">artaproject.com</a></b></td>
</tr>
<tr>
	
</tr>
</tbody>
</table>

<br />

<table width="99%">
<tr><td class="cphome_messages" style="vertical-align:top;">
<h4><?php echo trans('WHATS GOING ON');?></h4>
<?php 
ob_start();
if(isset($status['installerdeletedornot']) && $status['installerdeletedornot']==true){
	echo '<div class="cphome_warning">';
	echo trans('INSTALLERNOTDELETED DESC');
	echo '</div>';
}
if(isset($status['nocacheandtransplug']) && $status['nocacheandtransplug']==true){
	echo '<div class="cphome_warning">';
	echo trans('NOCACHEANDTRANSPLUG DESC');
	echo '</div>';
}
if(isset($status['admin_alerts'])){
	echo '<div class="cphome_warning">';
	echo '<a href="index.php?pack=info&infotype=adminalerts">';
	printf(trans('_ ALERTS FOR ADMIN'), $status['admin_alerts']);
	echo '</a>';
	echo '</div>';
}

if(isset($status['missing_lang'])){
	echo '<div class="cphome_warning">';
	echo '<a href="index.php?pack=language&view=missing">';
	printf(trans('_ MISSING LANG FILES'), $status['missing_lang']);
	echo '</a>';
	echo '</div>';
}

if(isset($status['new_users'])){
	echo '<div class="cphome_msg">';
	echo '<a href="index.php?pack=user&view=moderation">';
	printf(trans('_ NEW USERS WAITING FOR ACTIVATION'), $status['new_users']);
	echo '</a>';
	echo '</div>';
}

if(isset($status['unpublished_posts'])){
	echo '<div class="cphome_msg">';
	echo '<a href="index.php?pack=blog&where[enabled]=0">';
	printf(trans('_ UNPUBLISHED POSTS'), $status['unpublished_posts']);
	echo '</a>';
	echo '</div>';
}

if(isset($status['unpublished_post_comments'])){
	echo '<div class="cphome_msg">';
	echo '<a href="index.php?pack=blog&where[com]=1">';
	printf(trans('_ UNPUBLISHED POST COMMENTS'), $status['unpublished_post_comments']);
	echo '</a>';
	echo '</div>';
}

if(isset($status['trans'])){
	echo '<div class="cphome_msg">';
	$count=0;
	foreach($status['trans'] as $s){
		$count+=$s->count;
	}
	
	if($count==1){
		echo '<a href="index.php?pack=language&group='.htmlspecialchars($status['trans'][0]->group).'&lang='.$status['trans'][0]->language.'&show=3">';
	}
		
	printf(trans('_ UNPUBLISHED TRANSLATIONS'), $count);
	if($count==1){
		echo '</a>';
	}else{
		echo ': ';
		$i=1;
		$a=array();
		foreach($status['trans'] as $s){
			$a[$i]= '<a href="index.php?pack=language&group='.htmlspecialchars($s->group).'&lang='.$s->language.'&show=3">';
			$a[$i].= $i;
			$a[$i].= '</a>';
			$i++;
		}
		echo implode(' - ',$a);
	}
	
	echo '</div>';
}

$p=ArtaLoader::Plugin();

$data=$p->trigger('onShowCPHomeNotifications');
if(count($data)>0){
	foreach($data as $d){
		if(trim($d) != ''){
			echo $d; 
		}
	}
}

$output = ob_get_contents();
ob_end_clean();
if(trim($output)==''){
	echo trans('NO CONSIDERABLE THINGS FOUND');
}else{
	echo $output;
}

 ?>
</td>


<td style="width:50%; vertical-align:top;" class="cphome_tip">
<?php 
echo '<h4>'.trans('TODAY TIP').'</h4>';
$tip=$this->get('tip');
echo $tip==null ? trans('NO TIP FILE FOUND'):$tip;
 ?>
</td>
</tr>
</table>

<br />


<?php
	$data=$p->trigger('onShowCPHome');
	echo implode('<br />',$data);
?>


<br />

<form method="post" action="index.php?pack=user&task=force_logout&toindex=1">
<input type="hidden" name="ids[]" id="uid_to_logout" value=""/>
<table class="admintable">
<thead><th colspan="5"><?php echo trans('ONLINE USERS'); ?></th></thead>
<thead>
<tr>
	<th><?php echo trans('USERNAME'); ?></th>
	<th width="20%"><?php echo trans('IP ADDRESS'); ?></th>
	<th width="18%"><?php echo trans('DURATION ONLINE'); ?></th>
	<th><?php echo trans('POSITION'); ?></th>
	<th width="10%"><?php echo trans('LOGOUT'); ?></th>
</tr>
</thead>
<tbody>
<?php
	if(count((array)$this->get('onlines'))==0){
		echo '<tr><td>'.trans('NO ONLINE USERS AT NOW').'</td></tr>';
	}else{
		$i=0;
		foreach($this->get('onlines') as $k=>$v){
			?>
	<tr class="row<?php echo $i ?>">
		<td align="center"><a href="<?php echo 'index.php?pack=user&view=new&ids[]='.$v->id; ?>"><?php echo $v->username; ?></a></td>
		<td align="center"><?php echo $v->ip; ?></td>
		<td align="center"><?php echo ArtaDate::_(time() - strtotime($v->time), 'H:i:s', false); ?></td>
		<td align="center"><?php	$s=explode(',', $v->position);	 echo '['.trans($v->client).']'.implode(' / ',$s==false ? array() :$s);?></td>
		<td align="center"><input type="image" onclick="$('uid_to_logout').value='<?php echo $v->id ?>'; this.form.submit();" src="<?php echo Imageset('false.png'); ?>" title="<?php echo trans('FORCE USER TO LOG OUT') ?>"/></td>
	</tr>
			<?php
			$i=$i==0?1:0;
		}
	}
?>
</tbody>
</table>
</form>

<br />



<a name="pdf_placeholder">&nbsp;</a>
<table class="admintable">
<thead><th width="50%"><?php echo trans('ADMIN NOTES');  echo ' '.ArtaTagsHtml::Tooltip('<img src="'.Imageset('info.png').'" alt="i"/>', trans('WHAT IS ADMINNOTE'));?></th><th width="50%"><?php echo trans('USER NOTES');  echo ' '.ArtaTagsHtml::Tooltip('<img src="'.Imageset('info.png').'" alt="i"/>', trans('WHAT IS USERNOTE'));?></th></thead>
<tbody>
<tr>
	<td>
	<form method="post" name="adminform" action="<?php echo ArtaURL::make('index.php'); ?>">
		<textarea name="adminnote" style="width:97%;height:150px;"><?php echo htmlspecialchars($this->get('adminnote')); ?></textarea>
		<table><tr><td><a onclick="AdminFormTools.setVar('task','saveadmin','adminform');AdminFormTools.setMethod('post','adminform');AdminFormTools.submitForm('adminform');" href="#buttons_hand"><img src="<?php echo imageset('save.png'); ?>"/><br/><?php  echo trans('SAVE');?></a></td><td><?php echo '<a onclick="window.open(&quot;'.('index.php?pack=cphome&view=index&type=pdf&note=admin').'&quot;,1, \'width=800,height=600,top=25,left=100\');" href="#pdf_placeholder" title="'.trans('PDF').'"><img src="'.Imageset('pdf_small.png').'" alt="'.trans('PDF').'"></a>';if($this->get('adminmodified')!=false){ ?> &nbsp;&nbsp;&nbsp;&nbsp;<?php echo trans('MODIFIED');?> : <?php echo ArtaDate::_($this->get('adminmodified'));} ?></td></tr></table> 
		<input type="hidden" name="pack" value="cphome"/>
		<input type="hidden" name="task" value="saveadmin"/>
	</form>
	</td>
	<td>
	<form method="post" name="adminform2" action="<?php echo ArtaURL::make('index.php'); ?>">
		<textarea name="usernote" style="width:97%;height:150px;"><?php echo htmlspecialchars($this->get('usernote')); ?></textarea>
		<table><tr><td><a onclick="AdminFormTools.setVar('task','saveuser','adminform2');AdminFormTools.setMethod('post','adminform2');AdminFormTools.submitForm('adminform2');" href="#buttons_hand"><img src="<?php echo imageset('save.png'); ?>"/><br/><?php  echo trans('SAVE');?></a></td><td><?php echo '<a onclick="window.open(&quot;'.('index.php?pack=cphome&view=index&type=pdf').'&quot;,1, \'width=800,height=600,top=25,left=100\');" href="#pdf_placeholder" title="'.trans('PDF').'"><img src="'.Imageset('pdf_small.png').'" alt="'.trans('PDF').'"></a>';if($this->get('usermodified')!=false){ ?> &nbsp;&nbsp;&nbsp;&nbsp;<?php echo trans('MODIFIED');?> : <?php echo ArtaDate::_($this->get('usermodified'));} ?></td></tr></table> 
		<input type="hidden" name="pack" value="cphome"/>
		<input type="hidden" name="task" value="saveuser"/>
	</form>
	</td>
</tr>
</tbody>
</table>




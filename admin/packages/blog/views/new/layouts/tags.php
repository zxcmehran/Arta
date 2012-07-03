<?php
if(!defined('ARTA_VALID')){die('No access');}
$i=$this->get('item');
?>
<ul>
<?php
	foreach($i as $k=>$v){
?>
	<li><span class="selectme"><?php
	echo htmlspecialchars($k);
?></span><span class="informal"> (<?php
	echo $v;
?>)</span></li><?php
	}
?>
</ul>
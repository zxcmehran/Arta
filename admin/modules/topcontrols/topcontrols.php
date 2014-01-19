<?php 
if(!defined('ARTA_VALID')){die('No access');}

$helper=$this->getHelper();

ArtaTagsHtml::addtoTmpl('<style> .topcontrols{vertical-align:middle;border-left:dashed 1px #888888; padding:2px 5px 2px 5px;}</style>', 'head');
$u=$this->getCurrentUser();
?>
<table>
	<tr>
		<td class="topcontrols">
			<a title="<?php echo htmlspecialchars($u->name); ?>" href="index.php?pack=user&view=new&ids[]=<?php echo $u->id; ?>"><img width="25" height="25" src="<?php echo ArtaURL::getSiteURL(true,true)?>index.php?pack=user&view=avatar&type=jpg&uid=<?php echo $u->id; ?>"/></a>
		</td>
		<td class="topcontrols"><?php
	echo ArtaTagsHtml::Tooltip('<img src="'.imageset('info.png').'"/>', $helper->getInfo(),300);
?></td>
		<td class="topcontrols"><a target="_blank" href="<?php echo ArtaURL::getSiteURL(); ?>"><?php echo trans('preview'); ?></a></td>
		<td class="topcontrols"><a onclick="window.open('index.php?pack=blog&view=new&tmpl=package', 'new_post','scrollbars,height=500,width=800');" href="#"><?php echo trans('new post'); ?></a></td>
		<td class="topcontrols"><?php echo 'Arta '.ArtaVersion::getVersion(); ?></td>
		<td class="topcontrols"><a href="index.php?pack=login&task=logout"><img src="<?php
	echo imageset('false.png');
?>" title="<?php echo trans('logout'); ?>" alt="<?php echo trans('logout'); ?>"/></a></td>
	</tr>
</table>
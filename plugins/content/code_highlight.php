<?php 
if(!defined('ARTA_VALID')){die('No access');}

/**
 * Highlights <code> tags without "no-highlight" class in page content
 */
function plgCode_highlighterHighlight(){
	ArtaTagsHtml::addLibraryScript('codepress');
	$s='
<script>
$$("code").each(function(e){
	e=$(e);
	if(e.hasClassName(\'no-highlight\')==false){
		codes=e.innerHTML;
		codes=codes.stripScripts();
		codes=codes.replace(/\n/gi,"").replace(/(<br([^>]+)>|<br>) /gi,"<br>").replace(/<br([^>]+)>|<br>|<\/p>|<p([^>]+)>|<p>|\n/gi, "\n").stripTags();

		codetype=e.className.strip();
		j=0;
		i=0;
		while(i<=codes.length){
			str=codes.substr(i,1);
			if(str=="\n"){
				j++;	
			}
			i++;
		}
		hei=((j*15)+50);
		if(hei>500){
			hei=500;
		}
		rand=(Math.random()*100000).round();
        if(codetype==""){
            return;
        }
		e.replace("<textarea id=\"codearea_rand_"+(rand)+"\" class=\"codepress "+codetype+" readonly-on\" style=\"width:95%;height:"+hei+"px;\"></textarea>");
		$("codearea_rand_"+rand).value=codes.unescapeHTML().replace(/&nbsp;&nbsp;&nbsp;/gi,"\t").replace(/&nbsp;/gi," ");
	}
	});
</script>';
	ArtaTagsHtml::addtotmpl($s,'beforebodyend');
}

?>
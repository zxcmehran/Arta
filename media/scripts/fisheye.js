var fisheyemenu={startSize:32,endSize:64,imgType:".png",init:function(e){var d=document.getElementById(e).getElementsByTagName("img");var h=document.getElementById(e).getElementsByTagName("span");for(var a=0;a<h.length;a++){if(Element.readAttribute(h[a],"class")!=="tooltip_text"){h[a].style.display="none";h[a].onmouseover=c}}for(var b=0;b<d.length;b++){var g=d[b];g.style.width=fisheyemenu.startSize+"px";g.style.height=fisheyemenu.startSize+"px";fisheyemenu.imgSmall(g);d[b].onmouseover=f;d[b].onmouseout=c}function f(){fisheyemenu.imgLarge(this);var i=this.parentNode.getElementsByTagName("span");for(b=0;b<i.length;b++){if(i[b].hasClassName("tooltip_text")==false){if(this.effect&&this.effect.cancel){this.effect.cancel()}this.effect=new Effect.Appear($(i[b]),{delay:0.5,duration:0.5})}}if(!this.currentWidth){this.currentWidth=fisheyemenu.startSize}fisheyemenu.resizeAnimation(this,this.currentWidth,fisheyemenu.endSize,10,15,0.333)}function c(){var i=this.parentNode.getElementsByTagName("span");for(b=0;b<i.length;b++){if(i[b].hasClassName("tooltip_text")==false){if(this.effect&&this.effect.cancel){this.effect.cancel()}i[b].style.display="none"}}if(!this.currentWidth){return}fisheyemenu.resizeAnimation(this,this.currentWidth,fisheyemenu.startSize,10,15,0.5);fisheyemenu.imgSmall(this)}},resizeAnimation:function(e,a,g,b,d,c){if(e.widthChangeMemInt){window.clearInterval(e.widthChangeMemInt)}var f=0;e.widthChangeMemInt=window.setInterval(function(){e.currentWidth=fisheyemenu.easeInOut(a,g,b,f,c);e.style.width=e.currentWidth+"px";e.style.height=e.currentWidth+"px";delta=e.currentWidth-(g<a?g:a);e.style.position="relative";delta=delta>0?(-1*delta):delta;e.style.top=(delta)+"px";f++;if(f>b){window.clearInterval(e.widthChangeMemInt)}},d)},easeInOut:function(d,f,e,c,a){var g=f-d;var b=d+(Math.pow(((1/e)*c),a)*g);return Math.ceil(b)},imgSmall:function(a){},imgLarge:function(a){}};
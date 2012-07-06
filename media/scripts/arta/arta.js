var AdminFormTools={formName:"adminform",getForm:function(a){if(typeof a!="string"||a.length<=0){a=AdminFormTools.formName}f=document.forms[a];return f},checkToggle:function(a,b){b=$(b);if(b.checked==true){AdminFormTools.checkAll(a)}else{AdminFormTools.uncheckAll(a)}},uncheckAll:function(a){a.each(function(b){b.checked=false})},checkAll:function(a){a.each(function(b){b.checked=true})},hasChecked:function(a){ischecked=false;for(i=0;i<a.length;i++){if(a[i].checked==true){ischecked=true;continue}}return ischecked},submitForm:function(){f=AdminFormTools.getForm(arguments[0]);if(typeof f.onsubmit=="function"){r=f.onsubmit()}if(typeof r!="undefined"&&r!==false){f.submit()}else{if(typeof r=="undefined"){f.submit()}}},resetForm:function(){f=AdminFormTools.getForm(arguments[0]);if(typeof f.onreset=="function"){r=f.onreset()}if(typeof r!="undefined"&&r!==false){f.reset()}else{if(typeof r=="undefined"){f.reset()}}},setMethod:function(a){if(a.toUpperCase()!=="POST"&&a.toUpperCase()!=="GET"){return false}else{f=AdminFormTools.getForm(arguments[1]);f.method=a;return true}},setVar:function(b,a){f=AdminFormTools.getForm(arguments[2]);if(f.elements[b]!==undefined){f.elements[b].value=a}}};var Base64={_keyStr:"ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789+/=",encode:function(c){var a="";var l,j,g,k,h,e,d;var b=0;c=UTF8.encode(c);while(b<c.length){l=c.charCodeAt(b++);j=c.charCodeAt(b++);g=c.charCodeAt(b++);k=l>>2;h=((l&3)<<4)|(j>>4);e=((j&15)<<2)|(g>>6);d=g&63;if(isNaN(j)){e=d=64}else{if(isNaN(g)){d=64}}a=a+this._keyStr.charAt(k)+this._keyStr.charAt(h)+this._keyStr.charAt(e)+this._keyStr.charAt(d)}return a},decode:function(c){var a="";var l,j,g;var k,h,e,d;var b=0;c=c.replace(/[^A-Za-z0-9\+\/\=]/g,"");while(b<c.length){k=this._keyStr.indexOf(c.charAt(b++));h=this._keyStr.indexOf(c.charAt(b++));e=this._keyStr.indexOf(c.charAt(b++));d=this._keyStr.indexOf(c.charAt(b++));l=(k<<2)|(h>>4);j=((h&15)<<4)|(e>>2);g=((e&3)<<6)|d;a=a+String.fromCharCode(l);if(e!=64){a=a+String.fromCharCode(j)}if(d!=64){a=a+String.fromCharCode(g)}}a=UTF8.decode(a);return a}};var Crypt={MD5:function(v){function N(b,a){return(b<<a)|(b>>>(32-a))}function M(k,b){var F,a,d,x,c;d=(k&2147483648);x=(b&2147483648);F=(k&1073741824);a=(b&1073741824);c=(k&1073741823)+(b&1073741823);if(F&a){return(c^2147483648^d^x)}if(F|a){if(c&1073741824){return(c^3221225472^d^x)}else{return(c^1073741824^d^x)}}else{return(c^d^x)}}function u(a,c,b){return(a&c)|((~a)&b)}function t(a,c,b){return(a&b)|(c&(~b))}function s(a,c,b){return(a^c^b)}function p(a,c,b){return(c^(a|(~b)))}function y(G,F,ac,ab,k,H,I){G=M(G,M(M(u(F,ac,ab),k),I));return M(N(G,H),F)}function g(G,F,ac,ab,k,H,I){G=M(G,M(M(t(F,ac,ab),k),I));return M(N(G,H),F)}function K(G,F,ac,ab,k,H,I){G=M(G,M(M(s(F,ac,ab),k),I));return M(N(G,H),F)}function w(G,F,ac,ab,k,H,I){G=M(G,M(M(p(F,ac,ab),k),I));return M(N(G,H),F)}function e(k){var G;var d=k.length;var c=d+8;var b=(c-(c%64))/64;var F=(b+1)*16;var H=Array(F-1);var a=0;var x=0;while(x<d){G=(x-(x%4))/4;a=(x%4)*8;H[G]=(H[G]|(k.charCodeAt(x)<<a));x++}G=(x-(x%4))/4;a=(x%4)*8;H[G]=H[G]|(128<<a);H[F-2]=d<<3;H[F-1]=d>>>29;return H}function E(c){var b="",d="",k,a;for(a=0;a<=3;a++){k=(c>>>(a*8))&255;d="0"+k.toString(16);b=b+d.substr(d.length-2,2)}return b}var J=Array();var R,j,L,z,h,aa,Z,Y,X;var U=7,S=12,P=17,O=22;var D=5,C=9,B=14,A=20;var q=4,o=11,n=16,m=23;var W=6,V=10,T=15,Q=21;v=UTF8.encode(v);J=e(v);aa=1732584193;Z=4023233417;Y=2562383102;X=271733878;for(R=0;R<J.length;R+=16){j=aa;L=Z;z=Y;h=X;aa=y(aa,Z,Y,X,J[R+0],U,3614090360);X=y(X,aa,Z,Y,J[R+1],S,3905402710);Y=y(Y,X,aa,Z,J[R+2],P,606105819);Z=y(Z,Y,X,aa,J[R+3],O,3250441966);aa=y(aa,Z,Y,X,J[R+4],U,4118548399);X=y(X,aa,Z,Y,J[R+5],S,1200080426);Y=y(Y,X,aa,Z,J[R+6],P,2821735955);Z=y(Z,Y,X,aa,J[R+7],O,4249261313);aa=y(aa,Z,Y,X,J[R+8],U,1770035416);X=y(X,aa,Z,Y,J[R+9],S,2336552879);Y=y(Y,X,aa,Z,J[R+10],P,4294925233);Z=y(Z,Y,X,aa,J[R+11],O,2304563134);aa=y(aa,Z,Y,X,J[R+12],U,1804603682);X=y(X,aa,Z,Y,J[R+13],S,4254626195);Y=y(Y,X,aa,Z,J[R+14],P,2792965006);Z=y(Z,Y,X,aa,J[R+15],O,1236535329);aa=g(aa,Z,Y,X,J[R+1],D,4129170786);X=g(X,aa,Z,Y,J[R+6],C,3225465664);Y=g(Y,X,aa,Z,J[R+11],B,643717713);Z=g(Z,Y,X,aa,J[R+0],A,3921069994);aa=g(aa,Z,Y,X,J[R+5],D,3593408605);X=g(X,aa,Z,Y,J[R+10],C,38016083);Y=g(Y,X,aa,Z,J[R+15],B,3634488961);Z=g(Z,Y,X,aa,J[R+4],A,3889429448);aa=g(aa,Z,Y,X,J[R+9],D,568446438);X=g(X,aa,Z,Y,J[R+14],C,3275163606);Y=g(Y,X,aa,Z,J[R+3],B,4107603335);Z=g(Z,Y,X,aa,J[R+8],A,1163531501);aa=g(aa,Z,Y,X,J[R+13],D,2850285829);X=g(X,aa,Z,Y,J[R+2],C,4243563512);Y=g(Y,X,aa,Z,J[R+7],B,1735328473);Z=g(Z,Y,X,aa,J[R+12],A,2368359562);aa=K(aa,Z,Y,X,J[R+5],q,4294588738);X=K(X,aa,Z,Y,J[R+8],o,2272392833);Y=K(Y,X,aa,Z,J[R+11],n,1839030562);Z=K(Z,Y,X,aa,J[R+14],m,4259657740);aa=K(aa,Z,Y,X,J[R+1],q,2763975236);X=K(X,aa,Z,Y,J[R+4],o,1272893353);Y=K(Y,X,aa,Z,J[R+7],n,4139469664);Z=K(Z,Y,X,aa,J[R+10],m,3200236656);aa=K(aa,Z,Y,X,J[R+13],q,681279174);X=K(X,aa,Z,Y,J[R+0],o,3936430074);Y=K(Y,X,aa,Z,J[R+3],n,3572445317);Z=K(Z,Y,X,aa,J[R+6],m,76029189);aa=K(aa,Z,Y,X,J[R+9],q,3654602809);X=K(X,aa,Z,Y,J[R+12],o,3873151461);Y=K(Y,X,aa,Z,J[R+15],n,530742520);Z=K(Z,Y,X,aa,J[R+2],m,3299628645);aa=w(aa,Z,Y,X,J[R+0],W,4096336452);X=w(X,aa,Z,Y,J[R+7],V,1126891415);Y=w(Y,X,aa,Z,J[R+14],T,2878612391);Z=w(Z,Y,X,aa,J[R+5],Q,4237533241);aa=w(aa,Z,Y,X,J[R+12],W,1700485571);X=w(X,aa,Z,Y,J[R+3],V,2399980690);Y=w(Y,X,aa,Z,J[R+10],T,4293915773);Z=w(Z,Y,X,aa,J[R+1],Q,2240044497);aa=w(aa,Z,Y,X,J[R+8],W,1873313359);X=w(X,aa,Z,Y,J[R+15],V,4264355552);Y=w(Y,X,aa,Z,J[R+6],T,2734768916);Z=w(Z,Y,X,aa,J[R+13],Q,1309151649);aa=w(aa,Z,Y,X,J[R+4],W,4149444226);X=w(X,aa,Z,Y,J[R+11],V,3174756917);Y=w(Y,X,aa,Z,J[R+2],T,718787259);Z=w(Z,Y,X,aa,J[R+9],Q,3951481745);aa=M(aa,j);Z=M(Z,L);Y=M(Y,z);X=M(X,h)}var l=E(aa)+E(Z)+E(Y)+E(X);return l.toLowerCase()},SHA1:function(d){function c(A,z){var j=(A<<z)|(A>>>(32-z));return j}function t(B){var A="";var j;var C;var z;for(j=0;j<=6;j+=2){C=(B>>>(j*4+4))&15;z=(B>>>(j*4))&15;A+=C.toString(16)+z.toString(16)}return A}function v(B){var A="";var z;var j;for(z=7;z>=0;z--){j=(B>>>(z*4))&15;A+=j.toString(16)}return A}var h;var x,w;var b=new Array(80);var n=1732584193;var l=4023233417;var k=2562383102;var g=271733878;var e=3285377520;var u,s,q,p,o;var y;d=UTF8.encode(d);var a=d.length;var m=new Array();for(x=0;x<a-3;x+=4){w=d.charCodeAt(x)<<24|d.charCodeAt(x+1)<<16|d.charCodeAt(x+2)<<8|d.charCodeAt(x+3);m.push(w)}switch(a%4){case 0:x=2147483648;break;case 1:x=d.charCodeAt(a-1)<<24|8388608;break;case 2:x=d.charCodeAt(a-2)<<24|d.charCodeAt(a-1)<<16|32768;break;case 3:x=d.charCodeAt(a-3)<<24|d.charCodeAt(a-2)<<16|d.charCodeAt(a-1)<<8|128;break}m.push(x);while((m.length%16)!=14){m.push(0)}m.push(a>>>29);m.push((a<<3)&4294967295);for(h=0;h<m.length;h+=16){for(x=0;x<16;x++){b[x]=m[h+x]}for(x=16;x<=79;x++){b[x]=c(b[x-3]^b[x-8]^b[x-14]^b[x-16],1)}u=n;s=l;q=k;p=g;o=e;for(x=0;x<=19;x++){y=(c(u,5)+((s&q)|(~s&p))+o+b[x]+1518500249)&4294967295;o=p;p=q;q=c(s,30);s=u;u=y}for(x=20;x<=39;x++){y=(c(u,5)+(s^q^p)+o+b[x]+1859775393)&4294967295;o=p;p=q;q=c(s,30);s=u;u=y}for(x=40;x<=59;x++){y=(c(u,5)+((s&q)|(s&p)|(q&p))+o+b[x]+2400959708)&4294967295;o=p;p=q;q=c(s,30);s=u;u=y}for(x=60;x<=79;x++){y=(c(u,5)+(s^q^p)+o+b[x]+3395469782)&4294967295;o=p;p=q;q=c(s,30);s=u;u=y}n=(n+u)&4294967295;l=(l+s)&4294967295;k=(k+q)&4294967295;g=(g+p)&4294967295;e=(e+o)&4294967295}var y=v(n)+v(l)+v(k)+v(g)+v(e);return y.toLowerCase()},SHA256:function(q){var m=8;var o=0;function k(s,v){var u=(s&65535)+(v&65535);var t=(s>>16)+(v>>16)+(u>>16);return(t<<16)|(u&65535)}function e(t,s){return(t>>>s)|(t<<(32-s))}function g(t,s){return(t>>>s)}function a(s,u,t){return((s&u)^((~s)&t))}function d(s,u,t){return((s&u)^(s&t)^(u&t))}function h(s){return(e(s,2)^e(s,13)^e(s,22))}function b(s){return(e(s,6)^e(s,11)^e(s,25))}function p(s){return(e(s,7)^e(s,18)^g(s,3))}function l(s){return(e(s,17)^e(s,19)^g(s,10))}function c(t,u){var G=new Array(1116352408,1899447441,3049323471,3921009573,961987163,1508970993,2453635748,2870763221,3624381080,310598401,607225278,1426881987,1925078388,2162078206,2614888103,3248222580,3835390401,4022224774,264347078,604807628,770255983,1249150122,1555081692,1996064986,2554220882,2821834349,2952996808,3210313671,3336571891,3584528711,113926993,338241895,666307205,773529912,1294757372,1396182291,1695183700,1986661051,2177026350,2456956037,2730485921,2820302411,3259730800,3345764771,3516065817,3600352804,4094571909,275423344,430227734,506948616,659060556,883997877,958139571,1322822218,1537002063,1747873779,1955562222,2024104815,2227730452,2361852424,2428436474,2756734187,3204031479,3329325298);var v=new Array(1779033703,3144134277,1013904242,2773480762,1359893119,2600822924,528734635,1541459225);var s=new Array(64);var I,H,F,E,C,A,z,y,x,w;var D,B;t[u>>5]|=128<<(24-u%32);t[((u+64>>9)<<4)+15]=u;for(var x=0;x<t.length;x+=16){I=v[0];H=v[1];F=v[2];E=v[3];C=v[4];A=v[5];z=v[6];y=v[7];for(var w=0;w<64;w++){if(w<16){s[w]=t[w+x]}else{s[w]=k(k(k(l(s[w-2]),s[w-7]),p(s[w-15])),s[w-16])}D=k(k(k(k(y,b(C)),a(C,A,z)),G[w]),s[w]);B=k(h(I),d(I,H,F));y=z;z=A;A=C;C=k(E,D);E=F;F=H;H=I;I=k(D,B)}v[0]=k(I,v[0]);v[1]=k(H,v[1]);v[2]=k(F,v[2]);v[3]=k(E,v[3]);v[4]=k(C,v[4]);v[5]=k(A,v[5]);v[6]=k(z,v[6]);v[7]=k(y,v[7])}return v}function j(v){var u=Array();var s=(1<<m)-1;for(var t=0;t<v.length*m;t+=m){u[t>>5]|=(v.charCodeAt(t/m)&s)<<(24-t%32)}return u}function n(u){var t=o?"0123456789ABCDEF":"0123456789abcdef";var v="";for(var s=0;s<u.length*4;s++){v+=t.charAt((u[s>>2]>>((3-s%4)*8+4))&15)+t.charAt((u[s>>2]>>((3-s%4)*8))&15)}return v}q=UTF8.encode(q);return n(c(j(q),q.length*m))},CRC32:function(e){e=UTF8.encode(e);var c="00000000 77073096 EE0E612C 990951BA 076DC419 706AF48F E963A535 9E6495A3 0EDB8832 79DCB8A4 E0D5E91E 97D2D988 09B64C2B 7EB17CBD E7B82D07 90BF1D91 1DB71064 6AB020F2 F3B97148 84BE41DE 1ADAD47D 6DDDE4EB F4D4B551 83D385C7 136C9856 646BA8C0 FD62F97A 8A65C9EC 14015C4F 63066CD9 FA0F3D63 8D080DF5 3B6E20C8 4C69105E D56041E4 A2677172 3C03E4D1 4B04D447 D20D85FD A50AB56B 35B5A8FA 42B2986C DBBBC9D6 ACBCF940 32D86CE3 45DF5C75 DCD60DCF ABD13D59 26D930AC 51DE003A C8D75180 BFD06116 21B4F4B5 56B3C423 CFBA9599 B8BDA50F 2802B89E 5F058808 C60CD9B2 B10BE924 2F6F7C87 58684C11 C1611DAB B6662D3D 76DC4190 01DB7106 98D220BC EFD5102A 71B18589 06B6B51F 9FBFE4A5 E8B8D433 7807C9A2 0F00F934 9609A88E E10E9818 7F6A0DBB 086D3D2D 91646C97 E6635C01 6B6B51F4 1C6C6162 856530D8 F262004E 6C0695ED 1B01A57B 8208F4C1 F50FC457 65B0D9C6 12B7E950 8BBEB8EA FCB9887C 62DD1DDF 15DA2D49 8CD37CF3 FBD44C65 4DB26158 3AB551CE A3BC0074 D4BB30E2 4ADFA541 3DD895D7 A4D1C46D D3D6F4FB 4369E96A 346ED9FC AD678846 DA60B8D0 44042D73 33031DE5 AA0A4C5F DD0D7CC9 5005713C 270241AA BE0B1010 C90C2086 5768B525 206F85B3 B966D409 CE61E49F 5EDEF90E 29D9C998 B0D09822 C7D7A8B4 59B33D17 2EB40D81 B7BD5C3B C0BA6CAD EDB88320 9ABFB3B6 03B6E20C 74B1D29A EAD54739 9DD277AF 04DB2615 73DC1683 E3630B12 94643B84 0D6D6A3E 7A6A5AA8 E40ECF0B 9309FF9D 0A00AE27 7D079EB1 F00F9344 8708A3D2 1E01F268 6906C2FE F762575D 806567CB 196C3671 6E6B06E7 FED41B76 89D32BE0 10DA7A5A 67DD4ACC F9B9DF6F 8EBEEFF9 17B7BE43 60B08ED5 D6D6A3E8 A1D1937E 38D8C2C4 4FDFF252 D1BB67F1 A6BC5767 3FB506DD 48B2364B D80D2BDA AF0A1B4C 36034AF6 41047A60 DF60EFC3 A867DF55 316E8EEF 4669BE79 CB61B38C BC66831A 256FD2A0 5268E236 CC0C7795 BB0B4703 220216B9 5505262F C5BA3BBE B2BD0B28 2BB45A92 5CB36A04 C2D7FFA7 B5D0CF31 2CD99E8B 5BDEAE1D 9B64C2B0 EC63F226 756AA39C 026D930A 9C0906A9 EB0E363F 72076785 05005713 95BF4A82 E2B87A14 7BB12BAE 0CB61B38 92D28E9B E5D5BE0D 7CDCEFB7 0BDBDF21 86D3D2D4 F1D4E242 68DDB3F8 1FDA836E 81BE16CD F6B9265B 6FB077E1 18B74777 88085AE6 FF0F6A70 66063BCA 11010B5C 8F659EFF F862AE69 616BFFD3 166CCF45 A00AE278 D70DD2EE 4E048354 3903B3C2 A7672661 D06016F7 4969474D 3E6E77DB AED16A4A D9D65ADC 40DF0B66 37D83BF0 A9BCAE53 DEBB9EC5 47B2CF7F 30B5FFE9 BDBDF21C CABAC28A 53B39330 24B4A3A6 BAD03605 CDD70693 54DE5729 23D967BF B3667A2E C4614AB8 5D681B02 2A6F2B94 B40BBE37 C30C8EA1 5A05DF1B 2D02EF8D";if(typeof(crc)=="undefined"){crc=0}var a=0;var g=0;crc=crc^(-1);for(var b=0,d=e.length;b<d;b++){g=(crc^e.charCodeAt(b))&255;a="0x"+c.substr(g*9,8);crc=(crc>>>8)^a}return crc^(-1)}};var Url={encode:function(a){return escape(UTF8.encode(a))},decode:function(a){return UTF8.decode(unescape(a))}};var UTF8={encode:function(b){b=new String(b);b=b.replace(/\r\n/g,"\n");var a="";for(var e=0;e<b.length;e++){var d=b.charCodeAt(e);if(d<128){a+=String.fromCharCode(d)}else{if((d>127)&&(d<2048)){a+=String.fromCharCode((d>>6)|192);a+=String.fromCharCode((d&63)|128)}else{a+=String.fromCharCode((d>>12)|224);a+=String.fromCharCode(((d>>6)&63)|128);a+=String.fromCharCode((d&63)|128)}}}return a},decode:function(a){a=new String(a);var b="";var d=0;var e=c1=c2=0;while(d<a.length){e=a.charCodeAt(d);if(e<128){b+=String.fromCharCode(e);d++}else{if((e>191)&&(e<224)){c2=a.charCodeAt(d+1);b+=String.fromCharCode(((e&31)<<6)|(c2&63));d+=2}else{c2=a.charCodeAt(d+1);c3=a.charCodeAt(d+2);b+=String.fromCharCode(((e&15)<<12)|((c2&63)<<6)|(c3&63));d+=3}}}return b}};var Cookie={set:function(a,b){expires=new Date(arguments[2]);path=arguments[3];domain=arguments[4];secure=arguments[5];document.cookie=a+"="+escape(b)+((expires)?"; expires="+expires.toUTCString():"")+((path)?"; path="+path:"")+((domain)?"; domain="+domain:"")+((secure)?"; secure":"")},get:function(a){var h=a.length;var c=document.cookie;var e=c.length;var d=0;var g;while(d<e){var b=d+h;if(c.substring(d,b)==a){g=c.indexOf(";",b);if(g==-1){g=c.length}return unescape(c.substring(b+1,g))}d++}return""}};function in_array(b,a){for(i=0;i<a.length;i++){if(a[i]==b){return true}}return false}function trim(b,a){return ltrim(rtrim(b,a),a)}function ltrim(b,a){a=a||"\\s";return b.replace(new RegExp("^["+a+"]+","g"),"")}function rtrim(b,a){a=a||"\\s";return b.replace(new RegExp("["+a+"]+$","g"),"")}function str_replace(d,b,c){var a=c.split(d);return a.join(b)}function str_replace_reg(d,a,c){var b=new RegExp(d,"g");return c.replace(b,a)}function stopRKey(a){var a=(a)?a:((event)?event:null);var b=(a.target)?a.target:((a.srcElement)?a.srcElement:null);if((a.keyCode==13)&&(b.type=="text"||b.type=="password")&&(b.hasClassName("acceptRet")==false)){return false}}document.onkeypress=stopRKey;
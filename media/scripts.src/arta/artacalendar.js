/**
 * ArtaCalendar Javascript Calendar System.
 * IT MUST ONLY BE USED IN ARTA.
 * Supports Gregorian and Jalali calendars.
 * Supports Languages.
 * Supports many Instances at one document.
 * 
 * @author		Mehran Ahadi
 * @package		Arta
 * @version		$Revision: 2 2013/08/30 00:05 +3.5 GMT $
 * @link		http://artaproject.com	Author's homepage
 * @copyright	Copyright (C) 2008 - 2013  Mehran Ahadi
 * @license		GNU General Public License version 3 or later; see COPYING file.
 */
var ArtaCalendarStore = {
	weekdays: new Array('Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'),
	short_weekdays: new Array('Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat'),
	
	gregorian_months: new Array('January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'),
	jalali_months: new Array('Farvardin', 'Ordibehest', 'Khordad', 'Tir', 'Mordad', 'Shahrivar', 'Mehr', 'Aban', 'Azar', 'Dey', 'Bahman', 'Esfand'),
	
	gregorian_weekday: new Array(0,0,1,2,3,4,5,6),
	jalali_weekday: new Array(6,6,0,1,2,3,4,5),
	
	g_days_in_month: new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31), 
	j_days_in_month: new Array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29), 

	lang: new Array('Today','Previous year', 'Previous month', 'Next month', 'Next year', 'Close', 'Refresh', 'in Jalali Calendar', 'in Gregorian Calendar', 'Selected Date')
};

var ArtaCalendar = Class.create({
	
	date: {},
	target: null,
	params: null,
	today: new Date(),
	rand: null,
	container: null,
			
	initialize: function(target, params){
	
		function setDefault(name, val){
			if (Object.isUndefined(params[name])==true){
				params[name] = val;
			}
		}
		
		this.target = $(target);
				
		if(this.target==null){
			alert('Invalid Target "'+target+'" for ArtaCalendar.');
			return false;
		}
		
		setDefault('type', 'gregorian');
		setDefault('parent', false);
		setDefault('handler', target);
		setDefault('format', '%Y-%m-%d %H:%i:%s');
		setDefault('onChangeValue', Prototype.emptyFunction);
		setDefault('onOpen', Prototype.emptyFunction);

		if(params.parent!==false){
			params.parent=$(params.parent);
		}
		params.handler=$(params.handler);
		
		this.params = params;
		
		this.rand = parseInt(Math.random()*100000);
		
		this.load();
		
		return true;
	},
	
	load: function(){
		if(this.params.parent !== false){
			this.open(-1);
		}else{
			this.params.handler.observe('click', this.open.bindAsEventListener(this));
		}
	},
	
	open: function(){
		this.parseDate();
		if(arguments[0] != -1 && typeof arguments[0] == 'number'){
			this.params.onChangeValue.bindAsEventListener(this)();
		}else{
			this.params.onOpen.bindAsEventListener(this)();
			if(arguments[0] == -1){
				arguments[0] =0;
			}
		}
		this.drawHTML();
		if(this.params.parent == false){
			pos=Element.cumulativeOffset(this.params.handler);
			pos2=Element.cumulativeOffset(this.target);
			if(pos[1]+this.container.getHeight()>document.viewport.getHeight()){
				this.container.setStyle({top:(pos[1]-this.container.getHeight()-10)+"px"});
			}else{
				this.container.setStyle({top:(pos[1]+10)+"px"});
			}
			if(pos2[0]>pos[0]){
				this.container.setStyle({left: (pos[0]-this.container.getWidth()-10)+"px", display: 'none'});
			}else{
				this.container.setStyle({left: (pos[0]+10)+"px", display: 'none'});
			}
			if( typeof arguments[0] !== 'number'){
				new Effect.Appear(this.container, {duration:.3});
			}else if (arguments[0] == 0){
				this.container.show();
			}else{
				new Effect.Appear(this.container, {duration:parseInt(arguments[0])});
			}
		}
	},
	
	parseDate: function() {
		this.date.y = this.today.getFullYear();
		this.date.m = this.today.getMonth()+1;
		this.date.d = this.today.getDate();
		this.date.weekday = this.today.getDay();
		this.date.hour = this.today.getHours();
		this.date.min = this.today.getMinutes();
		this.date.sec = this.today.getSeconds();
		
		if(this.params.type=='jalali'){
			jalali = this.gregorian_to_jalali(this.date.y, this.date.m, this.date.d);
			this.date.y = jalali[0];
			this.date.m = jalali[1];
			this.date.d = jalali[2];
		}
		
		if(this.target.tagName=='INPUT'){
			str=this.target.value;
		}else{
			str=this.target.innerHTML;
		}

		var a     = str.split(/\W+/);
		var b     = this.params.format.match(/%./g);
		var i     = 0;
		
		for (i = 0; i < a.length; ++i) {
			if (!a[i]) continue;
			switch (b[i]) {
			case "%d":
			case "%j":
				this.date.d = parseInt(a[i], 10);
				break;
			case "%m":
			case "%n":
				this.date.m = parseInt(a[i], 10);
				break;
			case "%Y":
			case "%y":
				//1348,10,11 on zero timestamp in jalali
				// 2000 is 1378,10,11
				this.date.y = parseInt(a[i], 10);
				break;
			case "%H":
			case "%G":
				this.date.hour = parseInt(a[i], 10);
				break;
			case "%i":
				this.date.min = parseInt(a[i], 10);
				break;
			case "%s":
				this.date.sec = parseInt(a[i], 10);
				break;
			}
		}
	},
	
	drawHTML: function(){
		if(this.container !== null){
			this.container.remove();
			this.container = null;
		}
		var today = this.params.type=='jalali'?
					this.gregorian_to_jalali(parseInt(this.today.getFullYear()), 
								parseInt(this.today.getMonth())+1, 
								parseInt(this.today.getDate()))
					:
					new Array(parseInt(this.today.getFullYear()), 
								parseInt(this.today.getMonth())+1, 
								parseInt(this.today.getDate()));
		var date = this.date;
		var lang = ArtaCalendarStore.lang;
		var rand = this.rand;
		var weekdays = ArtaCalendarStore.weekdays;
		var short_weekdays = ArtaCalendarStore.short_weekdays;
		
		if(this.params.type=='jalali'){
			month_days=ArtaCalendarStore.j_days_in_month;
			
			if((date.m==1 && [1,5,9,13,18,22,26,30].indexOf((date.y-1)%33)>0)
			||(date.m==12 && [1,5,9,13,18,22,26,30].indexOf((date.y)%33)>0)){
				month_days[11]=30;
			}else{
				month_days[11]=29;
			}
			
			week=ArtaCalendarStore.jalali_weekday;
			month=ArtaCalendarStore.jalali_months[date.m-1];
		}else{
			month_days=ArtaCalendarStore.g_days_in_month;
			if((date.m==2 || date.m==3) && ((date.y)%400==0 || ((date.y)%4==0 && (date.y)%100!=0))){
				month_days[1]=29;
			}else{
				month_days[1]=28;
			}
			week=ArtaCalendarStore.gregorian_weekday;
			month=ArtaCalendarStore.gregorian_months[date.m-1];
		}
		
		if(! (date.m>0 && date.m<13) || ! (date.d >0 && date.d<=month_days[date.m-1])){
			alert('Invalid Date value specified.');
			return false;
		}
		
		divElement = new Element("DIV", this.params.parent==false?{style:"position: absolute;"}:{});
		divElement.observe('click', this.onClick.bindAsEventListener(this));
		
		prev_y=(date.y-1)+'-'+date.m+'-'+date.d;
		prev_m=date.y+'-'+(date.m-1)+'-'+date.d;
		if((date.m-1)<1){
			prev_m=(date.y-1)+'-'+12+'-'+date.d;
		}
		next_m=date.y+'-'+(date.m+1)+'-'+date.d;
		if((date.m+1)>12){
			next_m=(date.y+1)+'-'+1+'-'+date.d;
		}
		next_y=(date.y+1)+'-'+date.m+'-'+date.d;
		
		if(this.params.parent==false){
			exit='<th title="'+lang[5]+'" id="'+rand+'_exit" class="close">x</th>';
		}else{
			exit='<th title="'+lang[6]+'" id="'+rand+'_refresh" class="close">#</th>';
		}
		var content='<table><thead><tr><th colspan="6">'+month+' - '+date.y+'</th>'+exit+'</tr></thead><tr class="controls"><td id="'+rand+'_goto_'+prev_y+'" title="'+lang[1]+'">&lt;&lt;</td><td id="'+rand+'_goto_'+prev_m+'" title="'+lang[2]+'">&lt;</td><td id="'+rand+'_setvalue_'+today[0]+'-'+today[1]+'-'+today[2]+'" colspan="3">'+lang[0]+'</td><td id="'+rand+'_goto_'+next_m+'" title="'+lang[3]+'">&gt;</td><td id="'+rand+'_goto_'+next_y+'" title="'+lang[4]+'">&gt;&gt;</td></tr>';
				
				
		days=new Array();
		s_days=new Array();
		
		content +='<tr class="weekdays">';
		for(i=0;i<7;i++){
			days[i]=weekdays[week[i+1]];
			s_days[i]=short_weekdays[week[i+1]];
			if(s_days[i]==s_days[week[0]]){
				c=' class="weekend"';
			}else{
				c='';
			}
			content +='<td'+c+'><b>'+s_days[i]+'</b></td>';
		}
		content +='</tr>';
	
		// month start day
		
		if(this.params.type=='jalali'){
			month_start=this.getJalaliMonthStartWeekday();
			
			month_start +=1; // because jalali starts from sat, not sun!
			if(month_start>6){
				month_start -=7;
			}
		}else{
			month_start=this.getMonthStartWeekday();
		}
		trows=1;
		content +='<tr>';
		
		// now printing weekday
		weeknow=month_start;
		// now printing date
		now=1;
			if(month_start==0){
				j=7;
				while(j>0){
					last= month_days[date.m==1?11:date.m-2]-j+1;
					content +='<td id="'+rand+'_setvalue_'+(date.m==1?date.y-1:date.y)+'-'+(date.m==1?12:date.m-1)+'-'+last+'" class="other_months">'+last+'</td>';
					j--;
				}
				content +='</tr><tr>';
				trows++;
			}
			while(now <=month_days[date.m-1]){
				while(month_start>0){
					last= month_days[date.m==1?11:date.m-2]-month_start+1;
					content +='<td id="'+rand+'_setvalue_'+(date.m==1?date.y-1:date.y)+'-'+(date.m==1?12:date.m-1)+'-'+last+'" class="other_months">'+last+'</td>';
					month_start--;
				}
				if(weeknow==7){
					weeknow=0;
					content +='</tr><tr>';
					trows++;
				}
				if(now == date.d){// year and month should not be compared because it will change on calendar month view change
					class_sel='selected';
					lang_sel=lang[9]+' - ';
				}else{
					class_sel='';
					lang_sel='';
				}
				if(this.params.type=='jalali'){
					g_date=this.jalali_to_gregorian(date.y, date.m, now);
					transed=g_date[0]+'-'+g_date[1]+'-'+g_date[2]+' '+lang[8];
				}else{
					j_date=this.gregorian_to_jalali(date.y, date.m, now);
					transed=j_date[0]+'-'+j_date[1]+'-'+j_date[2]+' '+lang[7];
				}
				
				if(today[0]==date.y && today[1]==date.m && today[2]==now){
					if(class_sel!==''){
						class_sel=' '+class_sel;
					}
					content +='<td id="'+rand+'_setvalue_'+date.y+'-'+date.m+'-'+now+'" class="today'+class_sel+'" title="'+lang_sel+lang[0]+' - '+transed+'">'+now+'</td>';
				}else{
					if(class_sel!==''){
						class_sel=' class="'+class_sel+'"';
					}
					content +='<td id="'+rand+'_setvalue_'+date.y+'-'+date.m+'-'+now+'"'+class_sel+' title="'+lang_sel+transed+'">'+now+'</td>';
				}
				now++;
				weeknow++;
			}
		
		last=1;
		while(weeknow+last < 8){
			content +='<td id="'+rand+'_setvalue_'+(date.m==12?date.y+1:date.y)+'-'+(date.m==12?1:date.m+1)+'-'+last+'" class="other_months">'+last+'</td>';
			last++;
		}
		last2=1;
		if(trows!==6){
			content +='</tr><tr>';
			while(last2<8){
				content +='<td id="'+rand+'_setvalue_'+(date.m==12?date.y+1:date.y)+'-'+(date.m==12?1:date.m+1)+'-'+last+'" class="other_months">'+last+'</td>';
				last++;
				last2++;
			}
		}
		content +='</tr>';
		content +='</table>';
		Element.update(divElement, content);
		if(this.params.parent==false){
			par=document.body;
		}else{
			par=this.params.parent;
		}
		//divElement.writeAttribute('id', cal.params['handler']+'_calendar'); may not be necessary
		divElement.writeAttribute('class', 'calendar');
		par.appendChild(divElement);
		this.container=divElement;
	},
	
	onClick: function(e){
		el = e.element();
		elid = el.id.split('_');
		if(parseInt(elid[0])==this.rand){
			switch(elid[1]){
				case 'exit':
					this.close();
					break;
				case 'refresh':
					this.close(0);
					this.open(0);
					break;
				case 'goto':
				case 'setvalue':
					if(this.target.tagName=='INPUT'){
						this.target.value = this.createDate(elid[2]);
					}else{
						this.target.innerHTML = this.createDate(elid[2]);
					}
					this.close((elid[1]=='goto' || this.params.parent !== false)?0:null);
					if(elid[1]=='goto' || this.params.parent !== false)	this.open(0);
			}
		}
	},
	
	close: function(){
		if( typeof arguments[0] !== 'number'){
			new Effect.Fade(this.container, {duration:.3, afterFinish: function(){this.container.remove(); this.container = null;}.bindAsEventListener(this)});
		}else if (arguments[0] == 0){
			this.container.remove();
			this.container = null;
		}else{
			new Effect.Fade(this.container, {duration:parseInt(arguments[0]), afterFinish: function(){this.container.remove(); this.container = null;}.bindAsEventListener(this)});
		}
		
	},
	
	createDate: function(datestr) {
		date= datestr.split('-');
		
		weekday = this.today.getDay();
		hour = this.today.getHours();
		min = this.today.getMinutes();
		sec = this.today.getSeconds();
		
		var res=new String();
		
		var b     = this.params.format.match(/(%.|[^%]*)/g);
		var i     = 0;

		for (i = 0; i < b.length; i++) {
			
			switch (b[i]) {
			case "%d":
			case "%j":
				res +=date[2];
				break;
			case "%m":
			case "%n":
				res +=date[1];
				break;
			case "%Y":
			case "%y":
				//1348,10,11 on zero timestamp in jalali
				// 2000 is 1378,10,11
				res +=date[0];
				break;
			case "%H":
			case "%G":
				res +=hour;
				break;
			case "%i":
				res +=min;
				break;
			case "%s":
				res +=sec;
				break;
			default:
				res +=b[i];
			}
		}
		
		return res;
	}, 

	getJalaliMonthStartWeekday:function(){
		d=this.jalali_to_gregorian(this.date.y,this.date.m,1);
		gd=new Date(d[0],d[1]-1,d[2]);
		return gd.getDay();
	},

	getMonthStartWeekday: function(y,m){
		d=new Date(this.date.y,this.date.m-1,1);
		return d.getDay();		
	},
	
	gregorian_to_jalali : function (g_y, g_m, g_d){
		g_days_in_month = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); 
		j_days_in_month = new Array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);     
		gy = g_y-1600; 
		gm = g_m-1; 
		gd = g_d-1; 
		
		g_day_no = 365*gy+this.div(gy+3,4)-this.div(gy+99,100)+this.div(gy+399,400); 
		
		for (i=0; i < gm; ++i) 
			  g_day_no += g_days_in_month[i]; 
		if (gm>1 && ((gy%4==0 && gy%100!=0) || (gy%400==0))) 
			  /* leap and after Feb */ 
			  g_day_no++; 
		g_day_no += gd; 
		
		j_day_no = g_day_no-79; 
		
		j_np = this.div(j_day_no, 12053); /* 12053 = 365*33 + 32/4 */ 
		j_day_no = j_day_no % 12053; 
		
		jy = 979+33*j_np+4*this.div(j_day_no,1461); /* 1461 = 365*4 + 4/4 */ 
		
		j_day_no %= 1461; 
		
		if (j_day_no >= 366) { 
			  jy += this.div(j_day_no-1, 365); 
			  j_day_no = (j_day_no-1)%365; 
		} 
		
		for (i = 0; i < 11 && j_day_no >= j_days_in_month[i]; ++i) 
			  j_day_no -= j_days_in_month[i]; 
		jm = i+1; 
		jd = j_day_no+1; 
		
		return new Array(jy, jm, jd); 
	},
	
	jalali_to_gregorian: function(j_y, j_m, j_d){ 
		g_days_in_month = new Array(31, 28, 31, 30, 31, 30, 31, 31, 30, 31, 30, 31); 
		j_days_in_month = new Array(31, 31, 31, 31, 31, 31, 30, 30, 30, 30, 30, 29);
		
		jy = j_y-979; 
		jm = j_m-1; 
		jd = j_d-1; 
		
		j_day_no = 365*jy + this.div(jy, 33)*8 + this.div(jy%33+3, 4); 
		for (i=0; i < jm; ++i) 
			  j_day_no += j_days_in_month[i]; 
		
		j_day_no += jd; 
		
		g_day_no = j_day_no+79; 
		
		gy = 1600 + 400*this.div(g_day_no, 146097); /* 146097 = 365*400 + 400/4 - 400/100 + 400/400 */ 
		g_day_no = g_day_no % 146097; 
		
		leap = true; 
		if (g_day_no >= 36525) /* 36525 = 365*100 + 100/4 */ 
		{ 
			  g_day_no--; 
			  gy += 100*this.div(g_day_no,  36524); /* 36524 = 365*100 + 100/4 - 100/100 */ 
			  g_day_no = g_day_no % 36524; 
		
			  if (g_day_no >= 365) 
				 g_day_no++; 
			  else 
				 leap = false; 
		} 
		
		gy += 4*this.div(g_day_no, 1461); /* 1461 = 365*4 + 4/4 */ 
		g_day_no %= 1461; 
		
		if (g_day_no >= 366) { 
			  leap = false; 
		
			  g_day_no--; 
			  gy += this.div(g_day_no, 365); 
			  g_day_no = g_day_no % 365; 
		} 
		
		for (i = 0; g_day_no >= g_days_in_month[i] + (i == 1 && leap); i++) 
			  g_day_no -= g_days_in_month[i] + (i == 1 && leap); 
		gm = i+1; 
		gd = g_day_no+1; 
		
		return new Array(gy, gm, gd); 
	},

	div: function(a, b){
		return parseInt(a/b);
	}

});


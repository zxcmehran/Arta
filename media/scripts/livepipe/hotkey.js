if(typeof(Prototype)=="undefined"){throw"HotKey requires Prototype to be loaded."}if(typeof(Object.Event)=="undefined"){throw"HotKey requires Object.Event to be loaded."}var HotKey=Class.create({initialize:function(b,c,a){b=b.toUpperCase();HotKey.hotkeys.push(this);this.options=Object.extend({element:false,shiftKey:false,altKey:false,ctrlKey:true,bubbleEvent:true,fireOnce:false},a||{});this.letter=b;this.callback=function(d){if(!(this.options.fireOnce&&this.fired)&&Object.isFunction(c)){c(d)}if(!this.options.bubbleEvent){d.stop()}this.fired=true};this.element=$(this.options.element||document);this.handler=function(d){if(!d||((Event["KEY_"+this.letter]||this.letter.charCodeAt(0))==d.keyCode&&((!this.options.shiftKey||(this.options.shiftKey&&d.shiftKey))&&(!this.options.altKey||(this.options.altKey&&d.altKey))&&(!this.options.ctrlKey||(this.options.ctrlKey&&d.ctrlKey))))){if(this.notify("beforeCallback",d)===false){return}this.callback(d);this.notify("afterCallback",d)}}.bind(this);this.enable()},trigger:function(){this.handler()},enable:function(){this.element.observe("keydown",this.handler)},disable:function(){this.element.stopObserving("keydown",this.handler)},destroy:function(){this.disable();HotKey.hotkeys=HotKey.hotkeys.without(this)}});Object.extend(HotKey,{hotkeys:[]});Object.Event.extend(HotKey);
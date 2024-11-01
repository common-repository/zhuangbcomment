/**
 * 用来提交整个表单的JQUERY插件
 * 作者及API查询:http://www.malsup.com/jquery/form/
 */
(function($){
$.fn.ajaxSubmit=function(_2){
if(!this.length){
log("ajaxSubmit: skipping submit process - no element selected");
return this;
}
if(typeof _2=="function"){
_2={success:_2};
}
_2=$.extend({url:this.attr("action")||window.location.toString(),type:this.attr("method")||"GET"},_2||{});
var _3={};
this.trigger("form-pre-serialize",[this,_2,_3]);
if(_3.veto){
log("ajaxSubmit: submit vetoed via form-pre-serialize trigger");
return this;
}
var a=this.formToArray(_2.semantic);
if(_2.data){
_2.extraData=_2.data;
for(var n in _2.data){
a.push({name:n,value:_2.data[n]});
}
}
if(_2.beforeSubmit&&_2.beforeSubmit(a,this,_2)===false){
log("ajaxSubmit: submit aborted via beforeSubmit callback");
return this;
}
this.trigger("form-submit-validate",[a,this,_2,_3]);
if(_3.veto){
log("ajaxSubmit: submit vetoed via form-submit-validate trigger");
return this;
}
var q=$.param(a);
if(_2.type.toUpperCase()=="GET"){
_2.url+=(_2.url.indexOf("?")>=0?"&":"?")+q;
_2.data=null;
}else{
_2.data=q;
}
var _7=this,_8=[];
if(_2.resetForm){
_8.push(function(){
_7.resetForm();
});
}
if(_2.clearForm){
_8.push(function(){
_7.clearForm();
});
}
if(!_2.dataType&&_2.target){
var _9=_2.success||function(){
};
_8.push(function(_a){
$(_2.target).html(_a).each(_9,arguments);
});
}else{
if(_2.success){
_8.push(_2.success);
}
}
_2.success=function(_b,_c){
for(var i=0,_e=_8.length;i<_e;i++){
_8[i](_b,_c,_7);
}
};
var _f=$("input:file",this).fieldValue();
var _10=false;
for(var j=0;j<_f.length;j++){
if(_f[j]){
_10=true;
}
}
if(_2.iframe||_10){
if($.browser.safari&&_2.closeKeepAlive){
$.get(_2.closeKeepAlive,fileUpload);
}else{
fileUpload();
}
}else{
$.ajax(_2);
}
this.trigger("form-submit-notify",[this,_2]);
return this;
function fileUpload(){
var _12=_7[0];
if($(":input[@name=submit]",_12).length){
alert("Error: Form elements must not be named \"submit\".");
return;
}
var _13=$.extend({},$.ajaxSettings,_2);
var id="jqFormIO"+(new Date().getTime());
var $io=$("<iframe id=\""+id+"\" name=\""+id+"\" />");
var io=$io[0];
if($.browser.msie||$.browser.opera){
io.src="javascript:false;document.write(\"\");";
}
$io.css({position:"absolute",top:"-1000px",left:"-1000px"});
var xhr={responseText:null,responseXML:null,status:0,statusText:"n/a",getAllResponseHeaders:function(){
},getResponseHeader:function(){
},setRequestHeader:function(){
}};
var g=_13.global;
if(g&&!$.active++){
$.event.trigger("ajaxStart");
}
if(g){
$.event.trigger("ajaxSend",[xhr,_13]);
}
var _19=0;
var _1a=0;
var sub=_12.clk;
if(sub){
var n=sub.name;
if(n&&!sub.disabled){
_2.extraData=_2.extraData||{};
_2.extraData[n]=sub.value;
if(sub.type=="image"){
_2.extraData[name+".x"]=_12.clk_x;
_2.extraData[name+".y"]=_12.clk_y;
}
}
}
setTimeout(function(){
var t=_7.attr("target"),a=_7.attr("action");
_7.attr({target:id,encoding:"multipart/form-data",enctype:"multipart/form-data",method:"POST",action:_13.url});
if(_13.timeout){
setTimeout(function(){
_1a=true;
cb();
},_13.timeout);
}
var _1e=[];
try{
if(_2.extraData){
for(var n in _2.extraData){
_1e.push($("<input type=\"hidden\" name=\""+n+"\" value=\""+_2.extraData[n]+"\" />").appendTo(_12)[0]);
}
}
$io.appendTo("body");
io.attachEvent?io.attachEvent("onload",cb):io.addEventListener("load",cb,false);
_12.submit();
}
finally{
_7.attr("action",a);
t?_7.attr("target",t):_7.removeAttr("target");
$(_1e).remove();
}
},10);
function cb(){
if(_19++){
return;
}
io.detachEvent?io.detachEvent("onload",cb):io.removeEventListener("load",cb,false);
var _20=0;
var ok=true;
try{
if(_1a){
throw "timeout";
}
var _22,doc;
doc=io.contentWindow?io.contentWindow.document:io.contentDocument?io.contentDocument:io.document;
if(doc.body==null&&!_20&&$.browser.opera){
_20=1;
_19--;
setTimeout(cb,100);
return;
}
xhr.responseText=doc.body?doc.body.innerHTML:null;
xhr.responseXML=doc.XMLDocument?doc.XMLDocument:doc;
xhr.getResponseHeader=function(_24){
var _25={"content-type":_13.dataType};
return _25[_24];
};
if(_13.dataType=="json"||_13.dataType=="script"){
var ta=doc.getElementsByTagName("textarea")[0];
xhr.responseText=ta?ta.value:xhr.responseText;
}else{
if(_13.dataType=="xml"&&!xhr.responseXML&&xhr.responseText!=null){
xhr.responseXML=toXml(xhr.responseText);
}
}
_22=$.httpData(xhr,_13.dataType);
}
catch(e){
ok=false;
$.handleError(_13,xhr,"error",e);
}
if(ok){
_13.success(_22,"success");
if(g){
$.event.trigger("ajaxSuccess",[xhr,_13]);
}
}
if(g){
$.event.trigger("ajaxComplete",[xhr,_13]);
}
if(g&&!--$.active){
$.event.trigger("ajaxStop");
}
if(_13.complete){
_13.complete(xhr,ok?"success":"error");
}
setTimeout(function(){
$io.remove();
xhr.responseXML=null;
},100);
}
function toXml(s,doc){
if(window.ActiveXObject){
doc=new ActiveXObject("Microsoft.XMLDOM");
doc.async="false";
doc.loadXML(s);
}else{
doc=(new DOMParser()).parseFromString(s,"text/xml");
}
return (doc&&doc.documentElement&&doc.documentElement.tagName!="parsererror")?doc:null;
}
}
};
$.fn.ajaxForm=function(_29){
return this.ajaxFormUnbind().bind("submit.form-plugin",function(){
$(this).ajaxSubmit(_29);
return false;
}).each(function(){
$(":submit,input:image",this).bind("click.form-plugin",function(e){
var _2b=this.form;
_2b.clk=this;
if(this.type=="image"){
if(e.offsetX!=undefined){
_2b.clk_x=e.offsetX;
_2b.clk_y=e.offsetY;
}else{
if(typeof $.fn.offset=="function"){
var _2c=$(this).offset();
_2b.clk_x=e.pageX-_2c.left;
_2b.clk_y=e.pageY-_2c.top;
}else{
_2b.clk_x=e.pageX-this.offsetLeft;
_2b.clk_y=e.pageY-this.offsetTop;
}
}
}
setTimeout(function(){
_2b.clk=_2b.clk_x=_2b.clk_y=null;
},10);
});
});
};
$.fn.ajaxFormUnbind=function(){
this.unbind("submit.form-plugin");
return this.each(function(){
$(":submit,input:image",this).unbind("click.form-plugin");
});
};
$.fn.formToArray=function(_2d){
var a=[];
if(this.length==0){
return a;
}
var _2f=this[0];
var els=_2d?_2f.getElementsByTagName("*"):_2f.elements;
if(!els){
return a;
}
for(var i=0,max=els.length;i<max;i++){
var el=els[i];
var n=el.name;
if(!n){
continue;
}
if(_2d&&_2f.clk&&el.type=="image"){
if(!el.disabled&&_2f.clk==el){
a.push({name:n+".x",value:_2f.clk_x},{name:n+".y",value:_2f.clk_y});
}
continue;
}
var v=$.fieldValue(el,true);
if(v&&v.constructor==Array){
for(var j=0,_37=v.length;j<_37;j++){
a.push({name:n,value:v[j]});
}
}else{
if(v!==null&&typeof v!="undefined"){
a.push({name:n,value:v});
}
}
}
if(!_2d&&_2f.clk){
var _38=_2f.getElementsByTagName("input");
for(var i=0,max=_38.length;i<max;i++){
var _39=_38[i];
var n=_39.name;
if(n&&!_39.disabled&&_39.type=="image"&&_2f.clk==_39){
a.push({name:n+".x",value:_2f.clk_x},{name:n+".y",value:_2f.clk_y});
}
}
}
return a;
};
$.fn.formSerialize=function(_3a){
return $.param(this.formToArray(_3a));
};
$.fn.fieldSerialize=function(_3b){
var a=[];
this.each(function(){
var n=this.name;
if(!n){
return;
}
var v=$.fieldValue(this,_3b);
if(v&&v.constructor==Array){
for(var i=0,max=v.length;i<max;i++){
a.push({name:n,value:v[i]});
}
}else{
if(v!==null&&typeof v!="undefined"){
a.push({name:this.name,value:v});
}
}
});
return $.param(a);
};
$.fn.fieldValue=function(_41){
for(var val=[],i=0,max=this.length;i<max;i++){
var el=this[i];
var v=$.fieldValue(el,_41);
if(v===null||typeof v=="undefined"||(v.constructor==Array&&!v.length)){
continue;
}
v.constructor==Array?$.merge(val,v):val.push(v);
}
return val;
};
$.fieldValue=function(el,_48){
var n=el.name,t=el.type,tag=el.tagName.toLowerCase();
if(typeof _48=="undefined"){
_48=true;
}
if(_48&&(!n||el.disabled||t=="reset"||t=="button"||(t=="checkbox"||t=="radio")&&!el.checked||(t=="submit"||t=="image")&&el.form&&el.form.clk!=el||tag=="select"&&el.selectedIndex==-1)){
return null;
}
if(tag=="select"){
var _4c=el.selectedIndex;
if(_4c<0){
return null;
}
var a=[],ops=el.options;
var one=(t=="select-one");
var max=(one?_4c+1:ops.length);
for(var i=(one?_4c:0);i<max;i++){
var op=ops[i];
if(op.selected){
var v=$.browser.msie&&!(op.attributes["value"].specified)?op.text:op.value;
if(one){
return v;
}
a.push(v);
}
}
return a;
}
return el.value;
};
$.fn.clearForm=function(){
return this.each(function(){
$("input,select,textarea",this).clearFields();
});
};
$.fn.clearFields=$.fn.clearInputs=function(){
return this.each(function(){
var t=this.type,tag=this.tagName.toLowerCase();
if(t=="text"||t=="password"||tag=="textarea"){
this.value="";
}else{
if(t=="checkbox"||t=="radio"){
this.checked=false;
}else{
if(tag=="select"){
this.selectedIndex=-1;
}
}
}
});
};
$.fn.resetForm=function(){
return this.each(function(){
if(typeof this.reset=="function"||(typeof this.reset=="object"&&!this.reset.nodeType)){
this.reset();
}
});
};
$.fn.enable=function(b){
if(b==undefined){
b=true;
}
return this.each(function(){
this.disabled=!b;
});
};
$.fn.select=function(_57){
if(_57==undefined){
_57=true;
}
return this.each(function(){
var t=this.type;
if(t=="checkbox"||t=="radio"){
this.checked=_57;
}else{
if(this.tagName.toLowerCase()=="option"){
var _59=$(this).parent("select");
if(_57&&_59[0]&&_59[0].type=="select-one"){
_59.find("option").select(false);
}
this.selected=_57;
}
}
});
};
function log(){
if($.fn.ajaxSubmit.debug&&window.console&&window.console.log){
window.console.log("[jquery.form] "+Array.prototype.join.call(arguments,""));
}
}
})(jQuery);


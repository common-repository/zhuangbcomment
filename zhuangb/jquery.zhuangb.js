jQuery.fn.zhuangb=function(_1,_2){
var _3={url:_1,name:"zhuangb",event:"mouseover",title:"Leave a reply:"};
if(_2){
jQuery.extend(_3,_2);
}
_3.obj="#"+_2.name+"_zhuangb";
var _4=_3.name.split("_")[2];
var _5=_3.name.split("_")[1];
if(_4!=null&&_5!=null){
jQuery(this)[_3.event](function(e){
var _7=jQuery(_3.obj);
if(_7.attr("zhuangbWin")=="done"){
return false;
}
_7.html("<div class=\"zhuangbWin_form\"> Loading... </div>");
tit="<div class=\"zhuangbWin_tit\"><span class=\"zhuangbWin_title_close\"><a href=\"javascript:void(0);\">Close</a></span><span class=\"zhuangbWin_title\">"+_3.title+"</span></div>";
_7.prepend(tit);
_7.slideDown("fast");
_7.attr("zhuangbWin","done");
jQuery(_3.obj+" div.zhuangbWin_tit span.zhuangbWin_title_close").click(function(){
_7.slideUp();
_7.attr("zhuangbWin","close");
});
jQuery.ajax({url:_3.url,dataType:"html",type:"post",data:"postid="+_5+"&zhuangbid="+_4+"&zhuangbwin="+_3.obj+"&wpurl="+_3.wpurl+"&email="+_3.email+"&zhuangbaction=zhuangbform",success:function(_8){
_7.find(".zhuangbWin_form").html(_8);
},error:function(){
_7.find(".zhuangbWin_form").html("<font style=\"padding: 2px; font-size: 12px\">Requested error......\u88c5B\u5931\u8d25~</font>");
}});
});
}
};
function thisMovie(_9){
if(navigator.appName.indexOf("Microsoft")!=-1){
return window[_9];
}else{
return document[_9];
}
}
function asCallZhuangBlist(_a,_b,_c,_d,_e){
jQuery("#postlist_"+_e+"_"+_d+"_zhuangb").html("&nbsp;&nbsp;&nbsp;Loading......");
jQuery.ajax({url:_b+"/wp-content/plugins/zhuangb/comments-ajax-zhuangb.php",dataType:"html",type:"post",data:"zhuangb="+_d+"&glll="+_e+"&page="+_a+"&email="+_c+"&zhuangbaction=zhuangblist",success:function(_f){
jQuery("#postlist_"+_e+"_"+_d+"_zhuangb").slideUp("fast",function(){
jQuery("#postlist_"+_e+"_"+_d+"_zhuangb").html(_f);
});
jQuery("#postlist_"+_e+"_"+_d+"_zhuangb").slideDown("fast");
},error:function(){
jQuery("#postlist_"+_e+"_"+_d+"_zhuangb").html("&nbsp;&nbsp;&nbsp;zhuangb error - . -");
}});
}
function ajaxZhuangBList(_10,_11,_12,_13,_14){
jQuery(document).ready(function(){
if (_14 != null) {
jQuery("#post_"+_13+"_"+_12).zhuangb(_10+"/wp-content/plugins/zhuangb/comments-ajax-zhuangb.php",{name:"post_"+_13+"_"+_12,event:_14,wpurl:_10,email:_11});
}
jQuery.ajax({url:_10+"/wp-content/plugins/zhuangb/comments-ajax-zhuangb.php",dataType:"html",type:"post",data:"zhuangb="+_12+"&glll="+_13+"&page=10&email="+_11+"&zhuangbaction=zhuangblist",success:function(msg){
jQuery("#postlist_"+_13+"_"+_12+"_zhuangb").html(msg);
var myflash = thisMovie("zhuangb_flash_"+_13+"_"+_12);
if (myflash != null) {
try{
myflash.zhuangBList(jQuery("#postlist_"+_13+"_"+_12+"_zhuangb #zhuangbcount").val(),_13,_12,_11,_10);
} catch (e){
jQuery("#postlist_"+_13+"_"+_12+"_zhuangb h3").append("(Flash menu error.)");
}
}
},error:function(){
jQuery("#postlist_"+_13+"_"+_12+"_zhuangb").html("&nbsp;&nbsp;&nbsp;zhuangb error - . -");
}});
})
}
function ajaxZhuangBForm(_16,_17,_18,_19,_1a){
jQuery("#zhuangbform_"+_17+"_"+_16).ajaxForm({success:function(_1b){
jQuery(_18).html("<div style=\"font-size:10px; font:Tahoma, Verdana; padding: 10px; color:#000000;\"><br>Thank you for your comment! <br> ^-^</div>");
thisMovie("zhuangb_flash_"+_17+"_"+_16).zhuangBLoading();
jQuery("#postlist_"+_17+"_"+_16+"_zhuangb").html("&nbsp;&nbsp;&nbsp;Loading......");
jQuery(_18).slideUp();
jQuery(_18).attr("zhuangbWin","close");
ajaxZhuangBList(_19,_1a,_16,_17);
},error:function(_1c){
jQuery("#zhuangbform_"+_17+"_"+_16+"_responseText").empty().text(_1c.responseText);
var _1d=jQuery("#zhuangbform_"+_17+"_"+_16+"_responseText").text();
if(_1d.indexOf("error-page")>-1){
_1d=_1d.split("<p>")[1].split("</p>")[0];
alert(_1d);
}else{
alert(_1d);
}
jQuery("#zhuangbform_"+_17+"_"+_16+"_responseText").empty();
}});
}


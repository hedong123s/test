//顶部用户操作
$(function(){
	$(".hdUserWrap .hdUserInfo").click(function(){
		var i=$(this);
		var c=i.closest(".hdUserWrap").find(".hdUserMain");
		c.is(":hidden")?c.show():c.hide();
	})
	$(document).click(function(e){
		if($(e.target).closest(".hdUserMain").size()==0 && $(e.target).closest(".hdUserInfo").size()==0){
			$(".hdUserMain",window.top.document).hide();
		}
	})
})

//主导航 投资统计显示
function moneyTotalResize(){
	var windowWidth=$(window).width();
	var navWrapWidth=$(".navWrap .nav ul").outerWidth();
	var moneyTotalWidth=$(".moneyTotal").outerWidth();
	(navWrapWidth+moneyTotalWidth<=windowWidth-10)?$(".moneyTotal").show():$(".moneyTotal").hide();
}
$(function(){
	//moneyTotalResize();
	//$(window).resize(function(){moneyTotalResize();});
})

//主体宽高
function masterWrapResize(){
	//var windowWidth=$(window).width();
	var windowHeight=$(window).height();
	var headerHeight=$(".header").outerHeight();
	var navWrapHeight=$(".navWrap").outerHeight();
	$(".masterWrap,#main_iframe").height(windowHeight-headerHeight-navWrapHeight-2);  //减去2px误差
}
$(function(){
	masterWrapResize();
	$(window).resize(function(){masterWrapResize();});
})

//左侧菜单
$(function(){
	//展开关闭
	$(".masterMenu>ul>li>a,.masterMenu>ul>li>.arrow").live("click",function(){
		var i=$(this).closest("li");
			//i.addClass("cur").siblings("li").removeClass("cur");
		if(i.find("ul").size()>0){
			var ul=i.find("ul");
			!ul.is(":hidden")?i.removeClass("cur"):i.addClass("cur")/*.siblings("li").removeClass("cur")*/;
		}else{i.addClass("cur").siblings("li").removeClass("cur");}
	})
	//子菜单点击
	$(".masterMenu ul ul li").live("click",function(){
		$(".masterMenu ul ul li").removeClass("cur")
		$(this).addClass("cur");
	})
	
	//左侧关闭
	$(".togglemenu").click(function(){
		$(".masterMain").addClass("masterMainCur");
		$(".masterMenu").addClass("masterMenuCur");
		$(".toggleOpen").show();
	})
	
	//左侧展开
	$(".toggleOpen").click(function(){
		$(".masterMain").removeClass("masterMainCur");
		$(".masterMenu").removeClass("masterMenuCur");
		$(".toggleOpen").hide();
	})
})


//标签切换
$(function(){
	$(".tab-nav li").click(function(){
		var url=$(this).find("a").attr("href");
		if($(url).size()>0){
			$(this).addClass("cur").siblings("li").removeClass("cur");
			$(url).show().siblings(".tab-con").hide();
			return false;
		}
	})
})

/*列表页样式*/
$(function(){
	//$(".stdtable input:checkbox").uniform();
	
	//隔行换色
	$(".stdtablelist tbody tr:odd").each(function(){
		$(this).addClass("odd");
	});
	//划过
	$(".stdtablelist tbody tr").hover(function(){$(this).addClass("hover")},function(){$(this).removeClass("hover")});
	
	//全选/取消
	$(".checkall").change(function(){
		var checkbox=$(this).closest("form").find("input:checkbox:enabled");
		$(this).is(":checked")?checkbox.attr("checked",true):checkbox.attr("checked",false);
	})
})

//搜索展开关闭
$(function(){
	var listSearchDefault=$(".listSearchTit").attr("data-default");
	var listSearchShow=function(){
		$(".listSearchTit span").html("隐藏");
		$(".listSearchBox").show();
	}
	var listSearchHide=function(){
		$(".listSearchTit span").html("展开");
		$(".listSearchBox").hide();
	}
	//初始化
	listSearchDefault==1?listSearchShow():listSearchHide();
	
	//点击
	$(".listSearchTit").click(function(){
		$(".listSearchBox").is(":hidden")?listSearchShow():listSearchHide();
	});
})

/**
 * [alerts 警告]
 * @param i:弹出内容
 * @param c:点击确认后执行的操作
 * @param s:弹窗状态  默认为1为成功；2为失败；0为警告；默认为0；
 */
var alerts=function(i,c,s){ return $.dialog.alert(i,c,s);}

/* 
用途：检查输入对象的值是否符合整数格式 
输入：str 输入的字符串 
返回：如果通过验证返回true,否则返回false */ 
function isInteger( str ){  
	var regu = /^[-]{0,1}[0-9]{1,}$/; 
	return regu.test(str); 
} 


/**
用途：检查输入字符串是否是带小数的数字格式,可以是负数 
输入： 
s：字符串 
返回： 
如果通过验证返回true,否则返回false 
*/ 
function isDecimal( str ){   
	if(isInteger(str)) return true; 
	var re = /^[-]{0,1}(\d+)[\.]+(\d+)$/; 
	if (re.test(str)) { 
		if(RegExp.$1==0&&RegExp.$2==0) return false; 
		return true; 
	}else{ 
		return false; 
	} 
} 


//价格格式化，保留小数点2位
var format_price = function( s ){
	s=s.toString();
    s=s.replace(/^(\d*)$/,"$1.");
	s=(s+"00").replace(/(\d*\.\d\d)\d*/,"$1");
	s=s.replace(".",",");
	var re=/(\d)(\d{3},)/;
	while(re.test(s)){
			s=s.replace(re,"$1,$2");
	}
	s=s.replace(/,(\d\d)$/,".$1");
	return s.replace(/^\./,"0.");
}

//不能小于0.1
function CheckInputInt(oInput) {
	if ('' != oInput.value.replace(/\d/g,'')){
		oInput.value = oInput.value.replace(/\D/g,'');   
	}  
	if(parseInt(oInput.value)<0.1){oInput.value="1"; }
}
//不能小于0
function CheckInputInt2(oInput) {   
	if ('' != oInput.value.replace(/\d/g,'')){
		oInput.value = oInput.value.replace(/\D/g,'');   
	}  
	if(parseInt(oInput.value)<0){  
		oInput.value="1";  
	}    
}

//验证信息 str：需要检测的字符串；type：检测类型
function isValid(str,type){
	var regexEnum = {
		intege:"^-?[1-9]\\d*$",					//整数
		intege1:"^[1-9]\\d*$",					//正整数
		intege2:"^-[1-9]\\d*$",					//负整数
		num:"^([+-]?)\\d*\\.?\\d+$",			//数字
		num1:"^[1-9]\\d*|0$",					//正数（正整数 + 0）
		num2:"^-[1-9]\\d*|0$",					//负数（负整数 + 0）
		decmal:"^([+-]?)\\d*\\.\\d+$",			//浮点数
		decmal1:"^[1-9]\\d*.\\d*|0.\\d*[1-9]\\d*$",　　	//正浮点数
		decmal2:"^-([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*)$",　 //负浮点数
		decmal3:"^-?([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*|0?.0+|0)$",　 //浮点数
		decmal4:"^[1-9]\\d*.\\d*|0.\\d*[1-9]\\d*|0?.0+|0$",　　 //非负浮点数（正浮点数 + 0）
		decmal5:"^(-([1-9]\\d*.\\d*|0.\\d*[1-9]\\d*))|0?.0+|0$",　　//非正浮点数（负浮点数 + 0）
		email:"^\\w+((-\\w+)|(\\.\\w+))*\\@[A-Za-z0-9]+((\\.|-)[A-Za-z0-9]+)*\\.[A-Za-z0-9]+$", //邮件
		color:"^[a-fA-F0-9]{6}$",				//颜色
		url:"^http[s]?:\\/\\/([\\w-]+\\.)+[\\w-]+([\\w-./?%&=]*)?$",	//url
		chinese:"^[\\u4E00-\\u9FA5\\uF900-\\uFA2D]+$",					//仅中文
		ascii:"^[\\x00-\\xFF]+$",				//仅ACSII字符
		zipcode:"^\\d{6}$",						//邮编
		mobile:"^13[0-9]{9}|15[012356789][0-9]{8}|18[0-9]{9}|147[0-9]{8}|17[012356789][0-9]{8}$",				//手机
		ip4:"^(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)\\.(25[0-5]|2[0-4]\\d|[0-1]\\d{2}|[1-9]?\\d)$",	//ip地址
		notempty:"^\\S+$",						//非空
		picture:"(.*)\\.(jpg|bmp|gif|ico|pcx|jpeg|tif|png|raw|tga)$",	//图片
		rar:"(.*)\\.(rar|zip|7zip|tgz)$",								//压缩文件
		date:"^\\d{4}(\\-|\\/|\.)\\d{1,2}\\1\\d{1,2}$",					//日期
		qq:"^[1-9]*[1-9][0-9]*$",				//QQ号码
		tel:"^(([0\\+]\\d{2,3}-)?(0\\d{2,3})-)?(\\d{7,8})(-(\\d{3,}))?$",	//电话号码的函数(包括验证国内区号,国际区号,分机号)
		username:"^\\w+$",						//用来用户注册。匹配由数字、26个英文字母或者下划线组成的字符串
		letter:"^[A-Za-z]+$",					//字母
		letter_u:"^[A-Z]+$",					//大写字母
		letter_l:"^[a-z]+$",					//小写字母
		idcard:"^[1-9]([0-9]{14}|[0-9]{17})$"	//身份证
	}
	regex=regexEnum[type];
	is = (new RegExp(regex, "i")).test(str)
	if(!is){
		return false;
	}
	return true;
}

//验证是否为手机号
function isMobile(str){
	return isValid(str,'mobile');
}
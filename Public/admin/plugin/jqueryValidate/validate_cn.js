//错误信息本地化
(function (factory) {
    if (typeof define === "function" && define.amd) {
        define(["jquery", "jquery.validate"], factory);
    } else {
        factory(jQuery);
    }
}(function ($) {
    $.extend($.validator.messages, {
        required: "此项必须填写",
        remote: "请修正此栏位",
        email: "请输入有效的电子邮件",
        url: "请输入有效的网址",
        date: "请输入有效的日期",
        dateISO: "请输入有效的日期 (YYYY-MM-DD)",
        number: "请输入正确的数字",
        digits: "只可输入数字",
        creditcard: "请输入有效的信用卡号码",
        equalTo: "你的输入不相同",
        extension: "请输入有效的后缀",
        maxlength: $.validator.format("最多 {0} 个字"),
        minlength: $.validator.format("最少 {0} 个字"),
        rangelength: $.validator.format("请输入长度为 {0} 至 {1} 之間的字串"),
        range: $.validator.format("请输入 {0} 至 {1} 之间的数值"),
        max: $.validator.format("请输入不大于 {0} 的数值"),
        min: $.validator.format("请输入不小于 {0} 的数值")
    });
}));

$(document)
		.ready(
				function () {

				    // //////////////表单验证////////////////

				    // 设置默认属性
				    // $.validator.setDefaults({
				    // submitHandler : function() {
				    // alert("恭喜你！验证通过！");
				    // form.submit();
				    // }
				    // });

				    // 自定义验证

				    // 手机号码验证
				    jQuery.validator.addMethod(
									"isMobile",
									function (value, element) {
									    var length = value.length;
									    var mobile = /^(((13[0-9]{1})|(15[0-9]{1})|(17[0-9]{1})|(18[0-9]{1}))+\d{8})$/;
									    return this.optional(element)
												|| (length == 11 && mobile
														.test(value));
									}, "请正确填写您的手机号码");

				    // 电话号码验证
				    jQuery.validator.addMethod("isTel",
							function (value, element) {
							    var phone = /^0\d{2,3}-\d{7,8}$/;
							    return this.optional(element)
										|| (phone.test(value));
							}, "请正确填写您的电话号码");

				    // 字符验证
				    jQuery.validator.addMethod("stringCheck", function (value,
							element) {
				        return this.optional(element)
								|| /^[\u0391-\uFFE5\w]+$/.test(value);
				    }, "只能包括中文字、英文字母、数字和下划线");

				    // 中文字两个字节
				    jQuery.validator.addMethod("byteRangeLength", function (
							value, element, param) {
				        var length = value.length;
				        for (var i = 0; i < value.length; i++) {
				            if (value.charCodeAt(i) > 127) {
				                length++;
				            }
				        }
				        return this.optional(element)
								|| (length >= param[0] && length <= param[1]);
				    }, "请确保输入的值在3-15个字节之间(一个中文字算2个字节)");

				    // 下拉框验证
				    jQuery.validator.addMethod("selectNone", function (value,
							element) {
				        return value == "请选择";
				    }, "必须选择一项");

				    //企业名称验证
				    jQuery.validator
					.addMethod("companyName",
							function (value, element) {
							    var phone = /^[\u4E00-\u9FA5\w-\-\(\)\（\）]+$/;
							    return this.optional(element)
										|| (phone.test(value));
							}, "输入非法符号");

				    // 用户名验证
				    jQuery.validator
							.addMethod(
									"isName",
									function (value, element) {
									    var length = /^\w{6,30}$/;
									    var d = /\d/;
									    var z = /[a-zA-Z]/;
									    var down = /\_/;
									    var isname = false;
									    var num = 0;
									    if (d.test(value)) {
									        num++;
									    }
									    if (z.test(value)) {
									        num++;
									    }
									    if (down.test(value)) {
									        num++;
									    }
									    if (num > 1) {
									        isname = true;
									    }
									    return this.optional(element)
												|| (length.test(value)) && isname;
									}, "6-30位字母和数字、下划线的组合且首字符是字母");
				    // 密码验证
				    jQuery.validator
							.addMethod(
									"isPwd",
									function (value, element) {
									    var length = /^\w{6,20}$/;
									    var d = /\d/;
									    var z = /[a-zA-Z]/;
									    var down = /\_/;
									    var ispwd = false;
									    var num = 0;
									    if (d.test(value)) {
									        num++;
									    }
									    if (z.test(value)) {
									        num++;
									    }
									    if (down.test(value)) {
									        num++;
									    }
									    if (num > 1) {
									        ispwd = true;
									    }
									    return this.optional(element)
												|| (length.test(value) && ispwd);
									}, "6-20位字母和数字、下划线的组合");
				    //全数字验证
				    jQuery.validator
					.addMethod(
							"isNumber",
							function (value, element) {
							    var phone = /^[0-9][0-9_]*$/;
							    return this.optional(element)
										|| (phone.test(value));
							}, "全数字验证");

				    // 中文和英文验证
				    jQuery.validator.addMethod("isCE",
							function (value, element) {
							    var phone = /^[\u0391-\uFFE5A-Za-z]+$/;
							    return this.optional(element)
										|| (phone.test(value));
							}, "只能输入中文或者英文");

				    // 电子邮箱重写
				    jQuery.validator
							.addMethod(
									"isEmail",
									function (value, element) {
									    var tel = /^[a-zA-Z0-9._%-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
									    var length = /^.{6,30}$/;
									    return this.optional(element)
												|| (tel.test(value)) && (length.test(value));
									}, "请正确填写您的电子邮箱");
				    //中文名字
				    jQuery.validator.addMethod("cnCharset", function (value, element) {
				        return this.optional(element) || /^[\u4e00-\u9fa5]+$/.test(value);
				    });

				    //两位小数
				    jQuery.validator.addMethod("numFixed", function (value, element) {
				        return this.optional(element) || /^\d{0,8}\.{0,1}(\d{1,2})$/.test(value);
				    });

				    //身份证验证
				    jQuery.validator.addMethod("isID", function (idNo, element) {
				        var city = { 11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江 ", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北 ", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏 ", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外 " };

				        if (!idNo || !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(idNo)) {
				            return false;
				        }

				        if (!city[idNo.substr(0, 2)]) {
				            return false;
				        }

				        //18位身份证需要验证最后一位校验位
				        if (idNo.length == 18) {
				            var arrExp = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];//加权因子
				            var arrValid = [1, 0, "X", 9, 8, 7, 6, 5, 4, 3, 2];//校验码
				            var sum = 0, idx;
				            for (var i = 0; i < idNo.length - 1; i++) {
				                // 对前17位数字与权值乘积求和
				                sum += parseInt(idNo.substr(i, 1), 10) * arrExp[i];
				            }
				            // 计算模（固定算法）
				            idx = sum % 11;
				            // 检验第18为是否与校验码相等
				            return arrValid[idx] == idNo.substr(17, 1).toUpperCase();
				        }

				        return true;
				    }, "请正确填写您的身份证号码");

					//依赖验证路由
				    jQuery.validator.addMethod("isID_required", function (
							idNo, element, param) {
				        var required_val = jQuery(param).val().trim(); //依赖的值
				        if (required_val == 'id_card'){
				        	var city = { 11: "北京", 12: "天津", 13: "河北", 14: "山西", 15: "内蒙古", 21: "辽宁", 22: "吉林", 23: "黑龙江 ", 31: "上海", 32: "江苏", 33: "浙江", 34: "安徽", 35: "福建", 36: "江西", 37: "山东", 41: "河南", 42: "湖北 ", 43: "湖南", 44: "广东", 45: "广西", 46: "海南", 50: "重庆", 51: "四川", 52: "贵州", 53: "云南", 54: "西藏 ", 61: "陕西", 62: "甘肃", 63: "青海", 64: "宁夏", 65: "新疆", 71: "台湾", 81: "香港", 82: "澳门", 91: "国外 " };

					        if (!idNo || !/^\d{6}(18|19|20)?\d{2}(0[1-9]|1[012])(0[1-9]|[12]\d|3[01])\d{3}(\d|X)$/i.test(idNo)) {
					            return false;
					        }

					        if (!city[idNo.substr(0, 2)]) {
					            return false;
					        }

					        //18位身份证需要验证最后一位校验位
					        if (idNo.length == 18) {
					            var arrExp = [7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2];//加权因子
					            var arrValid = [1, 0, "X", 9, 8, 7, 6, 5, 4, 3, 2];//校验码
					            var sum = 0, idx;
					            for (var i = 0; i < idNo.length - 1; i++) {
					                // 对前17位数字与权值乘积求和
					                sum += parseInt(idNo.substr(i, 1), 10) * arrExp[i];
					            }
					            // 计算模（固定算法）
					            idx = sum % 11;
					            // 检验第18为是否与校验码相等
					            return arrValid[idx] == idNo.substr(17, 1).toUpperCase();
					        }

					        return true;
				        }else if (required_val == 'passport'){
				        	if (idNo.length <= 0){
				        		return false;
				        	}
				        	return true;
				        }
				    }, "证件号码格式错误");

				    var status = 1;



				    // 修改密码验证
				    $("#passwordForm03")
							.validate(
									{
									    // 验证规则
									    rules: {
									        password: {
									            required: true,
									            byteRangeLength: [6, 20],
									        },
									        inputPassword2: {
									            required: true,
									            equalTo: '#password',
									        }
									    },
									    // 设置错误信息
									    messages: {
									        password: {
									            required: "请输入登录密码",
									            byteRangeLength: "6-20位字母和数字、下划线的组合",
									        },
									        inputPassword2: {
									            required: "请输入确认密码",
									            equalTo: "两次输入密码不一致",
									        }
									    },
									    // 错误信息显示
									    errorPlacement: function (error,
												element) {
									        if (element
													.is('input[type=checkbox]')) {
									            var parent = $(element)
														.parent();
									            $(
														'<div class="form-control-feedback"><i class="sprite icon-u0311"></i></div>')
														.insertAfter(parent)
														.append(error);
									            // parent.parent().addClass('has-error
									            // has-feedback');
									        } else {
									            var parent = $(element)
														.parent();
									            $(element).next().remove('div')
									            $(
														'<div class="form-control-feedback"><i class="sprite icon-u0312"></i></div>')
														.insertAfter(element)
														.append(error);
									            parent
														.addClass('has-error has-feedback');
									        }
									        ;
									    },
									    // 成功信息显示
									    success: function (error, element) {
									        var parent = $(element).parent();
									        parent
													.removeClass('has-error has-feedback');
									        $(element).next().remove('div')
									        $(
													'<div class="form-control-feedback"><i class="sprite icon-u0311"></i></div>')
													.insertAfter(element);
									    }

									    // submitHandler: function (form) {
									    // $("#userregisterForm").submit();
									    // alert('xfm');
									    // }
									});


				    // 支付密码验证
				    $("#passwordForm")
							.validate(
									{
									    // 验证规则
									    rules: {
									        inputPassword: {
									            required: true,
									            byteRangeLength: [6, 20],
									        },
									        inputPassword2: {
									            required: true,
									            equalTo: '#inputPassword',
									        }
									    },
									    // 设置错误信息
									    messages: {
									        inputPassword: {
									            required: "请输入支付密码",
									            byteRangeLength: "6-20位字母和数字、下划线的组合",
									        },
									        inputPassword2: {
									            required: "请输入确认密码",
									            equalTo: "两次输入密码不一致",
									        }
									    },
									    // 错误信息显示
									    errorPlacement: function (error,
												element) {
									        var parent = $(element).parent();
									        $(element).next().remove('div')
									        $(
													'<div class="form-control-feedback"><i class="sprite icon-u0312"></i></div>')
													.insertAfter(element)
													.append(error);
									        parent
													.addClass('has-error has-feedback');
									    },
									    // 成功信息显示
									    success: function (error, element) {
									        var parent = $(element).parent();
									        parent
													.removeClass('has-error has-feedback');
									        $(element).next().remove('div')
									        $(
													'<div class="form-control-feedback"><i class="sprite icon-u0311"></i></div>')
													.insertAfter(element);
									    },

									    submitHandler: function (form) {
									        // alert("恭喜你！支付密码验证通过！");
									    }
									});

				});

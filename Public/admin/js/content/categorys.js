/**
 * 商品分类js
 */

/**
 * 分类增加编辑提交js
 */
$('.categorysForm').validate({
    //验证规则
    rules: {
        name: {
            required: true
        },
        sortid: {
            required: true,
            isNumber: true,
            range:[0,100000000]
        },
		status: {
            required: true
        },
		is_menu: {
            required: true
        }
    },
    //错误信息
    messages: {
        name: {
            required: '请输入商品分类名称'
        },
        sortid: {
            required: '请输入排序编号',
            isNumber: '请输入数字',
            range: '值范围在{0}-{1}之间'
        },
		status: {
            required: '请选择是否显示'
        },
		is_menu: {
            required: '请选择是否显示在导航栏'
        }
    },
    //输出错误
    errorPlacement: function(error, element){
        //$('.errorInfos').html(error).show();
    },
    //提交验证
    invalidHandler: function (form, validator) {
        $.each(validator.errorList, function (key, value) {
            $(".errorInfo").html(value.message).show();
            return false;
        });
    },
    //输出成功
    success: function(error, element){
        //$('.errorInfos').empty().show();
    },
    //提交
    submitHandler: function(form){
        $(".errorInfo").empty().show();

        $.post(add_url, $(form).serialize(), function(d) {
            if (d.status) {
                window.location.href = d.url;
            } else {
                $('.errorInfo').html(d.msg).show();
            }
        }, 'json');

        return false;
    }
});


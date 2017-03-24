//规格管理js


/**
 * 页面初始化
 */
$(function(){
    /**
     * 页面初始化
     */
    function init(){
        // 新增一个属性
        var html = template('add_attr_template');
        $('#attrs_body').append(html);
    }
    init();
    

    /**
     * 增加扩展属性
     */
    $('#attr_add').click(function(){
        var html = template('add_attr_template');
        $('#attrs_body').append(html);
    })

    /**
     * 删除扩展属性
     */
    $('#attrs_body tr a.del').live('click', function(){
        $(this).closest('tr').remove();
    })
})

/**
 * 分类增加编辑提交js
 */
$('.specForm').validate({
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
        }
		
    },
    //错误信息
    messages: {
        name: {
            required: '请输入商品规格名称'
        },
        sortid: {
            required: '请输入排序编号',
            isNumber: '请输入数字',
            range: '值范围在{0}-{1}之间'
        },
		status: {
            required: '请选择是否生效'
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



